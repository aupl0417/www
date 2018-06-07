<?php

namespace Admin\Controller;
use Think\Page;

/**
 * 后台留言管理控制器
 */
class FeedbackController extends AdminController {
    
    /**
     * 课程管理首页
     */
    public function index(){
        $this->meta_title = '留言管理首页';
        
        $where['f_branchId'] = array('in', $this->branchIds);
        $count = D('Common/Feedback')->where($where)->count();
        $page = new Page($count, C("SHOW_PAGE_SIZE"));
        $limit = $page->firstRow . ',' . $page->listRows;
        $fields = array('f_id', 'f_name','f_content', 'f_email', 'br_name','username', 'f_createTime', 'f_state');
        $result = D('Common/Feedback')->getFeedbackData($fields, $where, $limit);
        
        $this->result = $result;
        $this->show = $page->show();
        $this->uid = UID;
        
        $this->display();
    }
    
    //添加留言
    public function add(){
        if(IS_POST){
            $feedback = D('Common/Feedback');
            $data = $feedback->create();
            if($data){
                $id = $feedback->add($data);
                if($id){
                    action_log('add_Feedback', 'Feedback', $id, UID);
                    $this->success('新增成功', U('Feedback/index'));
                } else {
                    $this->error('新增失败');
                }
            }else {
                $this->error($feedback->getError());
            }
        } else {
            $info['f_email'] = M('UcenterMember')->where(array('id'=>UID))->getField('email');
            $this->meta_title = '新增留言';
            $this->info = $info;
            $this->display();
        }
        
    }
    
    /*
     * 编辑留言
     * */
    public function edit(){
        if(IS_POST){
            $feedback = D('Common/Feedback');
            $data = $feedback->create();
            if($data){
                $res = $feedback->save();
                if($res){
                    //记录行为
                    action_log('update_Feedback', 'Feedback', I('f_id'), UID);
                    $this->success('编辑成功', U('Feedback/index'));
                }else {
                    $this->error('编辑失败');
                }
            }
        }else {
            $id = I('id', 0);
            $fields = array('f_id', 'f_name','f_content', 'f_email');
            $info = D('Common/Feedback')->getFeedbackById($id, $fields);
            $this->meta_title = '编辑留言';
            $this->info = $info;
            $this->display('add');
        }
    }
    
    
    /*
     * 查看/审核留言
     * @param $id 留言id
     * */
    public function review(){
        if(IS_POST){
            $state = I('f_state', 0, 'intval');
            //$state == 0 && $this->error('请选择审核状态');
            $feedback = D('Common/Feedback');
            $res = $feedback->reviewInfoCacheClean();
            $res === false && $this->error($feedback->getError() ? $feedback->getError() : '操作失败');
            
            action_log('View_Feedback', 'Feedback', I('f_id'), UID);
            $this->success('操作成功', U('Feedback/index'));
        }else {
            $id = I('id', 0, 'intval');
            $id == 0 && $this->error('非法参数');
            $isReview = I('is_review', 0, 'intval');
            $fields = array('f_id', 'f_name','f_content', 'f_email', 'f_state');
            $info = D('Common/Feedback')->getFeedbackByIdCache($id, $fields);
            $this->meta_title = $isReview == 1 ? '审核留言' : '查看留言';
            $this->info = $info;
            $this->is_review = $isReview;
            $this->display();
        }
    }
    
    /* 删除留言
     * @param $id 留言id
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'非法参数'));
            $ids = !is_array($id) ? $id : implode(',', $id);
            $res = D('Common/Feedback')->delInfoCacheClean($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限！'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('delete_Feedback', 'Feedback', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
}
