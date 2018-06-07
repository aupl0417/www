<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Admin\Model\AuthGroupModel;

/**
 * 后台课程管理控制器
 */
class CertificateController extends AdminController {

    /**
     * 证书管理首页
     */
    public function index(){
        if(UID){
            $this->meta_title = '课程管理首页';
            $fields = array('cce_id', 'cce_type', 'cce_url', 'cce_createTime', 'cce_updateTime', 'gr_name');
            $result = D('CertificateTemplate')->getCertificateData(10, $fields);
            foreach($result['result'] as $key=>&$val){
                $val["cce_type"] = $val['cce_type'] == '0' ? '电子文档证书' : '实物文件邮寄';
            }
            $this->result = $result['result'];
            $this->show = $result['show'];
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
    /*
     * 添加证书
     * */
    public function add(){
        if(IS_POST){
            $gradeId = I('post.cce_gradeId', 0, 'intval');
            $type = I('post.cce_type', 0, 'intval');
            $icon = I('post.icon', '');
            !$gradeId && $this->error('请选择年级');
            !$icon[0] && $this->error('请上传证书');
            $data = array(
                'cce_gradeId' => $gradeId,
                'cce_type' => $type,
                'cce_url' => $icon[0],
                'cce_createTime' => date('Y-m-d H:i:s'),
                'cce_updateTime' => date('Y-m-d H:i:s'),
            );
            $id = M('certificate_templet')->add($data);
            if($id){
                //记录行为
                action_log('add_Certificate', 'CertificateTemplate', $id, UID);
                $this->success('新增成功', U('Certificate/index'));
            } else {
                $this->error('新增失败');
            }
        } else {
            $grade = array(
                array('gr_id'=>1, 'gr_name'=>'一年级'),
                array('gr_id'=>2, 'gr_name'=>'二年级'),
                array('gr_id'=>3, 'gr_name'=>'三年级'),
            );
            $this->grade = $grade;
            $this->meta_title = '添加证书';
            $this->display('add');
        }
        
    }
    
    /*
     * 编辑证书
     * */
    public function edit(){
        if(IS_POST){
            $gradeId = I('post.cce_gradeId', 0, 'intval');
            $type = I('post.cce_type', 0, 'intval');
            $icon = I('post.icon', '');
            !$gradeId && $this->error('请选择年级');
            !$icon[0] && $this->error('请上传证书');
            $data = array(
                'cce_gradeId' => $gradeId,
                'cce_type' => $type,
                'cce_url' => $icon[0],
                'cce_updateTime' => date('Y-m-d H:i:s'),
            );
            
            $res = M('certificate_templet')->save($data);
            if($res){
                //记录行为
                action_log('update_Certificate', 'Certificate', I('cce_id'), UID);
                $this->success('编辑成功', U('Course/index'));
            }else {
                $this->error('编辑失败');
            }
        }else {
            $id = I('id', 0);
            $fields = array('cce_id', 'cce_gradeId', 'cce_type', 'gr_name', 'cce_url');
            $info = D('CertificateTemplate')->getCertificateDataById($id, $fields);
            $grade = array(
                array('gr_id'=>1, 'gr_name'=>'一年级'),
                array('gr_id'=>2, 'gr_name'=>'二年级'),
                array('gr_id'=>3, 'gr_name'=>'三年级'),
            );
            
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            
            $this->assign('Menus', $menus);
            $this->meta_title = '编辑证书';
            $this->info = $info;
            $this->grade = $grade;
            $this->display('add');
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
