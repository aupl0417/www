<?php
/*  注意：
 * 函数名 + Cache ： 表示将该函数返回的数据加入缓存
 * 函数名 + Cache ： 表示将该Model所对应的缓存清除
 **/
namespace Admin\Controller;
use Think\Page;
/**
 * 后台课程管理控制器
 */
class CourseController extends AdminController {

    /**
     * 课程管理首页
     */
    public function index(){
        $this->meta_title = '课程管理首页';
        if(isset($_GET['pid'])){
            $this->search();
        }else {
            $where['co_branchId'] = array('in', implode(',', $this->branchIds));
            $model = D('Common/Course');
            $count = $model->where($where)->count();
            $page = new Page($count, C('SHOW_PAGE_SIZE'));
            $limit = $page->firstRow . ',' . $page->listRows;
            $field = array('co_id', 'co_name', 'co_description', 'co_score', 'co_logo', ' sd_name as courseTypeName', 'br_name as branchName', 'co_createTime', 'co_updateTime');
            $result = $model->getCourseDataCache($field, $where, $limit);
            
            $this->result = $result;
            $this->show = $page->show();
        }
        
        $this->display();
    }
    
    /*
     * 添加课程
     * */
    public function add(){
        if(IS_POST){
            $co_logo = I('post.co_logo');
            empty($co_logo[0]) && $this->error("请上传班级LOGO");
            
            $branchId = I('co_branchId');
            if(is_null($branchId) || !in_array($branchId, $this->branchIds)){
                $this->error('您没有选择该分院的权限！');
            }
            
            $course = D('Common/Course');
            $id = $course->addInfoCacheClean();
            !$id && $this->error($course->getError() ? $course->getError() : '新增失败');
            
            //记录行为
            action_log('add_Course', 'Course', $id, UID);
            $this->success('新增成功', U('Course/index'));
        } else {
            $this->meta_title = '新增课程';
            $field = array('sd_id as id', 'sd_name as name');
            $courseList = D('Common/StudyDirection')->getStudyDirectionDataCache($field);
            
            $Brach = D("Common/Branch");
            $branch = $Brach->recursion(BRANCHID);
            
            $this->courseList = json_encode($courseList);
            $this->branch = json_encode($branch);
            $this->uploadData = array('title'=>'', 'action'=>'add', 'name'=> 'co_logo');
            $this->display('add');
        }
        
    }
    
    /*
     * 编辑课程
     * */
    public function edit(){
        if(IS_POST){
            $id = I('post.co_id', 0, 'intval');
            $co_logo = I('post.co_logo');
            $id == 0 && $this->error('非法参数');
            empty($co_logo) && $this->error("请上传班级LOGO");
            
            $branchId = session('course_branchId');
            if(is_null($branchId) || !in_array($branchId, $this->branchIds)){
                $this->error('您没有权限！');
            }
            
            $courseBranchId = I('co_branchId', 0, 'intval');
            if(is_null($courseBranchId) || !in_array($courseBranchId, $this->branchIds)){
                $this->error('您没有选择该分院的权限！');
            }
            
            $course = D('Common/Course');
            $res = $course->editInfoCacheClean();
            
            $res === false && $this->error($course->getError() ? $course->getError() : '编辑失败');
            session('course_branchId', null);
            action_log('update_course', 'Course', $id, UID);
            $this->success('编辑成功', U('Course/index'));
        }else {
            $id = I('id', 0, 'intval');
            $id == 0 && $this->error('非法参数');
            $fields = array('co_id', 'co_name', 'co_content', 'co_description', 'co_score', 'co_logo', 'co_studyDirectionId', 'co_branchId', 'sd_name', 'br_name');
            $info = D('Common/Course')->getCourseDataByIdCache($id, $fields);
            
            //课程类别/学习方向
            $field = array('sd_id as id', 'sd_name as name');
            $courseList = D('Common/StudyDirection')->getStudyDirectionDataCache($field);
            
            //分院
            $Brach = D("Common/Branch");
            $branch = $Brach->recursion(BRANCHID);
            
            session('course_branchId', $info['co_branchId']);
            $this->courseList = json_encode($courseList);
            $this->branch = json_encode($branch);
            $this->branchName = $info['br_name'];
            $this->courseTypeName = $info['sd_name'];
            $this->meta_title = '编辑课程';
            $this->uploadData = array('title'=>'上传课程LOGO', 'action'=>'edit', 'name'=> 'co_logo');
            $this->info = $info;
            $this->display('add');
        }
    }
    
    //查看课程
    public function view(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        $fields = array('co_id', 'co_name', 'co_content', 'co_description', 'co_score', 'co_logo', 'co_studyDirectionId', 'co_branchId', 'sd_name', 'br_name');
        $info = D('Common/Course')->getCourseDataByIdCache($id, $fields);
        
        $this->branchName = $info['br_name'];
        $this->courseTypeName = $info['sd_name'];
        $this->meta_title = '查看课程';
        $this->info = $info;
        $this->display();
    }
    
    /* 删除课程
     * @param $id 课程id集
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            
            $ids = !is_array($id) ? $id : implode(',', $id);
            
            $res = D('Common/Course')->delCourseCacheClean($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限！'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('delete_course', 'Course', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
	
	public function getselectCourseListTempletByAjax() {
		$this->display(__FUNCTION__);
	}
	public function getselectCourseListByAjax($getStudyDirectionId='',$words='') {
		$courseModel = D('Common/Course');
		$field = array('co_id', 'co_name', 'sd_id', 'sd_name');
		$where['co_branchId'] = array('in', implode(',', $this->branchIds));
		if(!empty($getStudyDirectionId)) {
			$where['co_studyDirectionId'] = $getStudyDirectionId;
		}
		if(!empty($words)) {
			$where['co_name'] = array('like','%'.$words.'%');
		}
		$courseLIsts = $courseModel->listsCache($where, $field);
		$this->ajaxReturn($courseLIsts,'JSON');
	}
	
	private function search(){
	    /* 查询条件初始化 */
	    $map = array();
	    if(isset($_GET['title'])){
	        $title = (string)I('title');
	        $where['co_name']  = array('like', '%'.$title.'%');
	        $where['co_description']  = array('like', '%'.$title.'%');
	        $where['co_content']  = array('like', '%'.$title.'%');
	        $where['_logic']  = 'or';
	        $map['_complex'] = $where;
	    }
	    
	    if ( isset($_GET['time-start']) ) {
	        $map['co_createTime'][] = array('egt', I('time-start'));
	    }
	    if ( isset($_GET['time-end']) ) {
	        $map['co_createTime'][] = array('elt', I('time-end'));
	    }
	    
	    $map['co_branchId'] = array('in', implode(',', $this->branchIds));
	    $field = array('co_id', 'co_name', 'co_description', 'co_score', 'co_logo', ' sd_name as courseTypeName', 'br_name as branchName', 'co_createTime', 'co_updateTime');
	    $join = array(
	        'LEFT JOIN __STUDY_DIRECTION__ on sd_id=co_studyDirectionId',
	        "LEFT JOIN __BRANCH__ on br_id=co_branchId",
	    );
	    $list = $this->lists('course',$map,'co_createTime desc', '', $field, $join);
	    
	    $this->result = $list;
	}
    
}
