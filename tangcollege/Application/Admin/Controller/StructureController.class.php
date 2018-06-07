<?php

namespace Admin\Controller;
use User\Api\UserApi;
use Common\Model\BranchModel;

/**
 * 后台用户控制器
 */
class StructureController extends AdminController {

     /**
     * 用户列表
     */
    public function managerList(){
        $nickname       =   I('nickname');
        $map['status']  =   array('egt',0);
        if(is_numeric($nickname)){
            $map['uid|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
        }

        $list   = $this->lists('Member', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display(__FUNCTION__);
    }

    /**
     * 修改昵称初始化
     */
    public function updateNickname(){
        $nickname = M('Member')->getFieldByUid(UID, 'nickname');
        $this->assign('nickname', $nickname);
        $this->meta_title = '修改昵称';
        $this->display(__FUNCTION__);
    }

    /**
     * 修改昵称提交
     */
    public function submitNickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error('请输入昵称');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data   =   $Member->create(array('nickname'=>$nickname));
        if(!$data){
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改昵称成功！');
        }else{
            $this->error('修改昵称失败！');
        }
    }

    /**
     * 修改密码初始化
     */
    public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display(__FUNCTION__);
    }

    /**
     * 修改密码提交
     */
    public function submitPassword(){
        //获取参数
        $password   =   I('post.old');
        empty($password) && $this->error('请输入原密码');
        $data['password'] = I('post.password');
        empty($data['password']) && $this->error('请输入新密码');
        $repassword = I('post.repassword');
        empty($repassword) && $this->error('请输入确认密码');

        if($data['password'] !== $repassword){
            $this->error('您输入的新密码与确认密码不一致');
        }

        $Api    =   new UserApi();
        $res    =   $Api->updateInfo(UID, $password, $data);
        if($res['status']){
            $this->success('修改密码成功！');
        }else{
            $this->error($res['info']);
        }
    }

    /**
     * 用户行为列表
     */
    public function managerAction(){
        //获取列表数据
        $Action =   M('Action')->where(array('status'=>array('gt',-1)));
        $list   =   $this->lists($Action);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->assign('_list', $list);
        $this->meta_title = '用户行为';
        $this->display(__FUNCTION__);
    }

    /**
     * 新增行为
     */
    public function addManagerAction(){
        $this->meta_title = '新增行为';
        $this->assign('data',null);
        $this->display('editManagerAction');
    }

    /**
     * 编辑行为
     */
    public function editManagerAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑行为';
        $this->display(__FUNCTION__);
    }

    /**
     * 更新行为
     */
    public function saveManagerAction(){
        $res = D('Action')->update();
        if(!$res){
            $this->error(D('Action')->getError());
        }else{
            $this->success($res['id']?'更新成功！':'新增成功！', Cookie('__forward__'));
        }
    }

    /**
     * 会员状态修改
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('Member', $map );
                break;
            case 'resumeuser':
                $this->resume('Member', $map );
                break;
            case 'deleteuser':
                $this->delete('Member', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function managerAdd($username = '', $password = '', $repassword = '', $email = '',$identityType=2,$branchId=0){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email,'',$identityType,$branchId);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username,'status' => 1,'branchId'=>$branchId);
                $data = D('Member')->create();
                $user = array_merge($user, $data);
//                 dump($data);die;
                if(!M('Member')->add($user)){
                    $this->error('用户添加失败！');
                } else {
                    $this->success('用户添加成功！',U('managerList'));
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
			$Brach = new BranchModel();
			$brachLists = $Brach->recursion(0); 
			$this->assign('brachLists',json_encode($brachLists));
            $this->meta_title = '新增用户';
            $this->display(__FUNCTION__);
        }
    }
	
	public function branchList() {
		$Brach = new BranchModel();
		$brachLists = $Brach->lists('br_parentId="'.BRANCHID.'"','br_id,br_parentId,br_name,br_level','br_parentId ASC',true);
		$this->assign('brachLists',$brachLists);
		$this->display(__FUNCTION__);
		
	}
	
	public function getBranchSubListByAjax($id='') {
		$id = empty($id) ? BRANCHID : $id;
		if(!in_array($id,$this->branchIds)) {
			 $this->error('您没有权限！');
		}
		$Brach = new BranchModel();
		$brachLists = $Brach->lists('br_parentId="'.$id.'"','br_id,br_parentId,br_name,br_level','br_parentId ASC',true);
		$this->ajaxReturn($brachLists,'JSON');
	}
	public function branchEdit($br_id='') {
		if(empty($br_id))
		    $this->error('id不能为空');
		if(!in_array($br_id,$this->branchIds)) {
			 $this->error('您没有权限！');
		}		
		$Brach = new BranchModel();
		$status = $Brach->editCacheClean($br_id);
		if($status){	 
		   $this->success('编辑成功！');
		}else{
		   $errorMsg = empty($Brach->getError()) ? '编辑失败' : $Brach->getError();
		   $this->error($errorMsg);
		}
	}
	
	public function branchAddByAjax($br_parentId='') {	
	    $Brach = new BranchModel();
		if(IS_POST) {
		   if(!in_array($br_parentId,$this->branchIds)) {
			 $this->error('您没有权限！');
		   }	
		   $status = $Brach->addInfoCacheClean();
		   if($status){	 
		      $this->success('添加成功！');
		   }else{
		      $errorMsg = empty($Brach->getError()) ? '添加失败' : $Brach->getError();
		      $this->error($errorMsg);
		   }
		}else{
			$brachLists = $Brach->recursionCache(BRANCHID);
		    $this->assign('brachLists',json_encode($brachLists));
		    $html = $this->fetch('branchAddForm');
		    echo $html;   	
		}
	}
	
	
	public function getBranchEditFormByAjax($id='') {
		if(!in_array($id,$this->branchIds)) {
			 $this->error('您没有权限！');
		}
		$Brach = new BranchModel();
		$brachInfo = $Brach->info($id);
		if(!empty($brachInfo)) {
		   $brachLists = $Brach->recursionCache(BRANCHID); 
		   $this->assign('brachLists',json_encode($brachLists));
		   $Area = new \Common\Model\AreaModel();
		   $areaInfo = $Area->getFullAreaCache($brachInfo['br_areaId']);
		   if(empty($areaInfo)) {
			   $brachInfo['br_area'] = 0;
		   }else{
			   $tempArr[] = ['id'=>0,'selectVal'=>$areaInfo[0]];
			   $tempArr[] = ['id'=>$areaInfo[0],'selectVal'=>$areaInfo[1]];
			   $tempArr[] = ['id'=>$areaInfo[1],'selectVal'=>$areaInfo[2]];	
			   $brachInfo['br_area'] = json_encode($tempArr); 
			   unset($tempArr);     
		   }
		   $this->assign('brachInfo',$brachInfo);
		   $html = $this->fetch('branchEditForm');
		   echo $html;
		}else{
		   $this->error('记录不存在！');	
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
            default:  $error = '未知错误';
        }
        return $error;
    }

}
