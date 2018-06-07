<?php

namespace Admin\Controller;
use Common\Model\StudentModel;
use Common\Model\ClassModel;
use Common\Model\BranchModel;
use User\Api\UserApi;
use Think\Page;
/**
 * 后台学生管理控制器
 */
class StudentController extends AdminController {
    protected $studentModel;
    public function __construct() {
        $this->studentModel =  new StudentModel();
        parent::__construct();
    }
    
    public function index(){
        //该分院及下属分院的学生证书
        $model = D('StudentCertificate');
        $where['se_branchId'] = array('in', implode(',', $this->branchIds));
        $where['se_createPersonId'] = array('eq', UID);
        $where['_logic'] = 'or';
        
        $count = $model->where($where)->count();
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        
        $field = array('se_id', 'uma.username as studentName', 'br_name as branchName', 'cl_name as className', 'umb.username as cerMaker', 'se_isDown', 'se_downTime', 'se_url', 'cl_name as className', 'se_createTime', 'se_updateTime');
        $data = $model->getStudentCertificateListCache($field, $where, $limit);
        
        $this->meta_title = '学生证书管理';
        $this->result = $data;
        $this->display();
    }
	
    //添加学生证书
    public function add(){
        if(IS_POST){
            $se_url = I('se_url');
            $se_url['0'] == '' && $this->error('请选择证书');
            $model = D('StudentCertificate');
            $res = $model->addInfoCacheClean($se_url[0]);
            
            $res == 2 && $this->error('学生与班级不符');
            $res == 3 && $this->error('该学生未毕业');
            $res == 4 && $this->error('该学生已经无效');
            !$res && $this->error($model->getError() ? $model->getError() : '添加失败');
            
            action_log('Add_Student_Certificate', 'Student_Certificate', $res, UID);
            $this->success('添加成功', U('index'));
        }else {
            $this->meta_title = '添加学生证书';
            $this->uploadData = array('title'=>'上传证书', 'action'=>'add', 'name'=>'se_url', 'url'=>'', 'type'=>'image');//上传文件插件配置
            $this->display();
        }
    }
    
    //编辑学生证书
    public function edit(){
        if(IS_POST){
            $se_url = I('se_url');
            $se_url['0'] == '' && $this->error('请选择证书');
            $branchId = M('class')->getFieldByClId(I('se_classId'), 'cl_branchId');
            (is_null($branchId) || !in_array($branchId, $this->branchIds)) && $this->error('您没有权限！');
            
            $model = D('StudentCertificate');
            $res = $model->editInfoCacheClean($se_url[0], $branchId);
            
            $res == 2 && $this->error('学生与班级不符');
            $res == 3 && $this->error('该学生未毕业');
            $res == 4 && $this->error('该学生已经无效');
            $res === false && $this->error($model->getError() ? $model->getError() : '编辑失败');
            
            $seUrlImage = session('se_url_image');
            $result = M('Picture')->where(array('path'=>$seUrlImage))->delete();
            if($result){
                unlink($seUrlImage);
            }
            action_log('Update_Student_Certificate', 'Student_Certificate', I('se_id', 0, 'intval'), UID);
            $this->success('编辑成功', U('index'));
        }else {
            $id = I('id', 0, 'intval');
            $id == 0 && $this->error('非法参数');
            
            //证书数据
            $field = array('se_id', 'se_studentId', 'se_branchId', 'se_createPersonId', 'se_url', 'se_classId', 'cl_name', 'username as studentName');
            $info = D('StudentCertificate')->getStudentCertificateByIdCache($id, $field);
            session('se_url_image', $info['se_url']);//保存证书地址，确保在更换图片后，删除原来的图片
            
            $this->info = $info;
            $this->meta_title = '编辑学生证书';
            $this->uploadData = array('title'=>'编辑证书', 'action'=>'edit', 'name'=>'se_url', 'url'=>'', 'type'=>'image');//上传文件插件配置
            $this->display('add');
        }
        
    }
    
    public function view(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        
        //证书数据
        $field = array('se_id', 'se_studentId', 'se_branchId', 'se_createPersonId', 'se_url', 'se_classId', 'cl_name', 'username as studentName');
        $info = D('StudentCertificate')->getStudentCertificateByIdCache($id, $field);
        
        $this->info = $info;
        $this->meta_title = '编辑学生证书';
        $this->uploadData = array('title'=>'编辑证书', 'action'=>'edit', 'name'=>'se_url', 'url'=>'', 'type'=>'image');//上传文件插件配置
        $this->display();
    }
    
