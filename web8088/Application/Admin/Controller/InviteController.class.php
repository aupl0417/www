<?php
namespace Admin\Controller;
use Common\Controller\CommonController;

/**
 * 
 * @author user
 *
 */
class InviteController extends CommonController {

    public function index() {
    	$this->display();
    }
    /**
     * 查看邀请码列表
     */
    public function indexMore(){
        $curPage = I('get.page',1,'intval');
        $perPage = 10;
        $uniqueness = I('get.type',10,'intval');//10代表全部 
        $status     = I('get.status',10,'intval');
        $overtime   = I('get.overtime',10,'intval');
        $desc       = 'desc';
        $order      = 'id';
        $invite = D('InviteOfficial');
        $page = $invite->pageAll($curPage,$perPage,$uniqueness,$status,$overtime);
        $codeInfo = $invite->getCodeList( $uniqueness , $page['pageOffset'] , $page['perPage'] , $status ,$desc , $order , $overtime);
        if (!$codeInfo){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'list'      =>  array(
                    'page'  =>  $page,
                    'info'  =>  $codeInfo,
                ),
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'list'      =>  array(
                'page'  =>  $page,
                'info'  =>  $codeInfo,
            ),
        ));
    }
    /**
     * 某条邀请码详情
     */
    public function inveteInfo(){
        $id = I('get.id',0,'intval');
        $invite = D('InviteOfficial');
        $getall = $invite->getCodeOne($id);
        $this->assign('info',$getall);
        $this->display('inviteinfo');
    }
    /**
     * 某条邀请码的详情----与用户相关联的数据
     * 某个code的id
     */
    public function getUseInfo(){
        $curPage = I('get.page',1,'intval');
        $id = I('get.id',0,'intval');
        $perPage = 300;
        $invite = D('InviteGroup');
        $page = $invite->pageInfo($curPage,$perPage,$id);
        $codeInfo = $invite->getCodeInfo( $page['pageOffset'] , $page['perPage'] , $id );
        if (!$codeInfo){
            $this->ajaxreturn(array(
                'status'    =>  400,
                'info'      =>  array(
                    'info'  =>  $codeInfo,
                    'page'  =>  $page,
                ),
            ));
        }
        $this->ajaxreturn(array(
            'status'    =>  200,
            'info'      =>  array(
                'info'  =>  $codeInfo,
                'page'  =>  $page,
            ),
        ));
    }
    
    /**
     * 生成邀请码
     */
    public function addInvite(){
        $invite = D('InviteOfficial');
        $addStatus = $invite->addCode();
        if ($addStatus!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  $addStatus,
            ));
        }
        $addData = session('addinvite');
        foreach ($addData as $key=>$value){
            if ($key!=0){
                $addData[$key]['id'] = $addData[0]['id']+1;
            }
            $addData[$key]['ctime'] = date('Y-m-d H:i:s',$value['ctime']);
            $addData[$key]['endtime'] = date('Y-m-d H:i:s',$value['endtime']);
            $addData[$key]['color'] = 'red';
        }
        $addNum  = count($addData);
        $this->ajaxReturn(array(
            'status'    =>  200,
            'list'       =>  array(
                'num'   =>  $addNum,
                'info'  =>  $addData,
            ),
        ));
    }
    
}