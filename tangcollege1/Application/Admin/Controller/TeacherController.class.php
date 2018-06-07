<?php

namespace Admin\Controller;
use Common\Model\BranchModel;
use Common\Model\TeacherModel;
use Common\Model\TeacherLevelModel;
use User\Api\UserApi;
/**
 * 后台班级课时管理控制器
 */
class TeacherController extends AdminController {
   protected $branchModel;
   protected $teacherModel;
   protected $teacherLevelModel;
   public function __construct() {
	   $this->branchModel =  new BranchModel();	
	   $this->teacherModel =  new TeacherModel();
	   $this->teacherLevelModel =  new TeacherLevelModel();
       parent::__construct();
    }
   
    public function index(){
		$pageSize = C('SHOW_PAGE_SIZE');
		$where = 'branchId in('.implode(',',$this->branchIds).')';
		$count = $this->teacherModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->teacherModel->lists($where,'','',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->teacherModel->lists($where);
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function add($username = '', $password = '', $repassword = '', $email = '',$mobile = '',$branchId=0) {
		 if(IS_POST){
			 if(!empty($branchId) && !in_array($branchId,$this->branchIds)) {
				$this->error('非法操作！'); 
			 }
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }
            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email,$mobile,1,$branchId);
            if(0 < $uid){ 
			    $data   =   I('post.');
				$data['te_userId']  = $uid;
                if(!$this->teacherModel->addInfoCacheClean($data)){
					$User->deleteInfo($uid);   //暂时没做实物回滚 先简单处理
					$errorMsg = empty($this->teacherModel->getError()) ? '教师添加失败' : $this->teacherModel->getError();
			        $this->error($errorMsg); 
                } else {
                    $this->success('教师添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
			    $this->error($this->showRegError($uid));  
            }
        } else {
			$Brach = new BranchModel();
			$brachLists = $Brach->recursionCache(BRANCHID);
			$levelLists = $this->teacherLevelModel->lists(); 
			$this->assign('brachLists',json_encode($brachLists));
			$this->assign('levelLists',$levelLists);
            $this->meta_title = '新增教师';
            $this->display(__FUNCTION__);
        }
	}
	public function lists($page=0,$banchId=0,$isAjaxWay=true) {
		$fields = I('get.');
		$map = [];
		$branchIds = [];
		if(!empty($fields['branchId'])) {
			$branchIds = $this->branchModel->getPosterityIdsCache($fields['branchId']) + (array)$fields['branchId'];
		}else{
			$branchIds = $this->branchIds;
		}
		$map[] =  'branchId in('.implode(',',$branchIds).')';
		if($fields['words']!='') {
			$map[] = 'username like "%'.$fields['words'].'%"';	
		}
		if(!($page > 0)) 
		    $page = 1;
		 $pageSize = C('SHOW_PAGE_SIZE');
		 $where = implode(' and ',$map);	
		 $total  = $this->teacherModel->infoCount($where);
		 $totalPage = ceil($total/$pageSize);
		 $page = max(min($page,$total),0);
		 $limit = ($page - 1)* $pageSize .',' . $pageSize;
		 $lists = $this->teacherModel->lists($where,'','',$limit);
		 $lists = !empty($lists) ? $lists : [];
		 if($isAjaxWay) {
			$returnData = compact("pageSize","totalPage","page","lists"); 
			$this->ajaxReturn($returnData,'JSON'); 
		 }else {
		    $this->assign('page',$page);
		    $this->assign('totalPage',$totalPage);
		    $this->assign('teacherList',$lists);
			$this->display(__FUNCTION__);
		 }
	}	
    public function rank() {
		$TeacherLevelModel = new TeacherLevelModel();
		$lists = $TeacherLevelModel->listsCache();
		$this->assign('lists',$lists);
		$this->display(__FUNCTION__);
		
	}
	
	public function rankedit($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		$TeacherLevelModel = new TeacherLevelModel();
		if(IS_POST) {
		  if(!$TeacherLevelModel->editInfoCacheClean($id)){  
			     $errorMsg = empty($TeacherLevelModel->getError()) ? '更新失败' : $TeacherLevelModel->getError();
			      $this->error($errorMsg); 
            } else {
                 $this->success('更新成功！',U('index'));
           }
		}else{
		  $info = $TeacherLevelModel->infoCache($id);	
		  $this->assign('info',$info);
		  $this->display(__FUNCTION__);
		}
		
	}
	
	public function rankdel($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		$TeacherLevelModel = new TeacherLevelModel();
		
		  if(!$TeacherLevelModel->delInfoCacheClean($id)){  
			      $this->error('删除失败'); 
            } else {
                 $this->success('删除成功');
           }
		
	}
	
	public function rankadd() {
		
		if(IS_POST) {
		  $TeacherLevelModel = new TeacherLevelModel();
		  if(!$TeacherLevelModel->addInfoCacheClean()){  
			     $errorMsg = empty($TeacherLevelModel->getError()) ? '添加失败' : $TeacherLevelModel->getError();
			      $this->error($errorMsg); 
            } else {
                 $this->success('添加成功！',U('index'));
           }
		}else{
		  $this->display(__FUNCTION__);
		}
		
	}
	public function del($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		if($this->teacherModel->delInfoCacheClean($id))
		  $this->success('删除成功！');
		else{
		  $this->error('删除失败！');  
		}
	}
	
	public function getTeacherTempletByAjax() {
		$this->display(__FUNCTION__);
	}
	
	public function edit($id,$username = '', $password = '', $repassword = '') {
		 if(empty($id)) {
			 $this->error('ID不能为空');
			 exit();
		 }
		 if(IS_POST){
			$data   =   I('post.'); 
            /* 检测密码 */
            if(!(empty($password) && empty($repassword)) && ($password != $repassword)){
                $this->error('密码和重复密码不一致！');
            }else{
				unset($data['password']);
			}
            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $result    =   $User->editInfoCacheClean($id,$data);
            if($result['status']){ 
                if(!$this->teacherModel->editInfoCacheClean($id,$data)){  //暂时没做实物回滚 先简单处理
					$errorMsg = empty($this->teacherModel->getError()) ? '教师更新失败' : $this->teacherModel->getError();
			        $this->error($errorMsg); 
                } else {
                    $this->success('教师添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
			    $this->error($this->showRegError($result['info']));  
            }
        } else {
			$Brach = new BranchModel();
			$brachLists = $Brach->recursionCache(BRANCHID);
			$levelLists = $this->teacherLevelModel->lists();
			$info = $this->teacherModel->infoCache($id);
			$this->assign('info',$info);
			$this->assign('brachLists',json_encode($brachLists));
			$this->assign('levelLists',$levelLists);
            $this->meta_title = '新增教师';
            $this->display(__FUNCTION__);
        }
	}
	
   
	
    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
			case -12: $error = '身份类别必须填写！'; break;
			case -13: $error = '分院必须填写！'; break;
			case -14: $error = '教师更新失败！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }
	
}
