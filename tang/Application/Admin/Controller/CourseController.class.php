<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Admin\Model\AuthGroupModel;

/**
 * 后台课程管理控制器
 */
class CourseController extends AdminController {

    /**
     * 课程管理首页
     */
    public function index(){
        if(UID){
            $this->meta_title = '课程管理首页';
            $result = D('Course')->getCourseData(10);
            $this->result = $result['result'];
            $this->show = $result['show'];
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
    public function add(){
        if(IS_POST){
            $icon = I('post.icon');
            $course = D('Course');
            $data = $course->create();
            if(!empty($icon[0])){
                $data['co_logo'] = $icon[0];
            }
            if($data){
                $id = $course->add($data);
                if($id){
                    //记录行为
                    action_log('add_Course', 'Course', $id, UID);
                    $this->success('新增成功', U('Course/index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($course->getError());
            }
        } else {
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            $this->meta_title = '新增课程';
            $this->display('add');
        }
        
    }
    
    /*
     * 编辑课程
     * */
    public function edit(){
        if(IS_POST){
            $icon = I('post.icon');
            $course = D('Course');
            $data = $course->create();
            if(!empty($icon[0])){
                $data['co_logo'] = $icon[0];
            }
//             dump($data);die;
            if($data){
                $res = $course->save($data);
                if($res){
                    //记录行为
                    action_log('update_couse', 'Course', I('co_id'), UID);
                    $this->success('编辑成功', U('Course/index'));
                }else {
                    $this->error('编辑失败');
                }
            }
        }else {
            $id = I('id', 0);
            $fields = array('co_id', 'co_name', 'co_content', 'co_description', 'co_score', 'co_logo');
            $info = D('Course')->getCourseDataById($id, $fields);
            
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            
            $this->assign('Menus', $menus);
            $this->meta_title = '编辑课程';
            $this->info = $info;
            $this->display();
        }
    }
    
    /* 删除课程
     * @param $id 课程id
     * */
    public function del(){
        $id = I('id', 0);
        $res = D('Course')->delCourse($id);
        if($res){
            //记录行为
            action_log('delete_couse', 'Course', I('co_id'), UID);
            $this->success('删除成功', Cookie('__forward__'));
        }else {
            $this->error('删除失败');
        }
    }
    
}
