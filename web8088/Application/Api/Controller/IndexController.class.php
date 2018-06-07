<?php

namespace Api\Controller;
use Api\Controller\CommonController;

		/*
		 * 前台主页的模版
		 */
	class IndexController extends CommonController {

        /**
         * 判断有没有登录和登录的身份
         */
        public function checkLoginType() {
            if (session('?user')) {
                return $this->ajaxReturn([
                    'status'    =>  200,
                    'type'      =>  'u',
                ]);

            } else if (session('?shopkeeper')) {
                return $this->ajaxReturn([
                    'status'    =>  200,
                    'type'      =>  's',
                ]);

            }

            return $this->ajaxReturn([
                'status'    =>  403,
            ]);
        }

		//显示主页的模版文件
		public function index(){
			$this->display();
		}


	/*
	 * 主页的分类导航栏
	 */
	public function ajaxCategoy(){
		$category=D('Category');
		//(缓存3600秒)
		$list=$category->cache(true,3600)->select();
		$data=$category->getTree($list);
		if($data==true){
			$this->ajaxReturn(array(
					'status'	=>	200,
					'msg'		=>  $data
			));
		}else{
			$this->ajaxReturn(array(
					'status'	=>	400,
					'msg'		=> '获取数据失败！'
			));
		}

	}

	/*
	 * 搜索页面的约课信息
	 */
	public function ajaxGroup($perPage=6){
		$GroupInfo=D('GroupInfo');
		$cateid=I('post.cate_id',0,'intval');
		$areaid=I('post.area_id',0,'intval');
		$greet=I('post.greet');
		$nearby= I('post.nearby');
		$x= I('post.x');
		$y= I('post.y');
		
		$url="http://api.map.baidu.com/telematics/v3/reverseGeocoding?location={$x},{$y}&coord_type=gcj02&output=json&ak=4b312ce0e3931ab65e07ba2c59a3c152";
		$str=file_get_contents($url);
		$xy=json_decode($str,true);
		$description=$xy["description"];
		$district=$xy["district"];
		$street=$xy["street"];
		
		$pageOffset=I('post.pages')?I('post.pages'):1;

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
// 		    $pages    = $GroupInfo->selectPages($pageOffset,$perPage,$cateid);
		}else {
		    $cataImplode  = implode(',', $cateIdArray);
		    $map['gi.cateid'] = array('IN',$cataImplode);
		}

        // 关键字搜索补丁
        $keyword = I('post.keyword','','strip_tags');
        $map = $this->patchKeywordSearch("group", $map, $keyword);

//         $pages    = $GroupInfo->cateStatisticsPages($pageOffset,$perPage,$cataImplode);
        $pages    = $GroupInfo->cateStatisticsPagesSearch($pageOffset,$perPage,$map,$keyword);

		$data = $GroupInfo->getGroup($pages['pageOffset'],$pages['perPage'],$sort='desc', $greet,$map,$parentCateId);
		$data = array(
		    'info'=>$data,
		    'page'=>$pages,
		);

//         // 是桌面版用使用，统计页数
//         $is_desktop = I('get.is_desktop', 0, 'intval');
//         if ($is_desktop) {
//             $count = D('Common/GroupInfo')->countGroupForSearch($map);
//             $totalPages = ceil($count / $perPage);

//             return $this->ajaxReturn(array_merge($data, array(
//                 'totalPages'    =>  $totalPages,
//             )));
//         }

		$this->ajaxReturn($data);
	}

	/*
	 * 搜索页面的约课信息
	 */
	public function ajaxShop($perPage=6){
	    $ShopInfo=D('ShopInfo');
	    $cateid=I('post.cate_id',0,'intval');
	    $areaid=I('post.area_id',0,'intval');
	    $greet=I('post.greet');
	    $nearby= I('post.nearby');
	    $x= I('post.x');
	    $y= I('post.y');
	    
	    $url="http://api.map.baidu.com/telematics/v3/reverseGeocoding?location={$x},{$y}&coord_type=gcj02&output=json&ak=4b312ce0e3931ab65e07ba2c59a3c152";
	    $str=file_get_contents($url);
	    $xy=json_decode($str,true);
	    $description=$xy["description"];
	    $district=$xy["district"];
	    $street=$xy["street"];
	    
	    $pageOffset=I('post.pages')?I('post.pages'):1;

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
// 		if (!$cateIdArray){
// 		    $map['si.cateid']=array('eq',$cateid);
// 	        $pages=$ShopInfo->selectPages($pageOffset,$perPage,$cateid);
// 		}else {
// 		    $cataImplode=implode(',', $cateIdArray);
// 		    $map['si.cateid']=array('IN',$cataImplode);
// 		    $pages=$ShopInfo->cateStatisticsPages($pageOffset,$perPage, $cataImplode );
// 		}
		if (!$cateIdArray){
		    $map['si.cateid']=array('eq',$cateid);
		}else {
		    $cataImplode=implode(',', $cateIdArray);
		    $map['si.cateid']=array('IN',$cataImplode);
		}
		
	    // 关键字搜索补丁
	    $keyword = I('post.keyword');
	    $map = $this->patchKeywordSearch("shop", $map, $keyword);
	    
	    $pages=$ShopInfo->cateStatisticsPagesSearch($pageOffset,$perPage , $map , $keyword);


// 	    $data=$ShopInfo->getShop($pages['pageOffset'],$pages['perPage'],$sort='desc',$cateid,$areaid,$greet,$nearby,$map,$parentCateId);
	    $data=$ShopInfo->getShop($pages['pageOffset'],$pages['perPage'],$sort='desc',$greet,$map,$parentCateId);
	     
	    $data=array(
	        'info'=>$data,
	        'page'=>$pages,
	    );

//         // 是桌面版用使用，统计页数
//         $is_desktop = I('get.is_desktop', 0, 'intval');
//         if ($is_desktop) {
//             $count = D('Common/ShopInfo')->countShopForSearch($map);
//             $totalPages = ceil($count / $perPage);

//             return $this->ajaxReturn(array_merge($data, array(
//                 'totalPages'    =>  $totalPages,
//             )));
//         }

	    $this->ajaxReturn($data);
	}



	/*
	 * 筛选页面的搜索功能
	 *  拼凑数据（用户发布的约课信息和商家发布的课程）
	 */
	public function getSearch(){
	    $keywords= I('post.keywords'); //
	    $GroupInfo=D('GroupInfo');
	    $ShopInfo=D('ShopInfo');
	    $shopsearch=$ShopInfo->getShopSearch($keywords);
	    $groupsearch=$GroupInfo->getGroupSearch($keywords);
	    $data=array('arr'=>array('info'=>$groupsearch,'list'=>$shopsearch));
	    $returnData=array();
	    $temp=array();
	    foreach ($shopsearch as $v){
	       $temp['id']=$v['id'];
	        $temp['uid']=$v['uid'];
	        $temp['sid']=$v['sid'];
	        $temp['cateid']=$v['cateid'];
	        $temp['title']=$v['title'];
	        $temp['areaid']=$v['areaid'];
	        $temp['ltprice']=$v['ltprice'];
	        $temp['gtprice']=$v['gtprice'];
	        $temp['mode']=$v['mode'];
	        $temp['tags']=$v['tags'];
	        $temp['ctime']=$v['ctime'];
	        $temp['view']=$v['view'];
	        $temp['number']=$v['number'];
	        $temp['firstname']=$v['firstname'];
	        $temp['lastname']=$v['lastname'];
	        $temp['avatar']=$v['avatar'];
	        $temp['telstatus']=$v['telstatus'];
	        $temp['name']=$v['name'];
	        $temp['profession']=$v['profession'];
	        $temp['vstatus']=$v['vstatus'];
	        $temp['vtype']=$v['vtype'];
	        $temp['pricearr']=$v['pricearr'];
	        $temp['status']=$v['status'];
	        $temp['catename']=$v['catename'];
	        $temp['company_name']=$v['company_name'];
	        $temp['skavatar']=$v['skavatar'];
	        $temp['nickname']=$v['nickname'];
	        $temp['environ']=$v['environ'];
	        $temp['areaname']=$v['areaname'];
	        $temp['area_detail']=$v['area_detail'];
	        $temp['overtimes']=$v['overtimes'];
	        $temp['comment']=$v['comment'];
	        $temp['coursemun']=$v['coursemun'];
	        $temp['login_phone']=$v['login_phone'];
	        $temp['user_count']=$v['user_count'];
	        $temp['comment_count']=$v['comment_count'];
	        $temp['price']=$v['price'];
	        array_push($returnData, $temp);
	        $temp=array();
	    }
	    foreach ($groupsearch as $v){
	        $temp['id']=$v['id'];
	        $temp['uid']=$v['uid'];
	        $temp['sid']=$v['sid'];
	        $temp['cateid']=$v['cateid'];
	        $temp['title']=$v['title'];
	        $temp['areaid']=$v['areaid'];
	        $temp['ltprice']=$v['ltprice'];
	        $temp['gtprice']=$v['gtprice'];
	        $temp['mode']=$v['mode'];
	        $temp['tags']=$v['tags'];
	        $temp['ctime']=$v['ctime'];
	        $temp['view']=$v['view'];
	        $temp['number']=$v['number'];
	        $temp['firstname']=$v['firstname'];
	        $temp['lastname']=$v['lastname'];
	        $temp['avatar']=$v['avatar'];
	        $temp['telstatus']=$v['telstatus'];
	        $temp['profession']=$v['profession'];
	        $temp['vstatus']=$v['vstatus'];
	        $temp['vtype']=$v['vtype'];
	        $temp['name']=$v['name'];
	        $temp['status']=$v['status'];
	        $temp['catename']=$v['catename'];
	        $temp['company_name']=$v['company_name'];
	        $temp['skavatar']=$v['skavatar'];
	        $temp['nickname']=$v['nickname'];
	        $temp['environ']=$v['environ'];
	        $temp['areaname']=$v['areaname'];

	        $temp['pricearr']=$v['pricearr'];
	        $temp['area_detail']=$v['area_detail'];
	        $temp['overtimes']=$v['overtimes'];
	        $temp['comment']=$v['comment'];
	        $temp['coursemun']=$v['coursemun'];
	        $temp['login_phone']=$v['login_phone'];
	        $temp['user_count']=$v['user_count'];
	        $temp['comment_count']=$v['comment_count'];
	        array_push($returnData, $temp);
	    }
	    $this->ajaxReturn($returnData);


	}
	/*
	 * 主页的商家课程
	*/
	public function listinfo(){
		$ShopInfo=D('ShopInfo');
		$data=$ShopInfo->listInfo($pageOffset=0,$perPage=4,$sort='desc');

		$this->ajaxReturn(array(
				'status'	=>	200,
				'msg'		=>  $data
		));
	}

	/*
	 * 调用百度IP定位
	* 接收用户请求的地址
	* 返回JSON数据;
	*/
	public function addr(){
		//$ip=$_POST['ip'];
		$ip="58.63.150.32";
		$ak="4b312ce0e3931ab65e07ba2c59a3c152";
		//"http://api.map.baidu.com/telematics/v3/reverseGeocoding?location=113.385093,23.151741&coord_type=gcj02&ak=4b312ce0e3931ab65e07ba2c59a3c152";
		$url="http://api.map.baidu.com/location/ip?ak={$ak}&ip=$ip&coor=bd09ll";
		$str=file_get_contents($url);
		$data=json_decode($str);
		$this->ajaxReturn ($data);
	}

	/*
	 * 调用高德地图云搜索
	* 接收用户请求的地址经纬度
	* 返回JSON数据;
	*/
	public function mess(){
		//$ip=$_POST['ip'];
		//获取经纬度
		$xy="113.342636,23.186976";
		//获取关键词
		$keywords="英文";
		$tableid="54c1bf35e4b0a8782e3613f7";
		$key="d4a6813bd47f07ebd464ea8ca6ce712d";
		$url="http://yuntuapi.amap.com/datasearch/around?tableid=54c1bf35e4b0a8782e3613f7&keywords={$keywords}&center={$xy}&radius=50000&filter=type:写字楼&limit=10&page=2
		&key={$key}";
		$str=file_get_contents($url);
		$data=json_decode($str);
		$this->ajaxReturn ($data);
	}

	/*
	 * 主页的课程推广
	*/
	public function Gener(){
		$ShopInfo=D('ShopInfo');
		$data=$ShopInfo->gener();
		$this->ajaxReturn(array(
				'status'	=>	200,
				'msg'		=>  $data
		));
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
            $map['_string'] = "gi.catename like '%$keywords%' or skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or sk.company_name like  '%$keywords%'  or gi.catename like '$keywords%' or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or sk.company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or sk.company_name like  '%$keywords'";
            break;

        // 商家发布的课程信息
        case 'shop':
            $map['_string'] = "skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or company_name like  '%$keywords%'  or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or company_name like  '%$keywords'";
            break;

        }

        return $map;
    }

}
