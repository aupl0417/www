<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * 整个类=====++++=======================================暂时废弃================================
 * @author user
 * GroupAnswer
 */
class GroupAnswerController extends CommonController {
    
    

    /**
     * 发表约课课程的评论
     */
    public function answerAdd(){
header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $gid=I('post.gid')?I('post.gid'):0;//为了发表评论 获取该gid的评论信息
        $comid=I('post.comid')?I('post.comid'):0;//为了发表评论 获取该gid的评论信息
        $_POST['a_info']='dfdf';
        $_POST['gid']=3;
        $_POST['comid']=135;
        
        $GroupAnswer = D('GroupAnswer');
        $result = $GroupAnswer->addAnswer();
        
print_r($result);exit;

        if ($result !== true) {// 增加失败
            if ($result==="403"){
                $this->ajaxReturn(array(
                    'status'	=>	403,
                    'answer'	=>	'您还没有登录',
                ));
            }else {
                $this->ajaxReturn(array(
                    'status'	=>	400,
                    'answer'	=>	$result,
                ));
            }
        }
        if (session('?user')){
            $uid=session('user.id');
            $sid=0;
        }elseif (session('?shopkeeper')){
            $uid=0;
            $sid=session('shopkeeper.id');
        }
        $oneanswer = $GroupAnswer->oneAnswer($uid,$sid,$gid,$comid);
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'answer'	=>	$oneanswer,
        ));
    }
    
    
    
    
    /**
     * 根据get提交的gid 获取该gid的评论信息
     */
    public function answerGroup(){
header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $gid     = I('post.gid')?I('post.gid'):30;
        $comid   = I('post.comid')?I('post.comid'):121;
        $curPage = I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $perPage = 3;//每页显示的评论信息
        $answerGroup = D('GroupAnswer');
        $rel = $answerGroup->answerByGidComid($gid,comid,$curPage,$perPage,$order='desc');
print_r($rel);exit;
        if ($rel === false) {// 增加失败
            $this->ajaxReturn(array(
                'status'	=>	400,
                'comment'		=>	$rel,
            ));
        }
        $this->ajaxReturn(array(// 增加成功
            'status'	=>	200,
            'comment'		=>	$rel,
        ));
    }
    
    
    
    
}