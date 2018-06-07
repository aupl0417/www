<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class IndexController extends BaseController {


    public $perPage=2;//每页显示的页码
		/*
		 * 加载主页
		 */
    public function index(){

        if (is_mobile_request()  || session('?shopkeeper.id') ){

        }else{
            $this->redirect('Desktop/Pc/index');
        }

        header("content-Type: text/html; charset=Utf-8");//设置字符编码
    	//实例化模型类
    	$cate=D('Category');
    	$GroupInfo=D('GroupInfo');
    	$ShopInfo=D('ShopInfo');
    	$User=D('User');
    	$advert=D('Advert');
    	$ShopkeeperDetail=D('ShopkeeperDetail');
    	//查找分类的数据(缓存3600秒)
    	$data=$cate->where('parent_id=0')->limit(0,8)->order('sort desc')->select();
    	// 生成树状结构(获取分类菜单的信息)
    	$data=$cate-> getTree ($data);
    	//获取用户约课信息
    	$groupinfo=$GroupInfo->group($pageOffset=0,$perPage=3,$sort='desc');
    	//获取商家的公司名称和LOGO(缓存3600秒)
    	$shname=$ShopkeeperDetail->getShopName();
    	//获取主页的课程推广(缓存3600秒)
    	$gener=$ShopInfo->gener();

	    $Ad=$advert->getIndexAdvert();      //(缓存600秒)
    	//获取发布课程的信息
    	$lessinfo=$ShopInfo->listInfo($pageOffset=0,$perPage=3,$sort='desc');
    	//设置读取约课信息和发布课程的信息为3条
    	for($i=0;$i<3;$i++){
    		$groupinfo[$i]['less']=$lessinfo[$i];
    	}
    	/*
    	 * 判断用户或者商家是否登录
    	 * 判断session是否存在（再根据身份分配数据）
    	 */

    	//定义一个空数组（下标为身份rank的字段），默认为用户user
    	$array=array('rank'=>'user');
    	$user=session('?user') ? session('user') : false;
    	$shopkeeper=session('?shopkeeper')  ? session('shopkeeper') : false;
    	$array['user']=$user;
    	$array['shopkeeper']=$shopkeeper;
   	 if($user){
        $this->assign('array',$array);
    	}
    	if($shopkeeper){
    		//更改数组小标的rank的值为商家
    		$array['rank']='shopkeeper';
    		$this->assign('array',$array);
    	}



    	$this->assign('Ad',$Ad);
    	$this->assign('groupinfo',$groupinfo);
    	$this->assign('shname',$shname);
    	$this->assign('gener',$gener);
		$this->assign('data',$data);
		$this->display();

    }

    /*
     * 加载注册/登录页面
    */
    public function loginregister(){
    	$GroupInfo=D('GroupInfo');
    	//获取用户约课信息
    	$groupinfo=$GroupInfo->group($pageOffset=0,$perPage=2,$sort='desc');
    	$this->assign('groupinfo',$groupinfo);
    	$this->display();
    }

    /*
    * 调用百度IP定位
    * 接收用户请求的地址
    * 返回JSON数据;
    */
    public function addr(){
    	//获取IP
    	$ip=I('post.ip');
    	//获取配置文件的AK值
    	$ak=C('ak');
    	$url="http://api.map.baidu.com/location/ip?ak={$ak}&ip={$ip}&coor=bd09ll";
    	$str=file_get_contents($url);
    	$data=json_decode($str);
		$this->ajaxReturn ($data);
    }

    /*
     * 主业的约课信息和发布课程
     */

    public function shopAnduser(){
        $pageOffset=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $GroupInfo=D('GroupInfo');
        $ShopInfo=D('ShopInfo');
        $Userpages=$GroupInfo->grouppage($pageOffset,$this->perPage);
        $Shoppages=$ShopInfo->grouppage($pageOffset,$this->perPage);
        //获取用户约课信息
        $groupinfo=$GroupInfo->group($Userpages['pageOffset'],$Userpages['perPage'],$sort='desc');
        $lessinfo=$ShopInfo->listInfo($Shoppages['pageOffset'],$Shoppages['perPage'],$sort='desc');
        //设置读取约课信息和发布课程的信息为3条
        for($i=0;$i<3;$i++){
            $groupinfo[$i]['less']=$lessinfo[$i];
        }
        //if(!empty($groupinfo[$i]['less'])){
        $data=array('info'=>$groupinfo);
        $this->ajaxReturn($data);
        //}
    }


    public function userinfo(){
        $pageOffset=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $GroupInfo=D('GroupInfo');
        $Userpages=$GroupInfo->grouppage($pageOffset,$this->perPage);
        //获取用户约课信息
        $groupinfo=$GroupInfo->group($Userpages['pageOffset'],$Userpages['perPage'],$sort='desc');
        //设置读取约课信息和发布课程的信息为3条
        //if(!empty($groupinfo[$i]['less'])){
        $data=array('info'=>$groupinfo);
        /* var_dump($data);
        exit; */
        $this->ajaxReturn($data);
        //}
    }

    public function shopinfo(){
        $pageOffset=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
    $ShopInfo=D('ShopInfo');
        $Shoppages=$ShopInfo->grouppage($pageOffset,$this->perPage);
        //获取用户约课信息，置顶的排在最前头
        $lessinfo=$ShopInfo->listInfo($Shoppages['pageOffset'],$Shoppages['perPage'],$sort='desc', null, null, false, true);

        //if(!empty($groupinfo[$i]['less'])){
        $data=array('info'=>$lessinfo);

        $this->ajaxReturn($data);
        //}
        }

    /*
     * 加载点击首页的分类跳转的搜索页面
     */
    public function select(){
        //获取IP

        $this->cateid = I('get.cateid', 0, 'intval');
        if ($this->cateid) {
        $catemap = M('Category')->field(array('id', 'catename'))->where('parent_id=0')->select();
            $this->catenameMap = json_encode(array_combine(
                array_column($catemap, "id"), array_column($catemap, 'catename')
            ));
        }

        $this->display();
    }

    /*
     * 加载点击首页的分类跳转的搜索页面
     */
    public function selectTwo(){
        //获取IP

        $this->display('select_two');
    }

    /**
     * 404页面
     */
    public function notFound() {
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("404");
    }
}
