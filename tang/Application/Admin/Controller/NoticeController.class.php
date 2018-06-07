<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Admin\Model\AuthGroupModel;

/**
 * 后台公告管理控制器
 */
class NoticeController extends AdminController {

    /**
     * 课程管理首页
     */
    public function index(){
        if(UID){
            $this->meta_title = '公告首页';
            $fields = array('n_id', 'username', 'n_content', 'br_name', 'n_updateTime', 'n_createTime');
            $result = D('Notice')->getNoticeData(10, $fields);
            $this->result = $result['result'];
            $this->show = $result['show'];
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
    public function add(){
        if(IS_POST){
            $course = D('Notice');
            $data = $course->create();
            if($data){
                $id = $course->add();
                if($id){
                    //记录行为
                    action_log('add_Notice', 'Notice', $id, UID);
                    $this->success('新增成功', U('Notice/index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($course->getError());
            }
        } else {
            $branch = array(
                array('br_id'=>1, 'br_name'=>'分院1'),
                array('br_id'=>2, 'br_name'=>'分院2'),
                array('br_id'=>3, 'br_name'=>'分院3'),
            );
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            $this->meta_title = '新增公告';
            $this->branch = $branch;
            $this->display('add');
        }
        
    }
    
    /*
     * 编辑课程
     * */
    public function edit(){
        if(IS_POST){
            $course = D('Notice');
            $data = $course->create();
            if($data){
                $res = $course->save();
                if($res){
                    //记录行为
                    action_log('update_Notice', 'Notice', I('n_id'), UID);
                    $this->success('编辑成功', U('Notice/index'));
                }else {
                    $this->error('编辑失败');
                }
            }
        }else {
            $id = I('id', 0);
            $fields = array('n_id', 'n_content', 'n_branchId');
            $info = D('Notice')->getNoticeDataById($id, $fields);
            
            $branch = array(
                array('br_id'=>1, 'br_name'=>'分院1'),
                array('br_id'=>2, 'br_name'=>'分院2'),
                array('br_id'=>3, 'br_name'=>'分院3'),
            );
            
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            
            $this->assign('Menus', $menus);
            $this->meta_title = '编辑公告';
            $this->info = $info;
            $this->branch = $branch;
            $this->display('add');
        }
    }
    
    /* 删除课程
     * @param $id 课程id
     * */
    public function del(){
        $id = I('id', 0);
        $res = D('Notice')->delNotice($id);
        if($res){
            //记录行为
            action_log('delete_Notice', 'Notice', $id, UID);
            $this->success('删除成功', U('Notice/index'));
        }else {
            $this->error('删除失败');
        }
    }
}
