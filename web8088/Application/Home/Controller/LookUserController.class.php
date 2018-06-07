<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 *
 * @author user
 *
 */
class LookUserController extends BaseController {


    public $perPage=10;


    /**
     * 根据$uid 获取用户资料,,$vid游客
     */
    public function moInfo($uid=0,$vid=0){


        $uid=intval($uid);
        if (!is_numeric($uid)) {
            $this->redirect('Index/notFound');//没有该记录则跳转到404页面
        }

        $nowsUserId=session('user.id');

        if ($uid==0){//当传入的uid为0时，则查看是当前用户的信息
//---------------------------------------------------------------------- 游客
            $vid=intval($vid);
            if ($vid!=0){
                $visit_info = D('Visitor')->getOneInfo($vid);
                $visit_config = C('visitor_config');
                $visit_info['avatar'] = $visit_config['avatar'];
                $visit_info['lastname'] = $visit_info['name'];
//                 print_r($visit_info);exit;

                $returnIndex=S('returnIndex');
                $this->assign('returnIndex',$returnIndex);//判断是否用户编辑后跳转到这里的
                $this->assign('loginuser',0);//0查看的不是当前用户信息，1是查看当前用户信息
                $this->assign('info',$visit_info);
                $this->display('info_myInfo');
                exit;
            }
//---------------------------------------------------------------------- 游客
            if (!session('?user')){
                
                $this->redirect('Index/notFound');//没有该记录则跳转到404页面
            }
            $uid=session('user.id');
            $loginuid=1;
        }else {
            if ($nowsUserId==$uid){
                $loginuid=1;
            }else {
                $loginuid=0;
            }
        }


        $User = D('User');
        $result = $User->myInfo($uid);
        $count	= D('Common/GroupInfo')->usermun($uid);
        if ($count!=0){
            $assist	= D('Common/GroupAssist')->groupcount($result['gid']);
        }else {
            $assist=0;
        }
        $result['count']	= $count;
        $result['assist']	= $assist;
        $result['environ']	= '/Public/Uploads/'.$result['environ'];
        $returnIndex=S('returnIndex');
        $this->assign('returnIndex',$returnIndex);//判断是否用户编辑后跳转到这里的
        $this->assign('loginuser',$loginuid);//0查看的不是当前用户信息，1是查看当前用户信息
        $this->assign('info',$result);
        $this->display('info_myInfo');
    }


    /**
     * 查看某个用户的约课信息
     * @param number $uid
     */
    public function groupList($uid=0){

        $uid=intval($uid);
        if (!is_numeric($uid)) {
            $this->redirect('Index/notfound');//没有该记录则跳转到404页面
        }

        $nows_uid   = session('user.id');
        if ($uid!=$nows_uid){
            $now_user=0;//当0表示查看的不是登录的用户约课信息
        }elseif ($uid==$nows_uid){
            $now_user=$nows_uid;//查看的是登录的用户的信息
        }
        $this->assign('nowUser',$uid);
        $this->assign('checkUser',$now_user);
        $this->display('course');
    }


    //ajax获取用户的约课信息
    public function course_group(){
        $curPage=I('post.page',0,'intval')?I('post.page',0,'intval'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $uid=I('post.uid')?I('post.uid'):session('user.id');      // 要查看的uid的约课信息

        $uid=intval($uid);
        if (!is_numeric($uid)) {
            $this->ajaxReturn(null);//没有该记录则跳转到404页面
        }

        $groupByUser  =  D('Common/GroupInfo');
        $result       =  $groupByUser->groupByUser($uid,$curPage,$this->perPage);

        $data=array('info'=>$result);
        $this->ajaxReturn($data);
    }



}
