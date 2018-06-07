<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * 
 * @author user
 *
 */
class GroupPushedController extends CommonController {
    


    /**
     * 添加一条商家推送信息
     */
    public function addPush(){
        $gid=I('post.gid');
        $shopinfoid=I('post.infoid');
        if (!session('?shopkeeper')){//还未登录
            $this->ajaxReturn(array(
                'status'	=>	401,
                'pushed'	=>	'请先登录！',
            ));
        }else {
            $sinfoid=session('shopkeeper.id');
        }
        $Grouppush = D('GroupPushed');
        $result = $Grouppush->addPushByShop($shopinfoid,$gid);
        if ($result !== true) {
            if ($result==="502"){//已经送过该课程
                $this->ajaxReturn(array(
                    'status'	=>	502,
                    'pushed'	=>	$result,
                ));
            }else {
                $this->ajaxReturn(array(
                    'status'	=>	400,
                    'pushed'	=>	$result,
                ));
            }
        }
		$onepush = $Grouppush->onePushed($shopinfoid,$gid,'desc');
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'pusheds'	=>	$onepush,
        ));
    }
    

    /**
     * 根据gid，获取该gid推送过什么课程
     */
    public function pushedByGid(){
        $gid = I('post.gid')?I('post.gid'):18;
        $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $perPage=5;//每页显示的课程人数
        $groupPushed = D('GroupPushed');
        $rel = $groupPushed->pushedByGid($gid,$curPage,$perPage,$order='desc');
// print_r($rel);exit;
        if ($rel === false) {// 失败
            $this->ajaxReturn(array(
                'status'	=>	400,
                'pushed'	=>	$rel,
            ));
        }
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'pushed'	=>	$rel,
        ));
        
    }
    
    
    /**
     * 消息中====================
     * 为你推荐中的，根据get提交的gid  获取当前用户的所发布约课中  商家推送过的课程
     */
    public function pushedByNews(){
        $curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $perPage=3;
        if (!session('?user')){
            $this->ajaxReturn(array(
                'status'	=>	'401',
                'pushnews'	=>	'',
            ));
        };
        $uid=session('user.id');
        $newsPushed=D('GroupPushed');
        $newsinfo=$newsPushed->newsByUser($uid,$curPage,$perPage,$order='desc');
// print_r($newsinfo);exit;
        if ($newsinfo === null) {// 失败
            $this->ajaxReturn(array(
                'status'	=>	400,
                'pushnews'	=>	$newsinfo,
            ));
        }
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'pushnews'	=>	$newsinfo,
        ));
    }
    
    
    
    
    


    /**
     * AJAX返回当前用户的 被推送数--现在首页在轮循中
     */
    public function newsPushed(){
        $uid = session('user.id');
	    $NewsPushed   = D('GroupPushed');
	    $numPushed   = $NewsPushed->newsNumPushed($uid);//未读推送数
        if ($numPushed===false){
            $this->ajaxReturn(array(
                'status'	=>	400,//查询错误
                'data'	=>	$numPushed,
            ));
        }
        $this->ajaxReturn(array( // 查询跟约数
            'status'	=>	200,
            'data'	=>	$numPushed,
        ));
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

}