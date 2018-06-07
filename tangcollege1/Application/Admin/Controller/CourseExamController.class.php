<?php

namespace Admin\Controller;
use Think\Page;

/**
 * 后台题库管理控制器
 */
class CourseExamController extends AdminController {

    /**
     * 题库管理首页
     */
    public function index(){
        
        //获取下属分院的管理员和教师的id
        $condition['branchId'] = array('in', implode(',', $this->branchIds));
        $condition['identity'] = array('neq', 0);
        $uids = M('ucenter_member')->where($condition)->getField('id', true);
        
        //试题查询条件（自己上传的试题和下属分院共享的试题）
        $where['cre_userId'] = array('in', implode(',', $uids));
        $where['cre_isPublic'] = array('eq', 1);
        $map['_complex'] = $where;
        $map['cre_userId'] = UID;
        $map['_logic'] = 'or';
        
        /* $fields = array('cre_id', 'cre_name', 'cre_a', 'cre_b', 'cre_c', 'cre_d', 'cre_answer', 'cre_description', 'cre_userId', 'username','cre_type', 'cre_isPublic'); */
        $model = D('Common/CourseExam');
        $count = $model->where($map)->count();
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        $fields = array('cre_id', 'cre_name', 'cre_description', 'cre_userId', 'username','cre_type', 'cre_isPublic');
        $result = $model->getCourseExamDataCache($fields, $map, $limit);
        
        $this->result = $result;
        $this->show = $page->show();
        $this->uid = UID;
        $this->meta_title = '题库管理首页';
        $this->display();
    }
    
    //添加题目
    public function add(){
        if(IS_POST){
            $courseExam = D('Common/CourseExam');
            $id = $courseExam->addInfoCacheClean();
            !$id && $this->error($courseExam->getError() ? $courseExam->getError() : '新增失败');
            
            action_log('add_Course_Exam', 'Course_Exam', $id, UID);
            $this->success('新增成功', U('CourseExam/index'));
        } else {
            $this->meta_title = '新增题目';
            $this->display();
        }
    }
    
    /*
     * 编辑题目
     * */
    public function edit(){
        if(IS_POST){
            //如果试题所在的分院不属于当前管理员所在的分院和下属分院，则没有权限编辑
            $branchId = session('courseExamBranchId');
            (is_null($branchId) || !in_array($branchId, $this->branchIds)) && $this->error('您没有权限！');
            
            //如果课程所在的分院不属于当前管理员所在的分院和下属分院，则没有权限选择
            $courseId = I('cre_courseId', 0, 'intval');
            $courseBranchId = M('course')->getFieldByCoId($courseId, 'co_branchId');
            (is_null($courseBranchId) || !in_array($courseBranchId, $this->branchIds)) && $this->error('您没有权限选择该课程！');
            
            $courseExam = D('Common/CourseExam');
            $res = $courseExam->editInfoCacheClean();
            $res === false && $this->error($courseExam->getError() ? $courseExam->getError() : '编辑失败');
            
            session('courseExamBranchId', null);
            //记录行为
            action_log('update_Course_Exam', 'Course_Exam', I('cre_id', 0, 'intval'), UID);
            $this->success('编辑成功', U('CourseExam/index'));
        }else {
            $id = I('id', 0, 'intval');
            $id == 0 && $this->error('非法参数');
            $fields = array('cre_id', 'cre_name', 'cre_a', 'cre_b', 'cre_c', 'cre_d', 'cre_answer', 'cre_description','cre_type', 'cre_isPublic', 'cre_courseId', 'branchId', 'co_name');
            $info = D('Common/CourseExam')->getCourseExamByIdCache($id, $fields);
            session('courseExamBranchId', $info['branchId']);
            
            $this->courseName = $info['co_name'];
            $this->meta_title = '编辑题目';
            $this->info = $info;
            $this->display('add');
        }
    }
    
    //查看
    public function view(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        
        $fields = array('cre_id', 'cre_name', 'cre_a', 'cre_b', 'cre_c', 'cre_d', 'cre_answer', 'cre_description','cre_type', 'cre_isPublic', 'cre_courseId', 'co_name', 'branchId');
        $info = D('Common/CourseExam')->getCourseExamByIdCache($id, $fields);
        
        $this->courseName = $info['co_name'];
        $this->meta_title = '编辑题目';
        $this->info = $info;
        $this->disabled = 'disabled';
        $this->readonly = 'readonly';
        $this->display();
    }
    
    /* 删除题目
     * @param $id 题目id
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'非法参数'));
            $ids = !is_array($id) ? $id : implode(',', $id);
            
            $res = D('Common/CourseExam')->delInfoCacheClean($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限！'), 'json');
            !$res && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('delete_Course_Exam', 'Course_Exam', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else{
            $this->error('非法操作');
        }
    }
    
    /*
     * 题目共享
     * @param $id 题目id
     * @param $act 是否共享    0：取消共享   1：共享
     * */
    public function share(){
        if(IS_AJAX){
            $id = I('id', 0, 'intval');
            $act = I('act', 0, 'intval');
            
            $res = D('Common/CourseExam')->shareHandleCacheClean($id, $act);
            
            $action = $act == 1 ? '共享' : '取消共享';
            $actionName = $act == 1 ? 'SHARE' : 'CANCEL';
            if($res){
                //记录行为
                action_log($actionName.'_COURSE_EXAM', 'Course_Exam', $id, UID);
                $data = array('status'=>1, 'msg'=>$action.'成功');
            }else {
                $data = array('status'=>0, 'msg'=>$action.'失败');
            }
            $this->ajaxReturn($data, 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
    
}
