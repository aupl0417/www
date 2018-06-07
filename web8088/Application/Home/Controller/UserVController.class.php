<?php
namespace Home\Controller;
use Common\Controller\CommonController;


/**
 *
 * @author user
 *
 */
class UserVController extends CommonController {

    public $pushimgstatus=0;

	/**
	 * 个人申请认证与手机认证选择
	 */
	public function idenc(){
		$this->display('identifi_first');
	}


	/**
	 * 申请大V选择
	 */
	public function bigIden(){
		$this->display('bigV');
	}



	/**
	 * 个人申请，上穿个人认证材料,提交的文件处理
	 */
	public function perIden(){
		$userv	= D('UserV');
		$uid    = session('user.id');			//用户ID
		$userVInfo= $userv->uservinfo($uid);		//获取old的资料路径,0表示没有查找到该记录
		if ($userVInfo&&$userVInfo['vtype']==2){
		    $pushimgstatus='您已社团认证，无法再进行个人认证';
		}else {
    		$oldpath=$userVInfo['vpath'];
    		$pushimgstatus=0;
    		if (I('get.uploadfile')=='do') {
    		        $rel	= $userv->uploadFileV();
    		        if ($rel[1]==0) {				//图片是否上传成功
    		            $pushimgstatus=$rel[0];   //上传不成功，提示上传的错误信息
    		        }else {
    		            $unfile = $userv->unlinkfile($oldpath); //删除old资料
    		            $_POST['vpath']=$rel[0];				//赋值用于创建对象
    		            $result = $userv->pathFileV($uid,1);
    		            if ($result!==true) {		//是否资料入库了
    		                $pushimgstatus=$result;//资料没有入库，提示数据库错误信息
    		            }else {
    		                $oldpath=$rel[0];
    		                $pushimgstatus=$result;   //全部完成，成功操作后，提示成功信息
    		            }
    		        }
    		    }
		}
		$this->assign('pushimgstatus',$pushimgstatus);
		$this->assign('oldpath',$oldpath);
		$this->display('person_iden');
	}



	//个人申请资料信息入库----未
	public function perIdenV(){
		$rel	=D('UserV');
		$result	=$rel->pathFileV();
		if ($result!==true) {
			$this->ajaxReturn(
					array(
						'status'=>400,
						'message'=>$result
					)
			);
		}
		$this->ajaxReturn(
				array(
						'status'=>200,
						'message'=>'资料已上传，请等待审核'
				)
		);
	}
//----------------


//============
	/**
	 * 社团申V，上传社团认证材料
	 */
	public function stIden(){
		$userv	= D('UserV');
		$uid    = session('user.id');			//用户ID
		$userVInfo= $userv->uservinfo($uid);		//获取old的资料路径,0表示没有查找到该记录
		if ($userVInfo&&$userVInfo['vtype']==1){
		    $pushimgstatus='您已个人认证，无法再进行社团认证';
		}else {
    		$oldpath=$userVInfo['vpath'];
    		$pushimgstatus=0;
    		if (I('get.uploadfile')=='do') {				//赋值用于创建对象
        			$rel	= $userv->uploadFileV();
        			if ($rel[1]==0) {				//图片是否上传成功
        			    $pushimgstatus=$rel[0];
        			}else {
        			    $unfile = $userv->unlinkfile($oldpath); //删除old资料
        			    $_POST['vpath']=$rel[0];
        			    $result = $userv->pathFileV($uid,2);

        			    if ($result!==true) {		//是否资料入库了
        			        $pushimgstatus=$result;
        			    }else {
        			        $oldpath=$rel[0];
        			        $pushimgstatus=$result;
        			    }
        			}
    		}				//赋值用于创建对象
		}
		$this->assign('pushimgstatus',$pushimgstatus);
		$this->assign('oldpath',$oldpath);
	    $this->display('tuan_iden');

	}


	/**
	 * 个人申请中的，手机认证,发送验证码  页面
	 */
	public function phoneIden(){
	    $uid=session('user.id');
	    $User = D('User');
	    $check_tel_status=$User->checkTelStatus($uid);
	    $this->assign('check_tel_status',$check_tel_status);
		$this->display('phone_iden');
	}

	/**
	 * ajax发送手机验证码
	 */
	public function phoneCodeSend(){
		$tel = I('post.tel');
		$uid = session('user.id');
		$smsSend = D('Usertelactive');
		$checkSms=$smsSend->sendActiveTel($uid,$tel);
		if ($checkSms!==true){
		    $this->ajaxReturn(array(
	            'status'   => 400,
	            'msg'      => $checkSms,
	        ));
		}
		$this->ajaxReturn(array(
	        'status'   => 200,
	        'msg'      => $checkSms,
	    ));
	}


	/**
	 * 手机认证,验证验证码
	 */
	public function phonecode(){
		$code = I('post.code');
		$uid = session('user.id');
		$smsSend = D('Usertelactive');
		$smsStatus=$smsSend->checktrue($code,$uid);
		if ($smsStatus!==true){
		    $this->ajaxReturn(
		        array('status'=>400,'msg'=>$smsStatus)
		    );
		}
		$this->ajaxReturn(
		    array('status'=>200,'msg'=>$smsStatus)
		);
	}


}




