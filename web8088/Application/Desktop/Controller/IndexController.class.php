<?php
namespace Desktop\Controller;
use Think\Controller;

class IndexController extends Controller {

    public $perPage = 3;

    public function index(){
        A('Pc')->index();
        return;
    }

    public function userinfo(){
        $pageOffset=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $GroupInfo=D('GroupInfo');
        $Userpages=$GroupInfo->grouppage($pageOffset,$this->perPage);
        //获取用户约课信息
        $groupinfo=$GroupInfo->group($Userpages['pageOffset'],$Userpages['perPage'],$sort='desc');
        //设置读取约课信息和发布课程的信息为3条
        //if(!empty($groupinfo[$i]['less'])){
        $data=array(
            'info'=>$groupinfo,
            'page'=>$Userpages,
        );
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
        $data=array(
            'info'=>$lessinfo,
            'page'=>$Shoppages,
        );

        $this->ajaxReturn($data);
        //}
    }
    
    
    
    
    
//--------------------------------USER--------
    //获取合作机构
    public function cooperateInfo(){
        $shname = D('Common/ShopkeeperDetail')->getShopName(0, 24);
        if (!$shname){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'coinfo'    =>  $shname,
            ));
        }
        foreach ($shname as $skey=>$svalue){
            if ($skey/8==0){
                $codata[$skey%8][] = $svalue;
            }else {
                $codata[$skey/8][] = $svalue;
            }
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'coinfo'    =>  $codata,
        ));
    }
    // 热门课程
    public function getGener(){
        $gener = D('Common/ShopInfo')->gener(3, true);
        if(!$gener){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'gener'     =>  $gener,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'gener'     =>  $gener,
        ));
    }
    
    
    /**
     * 获取首页广告图
     */
    public function getBar(){
        $bar = D('Common/Advert')->getBarInfo(501);
        if ($bar===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'bar'       =>  $bar,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'bar'       =>  $bar,
        ));
    }
    
    
    
    
    
    
    
    public function search(){
        header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $type    = I('get.type',2,'intval');
//         $keyword = I('get.keyword','','strip_tags');
        
//         $cate_id = I('get.cateid',4,'intval');
//         $area_id = I('get.areaid',0,'intval');
//         $greet   = I('get.greet');
//         //定位 
//         $nearby = I('get.nearby');
//         $x      = I('get.x');
//         $y      = I('get.y');
        
//         //get转化成post
//         $_POST['type']    = $type;
//         $_POST['keyword'] = $keyword;
//         $_POST['cate_id'] = $cate_id;
//         $_POST['area_id'] = $area_id;
//         $_POST['greet']   = $greet;
//         $_POST['nearby']  = $nearby;
//         $_POST['x']       = $x;
//         $_POST['y']       = $y;
        
        switch ($type){
            case 1:
                $result = $this->ajaxGroup();
                break;
            case 2:
                $result = $this->ajaxShop();
                break;
            default:
                break;
        }

        print_r(count($result['info']));
        print_r('<br/>');
        print_r($result);
        exit;
        
        $this->display();
    }
    
    
    
    /*
     * 搜索页面的约课信息
     */
    public function ajaxGroup($perPage=6){
        $GroupInfo = D('GroupInfo');
        
        $cateid = I('get.cate_id',0,'intval');
        $areaid = I('get.area_id',0,'intval');
        $greet  = I('get.greet','','strip_tags');
        $nearby = I('get.nearby','','strip_tags');
        $x      = I('get.x');
        $y      = I('get.y');
        
        $url="http://api.map.baidu.com/telematics/v3/reverseGeocoding?location={$x},{$y}&coord_type=gcj02&output=json&ak=4b312ce0e3931ab65e07ba2c59a3c152";
        $str=file_get_contents($url);
        $xy=json_decode($str,true);
        $description=$xy["description"];
        $district=$xy["district"];
        $street=$xy["street"];
        
        $pageOffset=I('get.pages')?I('get.pages'):1;
    
        //分类下的所有分类
        $cateIdArray=D('Category')->getCateListByCateid($cateid);
        $cateIdCount=count($cateIdArray);
        $parentCateId=$cateIdArray[$cateIdCount-1];
        //定位
        if($nearby){
            $map['skd.area_detail'] = array('like',array('%'.$street.'%',$street.'%','%'.$street),'OR');
            $map['skd.area_detail'] = array('like',array('%'.$district.'%',$district.'%','%'.$district),'OR');
        }
        
        if($areaid!=0){
            $map['gi.areaid'] =array('eq',$areaid);
        }
        
        if (!$cateIdArray){
            $map['gi.cateid'] = array('eq',$cateid);
        }else {
            $cataImplode  = implode(',', $cateIdArray);
            $map['gi.cateid'] = array('IN',$cataImplode);
        }
    
        // 关键字搜索补丁
        $keyword = I('get.keyword','','strip_tags');
        $map = $this->patchKeywordSearch("group", $map, $keyword);

        $pages    = $GroupInfo->cateStatisticsPagesSearch($pageOffset,$perPage,$map,$keyword);

		$data = $GroupInfo->getGroup($pages['pageOffset'],$pages['perPage'],$sort='desc', $greet,$map,$parentCateId);
		
        $data=array(
            'info'=>$data,
            'page'=>$pages,
            'map' =>$map,
        );
    
        $this->ajaxReturn($data);
    }
    
    /*
     * 搜索页面的约课信息
     */
    public function ajaxShop($perPage=6){
        
        $ShopInfo = D('ShopInfo');
        
        $cateid = I('get.cate_id',0,'intval');
        $areaid = I('get.area_id',0,'intval');
        $greet  = I('get.greet','','strip_tags');
        $nearby = I('get.nearby','','strip_tags');
        $x      = I('get.x');
        $y      = I('get.y');
        
        $url="http://api.map.baidu.com/telematics/v3/reverseGeocoding?location={$x},{$y}&coord_type=gcj02&output=json&ak=4b312ce0e3931ab65e07ba2c59a3c152";
        $str=file_get_contents($url);
        $xy=json_decode($str,true);
        $description=$xy["description"];
        $district=$xy["district"];
        $street=$xy["street"];
        
        $pageOffset=I('get.pages')?I('get.pages'):1;
        
    
        //分类下的所有分类
        $cateIdArray=D('Category')->getCateListByCateid($cateid);
        $cateIdCount=count($cateIdArray);
        $parentCateId=$cateIdArray[$cateIdCount-1];
        //定位
        if($nearby){
            $map['si.area_detail'] =array('like',array('%'.$street.'%',$street.'%','%'.$street),'OR');
            $map['si.area_detail'] =array('like',array('%'.$district.'%',$district.'%','%'.$district),'OR');
        }
        if($areaid!=0){
            $map['si.areaid'] =array('eq',$areaid);
        }
    
        //-----分页----
        if (!$cateIdArray){
            $map['si.cateid']=array('eq',$cateid);
        }else {
            $cataImplode=implode(',', $cateIdArray);
            $map['si.cateid']=array('IN',$cataImplode);
        }
    
        // 关键字搜索补丁
        $keyword = I('get.keyword','','strip_tags');
        $map = $this->patchKeywordSearch("shop", $map, $keyword);
         
        $pages=$ShopInfo->cateStatisticsPagesSearch($pageOffset,$perPage , $map , $keyword);

        $data=$ShopInfo->getShop($pages['pageOffset'],$pages['perPage'],$sort='desc',$greet,$map,$parentCateId);
    
        $data=array(
            'info'=>$data,
            'page'=>$pages,
            'map' =>$map,
        );
        return $data;
    }
    
    

    /**
     * where条件增加了关键字搜索条件
     */
    protected function patchKeywordSearch($type, $map, $keywords) {
        $keywords = trim($keywords);
        // 没有关键字，直接返回
        if (!$keywords) {
            return $map;
        }
        // 判断是什么类型
        switch ($type) {
            // 用户发布的心愿单
            case 'group':
                $map['_string'] = "gi.catename like '%$keywords%' or skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or sk.company_name like  '%$keywords%'  or gi.catename like '$keywords%' or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or sk.company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or sk.company_name like  '%$keywords' ";
                break;
    
                // 商家发布的课程信息
            case 'shop':
                $map['_string'] = "skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or company_name like  '%$keywords%'  or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or company_name like  '%$keywords'";
                break;
    
        }
    
        return $map;
    }
    
    

    /**
     * 404页面
     */
    public function notFound() {
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("404");
    }
    
    
    
    
    
    
    
    
    
//----------------------------------------
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
