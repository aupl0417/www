<?php
namespace Api\Controller;
use Api\Controller\CommonController;
/**
 *
 * @author user
 *
 */
class UserSystemController extends CommonController {
	
	
    public $perPage=5;

    
    /**
     * 根据uid获取系统消息
     */
    public function systemList(){
	    $curPage = I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $uid = session('user.id');
	    $UserSys = D('UserSystem');
	    $result = $UserSys->sysByUid($curPage,$this->perPage,$uid);
	    $this->ajaxReturn(array(// 获取成功
			'status'	=>	200,
			'info'	=>	$result,
	    ));
    }
    
    



    /**
     * AJAX返回当前用户的 被推送数--现在首页在轮循中
     */
    public function newsSystem(){
        $uid = session('user.id');
        $NewsSystem   = D('UserSystem');
        $numSystem   = $NewsSystem->newsNumSystem($uid);//未读推送数
        if ($numSystem===false){
            $this->ajaxReturn(array(
                'status'	=>	400,//查询错误
                'data'	=>	$numSystem,
            ));
        }
        $this->ajaxReturn(array( // 查询跟约数
            'status'	=>	200,
            'data'	=>	$numSystem,
        ));
    }
    
    
    
    
    
	
}