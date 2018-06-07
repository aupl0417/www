<?php
namespace Desktop\Controller;
use Common\Controller\BaseController;
/**
 *
 * @author user
 *
 */
class UserController extends BaseController {

    /**
     * 列出文件
     */
    public function show() {
        $path = realpath(dirname(__DIR__) . '/View/User/');
        foreach (scandir($path) as $filename) {
            $filepath = $path . '/' . $filename;
            if (is_file($filepath)) {
                $link = __CONTROLLER__ . '/' . $filename;
                echo "<h3><a href=\"$link\">$link</a></h3>";
            }
        }
    }

    /**
     * 分类导航栏使用
     */
    protected function cateNav() {
        $cate = D('Category');
        $data = $cate->getAllCateInfo();
        $oneCate = array();
        $twoCate = array();
        $threeCate = array();
        $fourCate = array();
        $fiveCate = array();
        $sexCate = array();
        $sevenCate = array();
        foreach ($data as $key=>$value ){
            switch ($value['id']){
                case 6:
                    $oneCate = $value;
                    break;
                case 2:
                    $twoCate = $value;
                    break;
                case 8:
                    $threeCate = $value;
                    break;
                case 7:
                    $fourCate = $value;
                    break;
                case 4:
                    $fiveCate = $value;
                    break;
                case 1:
                    $sexCate = $value;
                    break;
                case 3:
                    $sevenCate = $value;
                    break;
                default:
                    break;
            }
        }
        $resultCate =array();
        $resultCate[] = $oneCate;
        $resultCate[] = $twoCate;
        $resultCate[] = $threeCate;
        $resultCate[] = $fourCate;
        $resultCate[] = $fiveCate;
        $resultCate[] = $sexCate;
        $resultCate[] = $sevenCate;
//         print_r($resultCate);exit;
        $this->cateNavData = $resultCate;
    }
    
    //登录后页面头部的名字和头像信息 
    public function useloginHeader(){
        if (session('?user')){
            $this->name = session('user.name');
            $this->avatar = session('user.avatar');
        }
    }
    
    
    public function index(){
        $this->useloginHeader();
//         header("content-Type: text/html; charset=Utf-8");//设置字符编码
//         $this->isLogin();
        $this->cateNav();
//         // 热门课程
//         $this->gener = D('Common/ShopInfo')->gener(3, true);
//         print_r($this->gener);exit;
        $this->display();
    }
    
    /**
     * 个人中心
     * @param number $uid
     * @param number $vid
     */
    public function home($uid=0,$vid=0){
        $this->useloginHeader();
        
        $uid=intval($uid);
        if (!is_numeric($uid)) {
            $this->redirect('Index/notFound');//没有该记录则跳转到404页面
        }
        
        //当前用户的id，0未登录
        if (session('?user.id')){
            $nowsUserId = session('user.id');
        }else {
            $nowsUserId = 0;
        }
        
        if ($uid==0){//当传入的uid为0时，则查看是当前用户的信息
//---------------------------------------------------------------------- 游客
            $vid=intval($vid);
            if ($vid!=0){
                $visit_info = D('Visitor')->getOneInfo($vid);
                $visit_config = C('visitor_config');
                $visit_info['avatar']   = $visit_config['avatar'];
                $visit_info['lastname'] = $visit_info['name'];

                $returnIndex=S('returnIndex');//判断返回是否跳转回首页
                $this->assign('returnIndex',$returnIndex);
                $this->assign('loginuser',0);//0查看的不是当前用户信息，1是查看当前用户信息
                $this->assign('info',$visit_info);
                $this->display();
                exit;
            }
//---------------------------------------------------------------------- 游客
            if (!session('?user')){
                $this->redirect('Index/notFound');//没有该记录则跳转到404页面
            }
            //当传入的uid为0时，则查看是当前用户的信息
            $uid=session('user.id');
            $loginuid=1;
        }else {
            //$loginuid=1查看的是当前用户自己的信息,$loginuid=0表示不是自己的信息
            if ($nowsUserId==$uid){
                $loginuid=1;
            }else {
                $loginuid=0;
            }
        }
        
        //查询uid用户的信息
        $User = D('User');
        $result = $User->myInfo($uid);
        
        $returnIndex=S('returnIndex');//判断返回是否跳转回首页 
        
        $this->assign('returnIndex',$returnIndex);
        $this->assign('loginuser',$loginuid);//0查看的不是当前用户信息，1是查看当前用户信息
        $this->assign('info',$result);
        $this->display();
    }

    /**
     * ajax获取用户的约课信息
     */
    public function course_group(){
        $perPage = 9;
        $curPage = I('get.page',1,'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $uid = I('get.uid',session('user.id'),'intval');      // 要查看的uid的约课信息
    
        $group      = D('Common/GroupInfo');
        $pageArray  = $group->grouppage($curPage,$perPage,$uid); // 查询满足要求的总记录数
        $rel        = $group->userGroupInfo($uid,0,$pageArray['pageOffset'],$pageArray['perPage'],$sort='desc');
        if (!$rel){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  $rel,
                'page'      =>  $pageArray,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'info'      =>  $rel,
            'page'      =>  $pageArray,
        ));
    }
    
    



    /**
     * 约课详情
     */
    public function onedetail(){
        $this->useloginHeader();
         
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
         
        $userInfo = D('User')->getUserInfo($info['uid']);
         
// print_r($info);exit;
        $this->assign('userinfo',$userInfo);
        $this->assign('info',$info);
        $this->display();
    }
    
    
    
    
    
//---------------------------------------有权限的---需要登录-----------------------------------------------------------    
    
    /**
     * 是否登录，，true已登录 
     * @return boolean
     */
    public function isLogin(){
        header("content-Type: text/html; charset=Utf-8");//设置字符编码
        if (!session('?user')){
            echo '请先登录！';
            exit;
            return false;
        }
        $this->useloginHeader();
        return true;
    }
    
    /**
     * 用户的个人中心的导航 
     */
    public function centerNav(){
        $userInfo['avatar'] = session('user.avatar');
        $userInfo['name']   = session('user.name');
        $userInfo['telstatus']    = session('user.telstatus');
        $userInfo['remark'] = session('user.remark');
        $userInfo['profession'] = session('user.profession');
        $userInfo['age']    = session('user.age');
        $this->userInfo = $userInfo;
    }
    /**
     * 用户的资料修改
     */
    public function edit(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $edit = D('User')->myeditor();
        
        $this->assign('info',$edit);
        $this->display();
    }
    /**
     * 用户的约课发布
     */
    public function publish(){
        $check = $this->isLogin();
        $this->centerNav();

        $this->display();
    }
    
    /**
     * AJAX返回所有的分类信息
     */
    public function catetory(){
        $cate = D('Category');
        $data = $cate->getAllCateInfo();
        $this->ajaxReturn(array(
            'status'    =>  200,
            'catenav'   =>  $data,
        ));
    }
    
    //收藏 
    public function mywish(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $this->display();
    }
    
    
    
    
    
    //认证
    public function certification(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $this->display();
    }
    
    //课程动态
    public function course(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $this->display();
    }
    
    
    //消息
    public function message(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $this->display();
    }
    

    
    //设置 -ok
    public function set(){
        $check = $this->isLogin();
        $this->centerNav();
        
        $this->display();
    }
    
    
    

    /**
     * 用户注销登录
     */
    public function outlogout(){
        if (I('get.zk')=='out') {
            $out=D('User');
            $resule=$out->userLogout();
            $this->redirect('User/index');
        }
    }
    
}
