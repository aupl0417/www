<?php
namespace Admin\Controller;
use Common\Controller\CommonController;

/**
 *
 * @author yuan
 *
 */
class DataController extends CommonController {

	/**
	 * 数据页面
	 *
	 */

    public $perPage=30;//每页显示的页码

    public function shopkeeperdata() {
        // 是不是自己人，是就1，不是就2
        $is_us = I('get.isus', 0, 'intval');

        header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $Shopkeeper=D('Shopkeeper');
        $curPage=I('get.p', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page

        $count= M('Shopkeeper')->where('is_us = %d', $is_us)->count(); // 查询总记录数
        $page = new \Common\Util\BootstrapPage($count, $this->rowlist);
        $this->page = $page->show();

        $data=$Shopkeeper->getShopkeeperData($page->firstRow, $page->listRows, 'desc', $is_us);

        $this->assign('data',$data);
        $this->count = $count;

        $this->display();
    }

    public function userdata() {
        header("content-Type: text/html; charset=Utf-8");//设置字符编码
       $User=D('User');
       $GroupInfo=D('GroupInfo');
       $ShopkeeperDetail=D('ShopkeeperDetail');
      $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
      $pageOffset=intval($pageOffset);
       $uid=I('post.uid',0,'intval');
      $pages=$User->userDataPage($pageOffset,$this->perPage);
        $data=$User->getUserData($pages['pageOffset'],$pages['perPage'],$sort='desc');
        $sum=$User->sum();
/*         $arr=array();
        if($uid){
     $array= $GroupInfo->where("uid='$uid'")->field('sid')->select();
                 foreach ($array as $key1 =>$value1){
                     array_push($arr,$value1['sid']);
                 }
                 $s=implode(',',$arr);
                 $map['sid']  = array('in',$s);
                 $array= $ShopkeeperDetail->join('__SHOPKEEPER__ on __SHOPKEEPER_DETAIL__.sid= __SHOPKEEPER__.id')
                 ->where($map)
                 ->field('login_phone,area_detail,nickname,login_email,tel')->select();

    $this->ajaxReturn($array);
        } */


     //var_dump($data); */
// print_r($pages);
// exit;   
      $this->assign('data',$data);
      $this->assign('sum',$sum);
      $this->assign('page',$pages); // 赋值分页输出
        $this->display();
    }

    public function catedata() {
        $Category=D('Category');
        $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $pages=$Category->cateDataPage($pageOffset,$this->perPage);
        $data=$Category->getCateData($pages['pageOffset'],$pages['perPage'],$sort='desc');
        $this->assign('data',$data);
       $this->assign('page',$pages); // 赋值分页输出
        $this->display();
    }

    /*
     *   用户跟约对应的商家
      */
    public function info(){
        $GroupInfo=D('GroupInfo');
        $ShopkeeperDetail=D('ShopkeeperDetail');
        $id=I('get.id',0,'intval');
        $arr=array();
         if($id){
            $array= $GroupInfo->where("uid='$id'")->field('sid')->select();
            foreach ($array as $key1 =>$value1){
                array_push($arr,$value1['sid']);
            }
            $s=implode(',',$arr);
            $map['sid']  = array('in',$s);
            $array= $ShopkeeperDetail->join('__SHOPKEEPER__ on __SHOPKEEPER_DETAIL__.sid= __SHOPKEEPER__.id')
            ->where($map)
            ->field('login_phone,area_detail,nickname,login_email,tel')->select();
            $this->assign('array',$array);
        }
        $this->display();
    }


      /*
       *
       * 用户发布的心愿所有跟约的人
       *
       */
        public function assinfo(){
            header("content-Type: text/html; charset=Utf-8");//设置字符编码
           $id=I('get.id',"",'intval');
             $User=D('User');
            $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
            $perPage=12;//每页显示的页码
            $pages=$User->userDataPage($pageOffset,$perPage,$id);
            $data=$User->getAssInfo($pages['pageOffset'],$pages['perPage'],$id);
            $this->assign('data',$data);
            $this->assign('page',$pages); // 赋值分页输出
            $this->display();

}

                /*
                 *
                 * 用户发布的心愿所有跟约的人
                 *
                 */
                public function tsInfo(){
                    header("content-Type: text/html; charset=Utf-8");//设置字符编码
                    $id=I('get.id',"",'intval');
                    $User=D('User');
                    $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
                    $perPage=12;//每页显示的页码
                    $pages=$User->tsTnfoDataPage($pageOffset,$perPage,$id);
                    $data=$User->getTsInfo($pages['pageOffset'],$pages['perPage'],$id);
                    $this->assign('data',$data);

                    $this->assign('page',$pages); // 赋值分页输出
                    $this->display();

                }

                /*
                 *
                 * 用户发布的心愿评论的信息数据
                 *
                 */
                public function comInfo(){
                    header("content-Type: text/html; charset=Utf-8");//设置字符编码
                    $id=I('get.id',"",'intval');
                    $User=D('User');
                    $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
                    $perPage=5;//每页显示的页码
                    $pages=$User->userDataPage($pageOffset,$perPage,$id);
                    $data=$User->getcomInfo($pages['pageOffset'],$pages['perPage'],$id);
                    $this->assign('data',$data);
                    $this->assign('page',$pages); // 赋值分页输出
                    $this->display();

                }

                /*
                 *
                 *  商家发布的课程详情的信息数据
                 *
                 */
                public function kcInfo(){
                    header("content-Type: text/html; charset=Utf-8");//设置字符编码
                    $id=I('get.id',"",'intval');
                   $ShopInfo=D('ShopInfo');
                    $pageOffset=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
                    $perPage=8;//每页显示的页码
                    $pages=$ShopInfo->kcTnfoDataPage($pageOffset,$perPage,$id);
                    $data=$ShopInfo->getkcInfo($pages['pageOffset'],$pages['perPage'],$id);
                    $this->assign('data',$data);
                    $this->assign('page',$pages); // 赋值分页输出
                    $this->display();

                }
}


