<?php
/*  注意：
 * 函数名 + Cache ： 表示将该函数返回的数据加入缓存
 * 函数名 + Cache ： 表示将该Model所对应的缓存清除
 **/
/**
 * 后台课程分类管理控制器
 */
namespace Admin\Controller;
use Think\Page;

class CourseTypeController extends AdminController {

    /**
     * 课程分类管理首页
     */
    public function index(){
        $this->meta_title = '课程分类管理首页';
        $model = D('Common/StudyDirection');
        
        $count = $model->count();
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        $result = $model->getStudyDirectionListCache(true, '', $limit);
        
        $this->result = $result;
        $this->show = $page->show();
        $this->display();
    }
    
    /*
     * 添加课程分类
     * */
    public function add(){
        if(IS_POST){
            $course = D('Common/StudyDirection');
            $id = $course->addInfoCacheClean();
            !$id && $this->error($course->getError() ? $course->getError() : '新增失败');
            
            action_log('add_Course_Type', 'Course_Type', $id, UID);
            $this->success('新增成功', U('CourseType/index'));
        } else {
            $this->meta_title = '新增课程分类';
            $this->display('add');
        }
        
    }
    
    /*
     * 编辑课程分类
     * */
    public function edit(){
        if(IS_POST){
            $id = I('sd_id', 0, 'intval');
            empty($id) && $this->error('非法参数');
            $course = D('Common/StudyDirection');
            $res = $course->editInfoCacheClean();
            $res === false && $this->error($course->getError() ? $course->getError() : '编辑失败');
            
            action_log('update_course', 'Course', $id, UID);
            $this->success('编辑成功', U('index'));
        }else {
            $id = I('id', 0, 'intval');
            empty($id) && $this->error('非法参数');
            $where = array('sd_id' => $id);
            $info = D('Common/StudyDirection')->getCourseTypeDataOneCache($where);
            
            $this->meta_title = '编辑课程分类';
            $this->info = $info;
            $this->display('add');
        }
    }
    
    /* 删除课程分类
     * @param $id 课程分类id集
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            
            $ids = !is_array($id) ? $id : implode(',', $id);
            $res = D('Common/StudyDirection')->delInfoCacheClean($ids);
            if($res){
                action_log('Delete_Study_Direction', 'StudyDirection', $ids, UID);//记录行为
                $data = array('status'=>1, 'msg'=>'删除成功');
            }else {
                $data = array('status'=>0, 'msg'=>'删除失败');
            }
            $this->ajaxReturn($data, 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
}
