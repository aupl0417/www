<?php
namespace Api\Controller;
use Api\Controller\CommonController;
/**
 *
 * @author user
 *
 */
class UserCollectController extends CommonController {
	
    
    
    /**
     * 添加一条收藏信息
     */
    public function addCollect(){
        $gid=I('get.gid');
		$nowhref=I('get.nowhref');
        if (!session('?user.id')){//还未登录
		    session('historyhref',$nowhref);
            $this->ajaxReturn(array(
                'status'	=>	401,
                'assist'	=>	'请先登录！',
            ));
        }else {
            $uid=session('user.id');
        }
        $userCollect = D('UserCollect');
        $result = $userCollect->addCollectByUser($uid,0,$gid);
        if ($result !== true) {
            if ($result==="402"){//已经收藏并且删除成功
                $this->ajaxReturn(array(
                    'status'	=>	402,
                    'collect'	=>	$result,
                ));
            }else {
                $this->ajaxReturn(array(
                    'status'	=>	400,
                    'collect'	=>	$result,
                ));
            }
        }
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'collect'	=>	$result,
        ));
    }
    
}