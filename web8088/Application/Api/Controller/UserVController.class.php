<?php
namespace Api\Controller;
use Api\Controller\CommonController;
/**
 *
 * @author user
 *
 */
class UserVController extends CommonController {
    
    
    /**
     * type认证类型,1个人,2社团
     * file
     */
    public function stIdenV(){
        if (!session('?user')){
            $this->ajaxReturn(array(
                'status'    =>  304,
                'info'      =>  '请先登录',
            ));
        }
        $type   = I('post.type',0,'intval');//认证类型
        $userv	= D('UserV');
        $uid    = session('user.id');			//用户ID
        $userVInfo= $userv->uservinfo($uid);		//获取old的资料路径,0表示没有查找到该记录
        if ($userVInfo && $userVInfo['vtype']==2 && $type==1){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  '您已社团认证，无法再进行个人认证',
            ));
        }else if($userVInfo && $userVInfo['vtype']==1 && $type==2){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  '您已个人认证，无法再进行社团认证',
            ));
        }
        $oldpath = $userVInfo['vpath'];
        
        $rel	= $userv->uploadFileV();//图片上传
        
        if ($rel[1]==0) {				//图片是否上传成功,0不成功，1成功
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  $rel[0],//上传不成功，提示上传的错误信息
            ));
        }
        $unfile = $userv->unlinkfile($oldpath); //删除old资料
        
        $_POST['vpath'] = $rel[0];				//赋值用于创建对象
        if ($type==1){
            $result = $userv->pathFileV($uid,1);
        }else {
            $result = $userv->pathFileV($uid,2);
        }
        if ($result!==true) {		//是否资料入库了
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  $result,//资料没有入库，提示数据库错误信息
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'info'      =>  $result,//全部完成，成功操作后，提示成功信息
        ));
    }
    
    
    
    
    
    
    
}