    /*
     * 删除学生证书
     * @param $id 学生证书id集   type : int/array
     * return json
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            $ids = !is_array($id) ? $id : implode(',', $id);
            
            $res = D('StudentCertificate')->delInfo($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=> '您没有权限！'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=> '删除失败'), 'json');
            
            action_log('Delete_Student_Certificate', 'Student_Certificate', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=> '删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
	
	public function lists() {
		$pageSize =  C('SHOW_PAGE_SIZE');
		$where = [];
		if(BRANCHID != 0) {
		  $where[] = 'cl_branchId in('.implode(',',$this->branchIds).') or branchId in('.implode(',',$this->branchIds).')';
		}
		$words = I('get.words');
		$words = isset($words) ? trim($words) : '';
		if(!empty($words)) {
		  if(preg_match('/^[1-9]\d*$/',$words)) {
			$where[] ='(id ="'.$words.'" or username like "%'.$words.'%")'; 
		  }else{
			$where[] ='username like "%'.$words.'%"';   
		  }
		}
		$where = implode(' and ',$where);
		$count = $this->studentModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->studentModel->lists($where,'','',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->studentModel->lists($where);
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
	}
	public function delstu($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		}
		if(BRANCHID == 0){
		   if($this->studentModel->delInfoCacheClean($id)){
		      $this->success('删除成功！');
	       }else{
		     $this->error('删除失败！');  
		   }
		}else{
		   if($this->studentModel->cutOutStudentFromBranchCacheClean($id,$this->branchIds)){
		      $this->success('删除成功！');
	       }else{
		     $this->error('删除失败！');  
		   }
		}
		
	}
	public function getAddStudentTempletByAjax($classId='',$password='',$repassword='') {
		   if(IS_POST){
			    /* 检测密码 */
               if($password != $repassword){
                 $this->error('密码和重复密码不一致！');
               }
			   $data = I('post.');
			   $data['branchId'] = BRANCHID;
               if(!$this->studentModel->addInfoCacheClean($data)){
					$errorMsg = empty($this->studentModel->getError()) ? '学生添加失败' : $this->studentModel->getError();
			        $this->error($errorMsg); 
                } else {
                    $this->success('学生添加成功！',U('lists'));
                }
		    }else{
			   if(!empty($classId)) {
				 $classModel = new classModel();
				 $info = $classModel->info($classId,'cl_name');
				 if(!empty($info)) {
					$classInfo['name'] = $info['cl_name'];
					$classInfo['id'] = $classId;
				    $this->assign('classInfo',$classInfo);
				 }
			   }
			  $this->display(__FUNCTION__); 
		 }
		
	}
	public function getStudentTempletByAjax() {
		$Brach = new BranchModel();
		$brachLists = $Brach->recursionCache(BRANCHID);
		$this->assign('brachLists',json_encode($brachLists));
		$this->display(__FUNCTION__);
	}
	
	public function getStudentListByAjax() {
		$fields = I('get.');
		$map = [];
		if(!empty($fields['classId'])) {
			$map[] = 'cs_classId="'.$fields['classId'].'"';
		}
		
		if($fields['words']!='') {
			$map[] = 'username like "%'.$fields['words'].'%"';	
		}
		$map[] = 'cl_branchId in('.implode(',',$this->branchIds).')';
		$lists = $this->studentModel->getStudentAndClass(!empty($map) ? implode(' and ',$map) : '');
		$this->ajaxReturn($lists, 'json');
		
	}
	public function getEditStudentTempletByAjax($id='', $password='',$repassword='') {
		 if(empty($id)) {
			 $this->error('ID不能为空');
		 }
		 $where = 'stu_userId ="'.$id.'" and branchId in('.implode(',',$this->branchIds).')';
		 if(IS_POST){
			$isExists = $this->studentModel->infoCount($where); 
			if(!$isExists) {
				$this->error('信息不存在！');
			}
			$data   =   I('post.'); 
            /* 检测密码 */
            if(!(empty($password) && empty($repassword)) && ($password != $repassword)){
                $this->error('密码和重复密码不一致！');
            }else{
				unset($data['password']);
			}      
            if(!$this->studentModel->editInfoCacheClean($id,$data)){  //暂时没做实物回滚 先简单处理
			    $errorMsg = empty($this->studentModel->getError()) ? '学生更新失败' : $this->studentModel->getError();
			    $this->error($errorMsg); 
            }else{
                $this->success('学生更新成功！',U('index'));
            }
		 }else{
			$info = $this->studentModel->info($where);
			if(empty($info)) {
				 $this->error('信息不存在！');
			}
			$this->assign('info',$info);
			$this->display(__FUNCTION__); 
		 }
		
	}
	
    
}
