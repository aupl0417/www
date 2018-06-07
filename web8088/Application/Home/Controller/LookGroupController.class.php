<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 *
 * @author user
 *
 */
class LookGroupController extends BaseController {






    /**
     * 根据get提交过来的gid，获取该gid的详细信息
     */
    public function onedetail(){
//header("Content-type:text/html;charset=utf-8");
        $gid = I('get.gid')?I('get.gid'):0;

        $gid=intval($gid);
        if (!is_numeric($gid)) {
        	$this->redirect('Index/notfound');//没有该记录则跳转到404页面
        }
        if (!$gid){
            $this->redirect('Index/notfound');//没有该记录则跳转到404页面
        }
        $groupOne = D('GroupInfo');
        $info  = $groupOne->groupInfoOne($gid);
        if ($info==false){
            $this->redirect('Index/notfound');//没有该记录则跳转到404页面
        }
// print_r($info);exit;
        $this->assign('info',$info);
        $this->display('gdetail');
    }




    /**
     * 根据get提交过来的gid，page来 获取该gid的组团人的信息
     */
    public function assistGroup(){
        $gid = I('get.gid')?I('get.gid'):1;
        $curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $perPage=8;//每页显示的组团人数
        $assistUser = D('GroupAssist');
        $assist = $assistUser->assistByGid($gid,$curPage,$perPage,$order='desc');
        if (!$assist){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'assistdata'=>  $assist,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'assistdata'=>  $assist,
        ));
        // $data=array('info'=>$assist);
        // print_r($data);exit;
//         $this->ajaxReturn($assist);
    }



}
