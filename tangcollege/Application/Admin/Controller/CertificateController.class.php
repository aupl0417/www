<?php

namespace Admin\Controller;
use Think\Page;
/**
 * 后台证书管理控制器
 */
class CertificateController extends AdminController {

    /**
     * 证书管理首页
     */
    public function index(){
        $this->meta_title = '课程管理首页';
        $model = D('CertificateTemplate');
        $where['cce_branchId'] = array('in', implode(',', $this->branchIds));
        $count = $model->where($where)->count();
        
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        
        $fields = array('cce_id', 'cce_type', 'cce_url', 'cce_createTime', 'cce_updateTime', 'gr_name');
        $result = $model->getCertificateDataCache($fields, $where, $limit);
        
        $this->result = $result;
        $this->show = $page->show();
        $this->display();
    }
    
    /*
     * 添加证书
     * */
    public function add(){
        if(IS_POST){
            $icon = I('post.cce_url', '');
            !$icon[0] && $this->error('请上传证书');
            
            $model = D('CertificateTemplate');
            $id = $model->addInfoCacheClean();
            !$id && $this->error($model->getError() ? $model->getError() : '新增失败');
            
            //记录行为
            action_log('add_Certificate', 'CertificateTemplate', $id, UID);
            $this->success('新增成功', U('Certificate/index'));
        } else {
            $grade = D('Common/Grade')->listsCache('', array('gr_id', 'gr_name'));
            $this->grade = $grade;
            $this->meta_title = '添加证书';
            $this->uploadData = array('title'=>'上传证书', 'action'=>'add', 'name'=>'cce_url');
            $this->display('add');
        }
    }
    
    /*
     * 编辑证书
     * */
    public function edit(){
        if(IS_POST){
            $cce_url = I('post.cce_url', '');
            !$cce_url[0] && $this->error('请上传证书');
            
            $branchId = session('certificate_template_branchId');
            if(is_null($branchId) || !in_array($branchId, $this->branchIds)){
                $this->error('您没有权限！');
            }
            $model = D('CertificateTemplate');
            $res = $model->editInfoCacheClean();
            $res === false && $this->error($model->getError() ? $model->getError() : '编辑失败');
            
            //记录行为
            action_log('update_Certificate', 'Certificate', I('cce_id', 0, 'intval'), UID);
            session('certificate_template_branchId', null);
            $this->success('编辑成功', U('Certificate/index'));
        }else {
            $id = I('id', 0, 'intval');
            $id == 0 && $this->error('非法参数');
            
            $fields = array('cce_id', 'cce_gradeId', 'cce_type', 'gr_name', 'cce_url', 'cce_branchId');
            $info = D('CertificateTemplate')->getCertificateDataByIdCache($id, $fields);
            $grade = D('Common/Grade')->lists('', array('gr_id', 'gr_name'));
            
            session('certificate_template_branchId', $info['cce_branchId']);
            $this->meta_title = '编辑证书';
            $this->uploadData = array('title'=>'上传证书', 'action'=>'edit', 'name'=>'cce_url');
            $this->info = $info;
            $this->grade = $grade;
            $this->display();
        }
    }
    
    public function view(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        
        $fields = array('cce_id', 'cce_gradeId', 'cce_type', 'gr_name', 'cce_url', 'cce_branchId');
        $info = D('CertificateTemplate')->getCertificateDataByIdCache($id, $fields);
        $grade = D('Common/Grade')->lists('', array('gr_id', 'gr_name'));
        
        $this->meta_title = '查看证书';
        $this->uploadData = array('title'=>'编辑证书', 'action'=>'edit', 'name'=>'cce_url');
        $this->info = $info;
        $this->grade = $grade;
        $this->display();
    }
    
    /* 删除证书
     * @param $id 证书id集
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            $ids = !is_array($id) ? $id : implode(',', $id);
            
            $res = D('CertificateTemplate')->delCertificateCacheClean($ids, $this->branchIds);
            dump($res);die;
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限！'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('Delete_Certificate_Template', 'Certificate_Templet', $ids, UID);
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
}
