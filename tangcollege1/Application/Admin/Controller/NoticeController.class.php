<?php

namespace Admin\Controller;
use Think\Page;
/**
 * 后台公告管理控制器
 */
class NoticeController extends AdminController {

    /**
     * 公告管理首页
     */
    public function index(){
        $this->meta_title = '公告首页';
        $model = D('Notice');
        $fields = array('n_id', 'username', 'n_content', 'br_name', 'n_updateTime', 'n_createTime');
        $where['n_branchId'] = array('in', implode(',', $this->branchIds));
        $count = $model->where($where)->count();
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        $result = $model->getNoticeDataCache($fields, $where);
        foreach ($result as $key=>&$val){
            if(mb_strlen($val['n_content'], 'UTF-8') > 50){
                $val['n_content'] = mb_substr(trim($val['n_content'], '&nbsp;'), 0, 50, 'UTF-8').'...';
            }
        }
        
        $this->result = $result;
        $this->show = $page->show();
        $this->display();
    }
    
    public function add(){
        if(IS_POST){
            $course = D('Notice');
            $id = $course->addInfoCacheClean();
            !$id && $this->error($course->getError() ? $course->getError() : '新增失败');
            
            //记录行为
            action_log('add_Notice', 'Notice', $id, UID);
            $this->success('新增成功', U('Notice/index'));
        } else {
            $Brach = D('Common/Branch');
            $branch = $Brach->recursion(BRANCHID);
            $this->meta_title = '新增公告';
            $this->assign('branch',json_encode($branch));
            $this->display('add');
        }
    }
    
    /*
     * 编辑公告
     * */
    public function edit(){
        if(IS_POST){
            $branchId = session('notice_branchId');
            (is_null($branchId) || !in_array($branchId, $this->branchIds)) && $this->error('您没有权限！');
            
            $course = D('Notice');
            $res = $course->editInfoCacheClean();
            $res === false && $this->error($course->getError() ? $course->getError() : '编辑失败');
            
            action_log('update_Notice', 'Notice', I('n_id'), UID);
            $this->success('编辑成功', U('Notice/index'));
        }else {
            $id = I('id', 0, 'intval');
            $fields = array('n_id', 'n_content', 'n_branchId');
            $info = D('Notice')->getNoticeDataByIdCache($id, $fields);
            
            //分院
            $branchId = $info['n_branchId'];
            $Brach = D('Common/Branch');
            $branch = $Brach->recursion(BRANCHID);
            
            session('notice_branchId', $branchId);
            $this->meta_title = '编辑公告';
            $this->info = $info;
            $this->assign('branch',json_encode($branch));
            $this->branchId = $branchId;
            $this->branchName = M('branch')->getFieldByBrId($branchId, 'br_name');
            $this->display('add');
        }
    }
    
    /* 删除公告
     * @param $id 公告id集   type : array/int
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            $ids = !is_array($id) ? $id : implode(',', $id);
            
            $res = D('Notice')->delNoticeCacheClean($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限!'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('delete_Notice', 'Notice', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
}
