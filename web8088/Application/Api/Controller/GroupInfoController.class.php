<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * 
 * @author user
 *
 */
class GroupInfoController extends CommonController {

	/**
	 * 发布组团课程
	 */
	public function groupAdd(){
		$GroupInfo = D('GroupInfo');
    	$result = $GroupInfo->addGroup();
    	if ($result !== true) {// 增加失败
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(// 增加成功
    			'status'	=>	200,
    			'msg'		=>	$result,
    	));
	}
	/**
	 * 获取3条商家机构信息记录-
	 */
	public function getShopThree(){
	    $this->getGroupInfo(3);
	}
	/*
	 *  发布心愿页面
	 *  获取商家机构信息	
	 */
	public function getGroupInfo($perPage=6){
		$area_id = I('post.area_id',0,'intval'); // 用intval过滤$_POST['area_id']
		$cate_id= I('post.cate_id','0','intval'); // 用intval过滤$_POST['cate_id']
		$greet= I('post.greet'); 
	
		$nearby= I('post.nearby');
		$x= I('post.x');
		$y= I('post.y');
		$url="http://api.map.baidu.com/telematics/v3/reverseGeocoding?location={$x},{$y}&coord_type=gcj02&output=json&ak=4b312ce0e3931ab65e07ba2c59a3c152";
		$str=file_get_contents($url);
		$xy=json_decode($str,true);
		$description=$xy["description"];
		$district=$xy["district"];
		$street=$xy["street"];
		$pageOffset=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
// 	    $perPage=6;
	    $ShopkeeperDetail= D('ShopkeeperDetail');

	    $cateIdArray = D('Category')->getCateListByCateid($cate_id);
		$cateIdCount = count($cateIdArray);
		$parentCateId=$cateIdArray[$cateIdCount-1];

	    if($nearby){
    		$map['sd.area_detail'] =array('like',array('%'.$street.'%',$street.'%','%'.$street),'OR');
    		$map['sd.area_detail'] =array('like',array('%'.$district.'%',$district.'%','%'.$district),'OR');
	    }
	    if (!$cateIdArray){
	        $map['sd.cateid'] = array('eq',$cate_id);
	        $pages    = $ShopkeeperDetail->getInfopages($pageOffset,$perPage,$cate_id,$area_id);
	    }else {
	        $cataImplode  = implode(',', $cateIdArray);
	        $map['sd.cateid'] = array('IN',$cataImplode);
	        $pages    = $ShopkeeperDetail->cateStatisticsPages($pageOffset,$perPage,$cataImplode,$area_id);
	    }
	    if($area_id!=0){
	        $map['sd.areaid'] =array('eq',$area_id);
	    }
	    
		$shopkeeper=$ShopkeeperDetail->getshopkeeper($pages['pageOffset'],$pages['perPage'],$area_id,$cate_id,$greet,$nearby,$map,$sort='desc',$parentCateId);
		
		$data=array(
		    'info'=>$shopkeeper,
		    'page'=>$pages,
		    'map'=>$map,
		    'parentid'=>$parentCateId,
		);
		$this->ajaxReturn($data);   
	}
	
	public function getSeachShopAll(){
	    $this->getSearch(3);
	}
	/*
	 * //麻痹的，终于改的差不多了，痛苦啊，最讨厌改别人的代码了，分页都没有，竟然定死了20条数据 ，now加入分页功能 -----by---user
	 *  发布心愿页面--搜索功能
	 *  获取商家机构信息
	 */
	public function getSearch($perPage = 20){
		$keywords = I('post.keywords'); 
		$curPage = I('post.page',1,'intval'); 
		$ShopkeeperDetail = D('ShopkeeperDetail');
		$pageArray = $ShopkeeperDetail->getSearchInfoPage( $keywords , $curPage , $perPage );
		$shopkeeper = $ShopkeeperDetail->getSearchInfo($keywords , $pageArray['pageOffset'] , $pageArray['perPage'] );
	
		$data=array(
		    'info'=>$shopkeeper,
		    'page'=>$pageArray,
		);
		$this->ajaxReturn($data);
	}
	
	/*
	 *  发布心愿页面
	 *  获取某一个商家详细信息
	 */
	public function getBusiness(){
		$sid= I('post.sid',0,'intval'); // 用intval过滤$_POST['sid']
		$ShopkeeperDetail= D('ShopkeeperDetail');
		$info=$ShopkeeperDetail->getBusinessInfo($sid);
		$this->ajaxReturn($info); 
	}
	
		
	/*
	 *  发布心愿页面
	 *  获取某一个商家的评论信息
	 */
	public function getComment(){
		 $sid= I('post.sid',0,'intval'); // 用intval过滤$_POST['sid']
		 $curPage=I('post.pages')?I('post.pages'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$content= I('post.content','','strip_tags'); // 用strip_tags过滤$_POST['content']
		$ShopkeeperComment= D('ShopkeeperComment');
		$perPage=5;//每页显示的页码
		$info=$ShopkeeperComment->getBusinesscomment($curPage,$perPage,$sid);
		if ($info!==true){
		      $this->ajaxReturn(array(
        		    'status'  => 400,
        		    'info'    => $info,
		      ));  
		}
		$this->ajaxReturn(array(
		    'status'  => 200,
		    'info'    => $info,
		));  
		
	}
	
	/*
	 *  发布心愿页面
	 *  插入数据（对某一个商家机构的评论）
	 */
	public function insertComment(){
	    $sid= I('post.sid',0,'intval'); // 用intval过滤$_POST['sid']
		$content= I('post.content','','strip_tags'); // 用strip_tags过滤$_POST['content']
		$ShopkeeperComment= D('ShopkeeperComment');
		
		$res=$ShopkeeperComment->insert($sid,$content);
		if ($res!==true){
		      $this->ajaxReturn(array(
        		    'status'  => 400,
        		    'comment'    => $res,
		      ));  
		}
		$this->ajaxReturn(array(
		    'status'  => 200,
		    'comment'    => $res,
		));  
	}
	public function delComment(){
	    $sid   = I('post.sid',0,'intval'); // 用intval过滤$_POST['sid']
	    $uid   = session('user.id');
	    $comid = I('post.comid',0,'intval'); // 用intval过滤$_POST['sid']
		$ShopkeeperComment= D('ShopkeeperComment');
	    $result    = $ShopkeeperComment->delCommByGid($comid,$sid,$uid);
		if ($result!==true){
		      $this->ajaxReturn(array(
        		    'status'  => 400,
        		    'comment' => $result,
		      ));  
		}
		$this->ajaxReturn(array(
		    'status'  => 200,
		    'comment' => $result,
		));  
	    
	}
	/*
	 *  发布心愿页面
	 *  插入数据（由用户添加一个新的商家机构信息）
	 */
	public function newShopkeeper(){
	    $cateid= I('post.cateid',0,'intval'); // 用intval过滤$_POST['cateid']
	    $areaid= I('post.areaid',0,'intval'); // 用intval过滤$_POST['areaid']
	    $comname= I('post.comname','','strip_tags'); // 用strip_tags过滤$_POST['content']
	    $nickname= I('post.nickname','','strip_tags'); // 用strip_tags过滤$_POST['content']
	    $phone= I('post.phone'); 
	    $avatar= I('post.avatar','','strip_tags'); 
	    $areaname= I('post.areaname');
	    $shopkeeper= D('Shopkeeper');	
	    $res=$shopkeeper->insertNewShopkeeper($cateid,$areaid,$comname,$nickname,$phone,$avatar,$areaname);
	    $this->ajaxReturn($res);  
	}
	
	/*
	 *  发布心愿页面
	 *  获取分类
	 */
	public function getCategory(){
	    $category= D('Category');
	    $cateid= I('post.cateid',0,'intval');
	    $allCate=$category->where('parent_id=%d',$cateid)->field('id,parent_id,catename')->select();
	    $data=array('info'=>$allCate);
		$this->ajaxReturn($data);  
	}
	
	public function thirdCategory(){
	    $category= D('Category');
	    $cateid= I('post.cateid',0,'intval');
	    $allCate=$category->where('parent_id=%d',$cateid)->field('id,parent_id,catename')->select();
	    $data=array('info'=>$allCate);
	    $this->ajaxReturn($data);
	}

	/*
	 *  发布心愿页面
	 *  获取2级全部分类
	 *  添加机构
	 */
	public function getAllCategory(){
	    header("content-Type: text/html; charset=Utf-8");//设置字符编码
	    $category = D('Category')->getAllTwoLevel();
        if (!$category){
    	    $this->ajaxReturn(array(
    	        'status'   =>  200,
    	        'cate'     => $category,
    	    ));
        }
	    $category_array=array();
	    foreach ($category as $key=>$value){
	        $category_array[$key]['parent_cate']=$value[0];
	        unset($value[0]);
	        $category_array[$key]['list']=$value;
	    }
	    $this->ajaxReturn(array(
	        'status'   =>  200,
	        'cate'     => $category_array,
	    ));
	    print_r($category_array);
	}
	
	
	/*
	 *  发布心愿页面
	 *  插入发布心愿的数据
	 */
	public function insertGroupInfo(){
	    $title= I('post.title','','strip_tags');
	    $priceid= I('post.priceid',0,'intval'); 
	    $sid= I('post.sid',0,'intval');
	    $cateid= I('post.cateid',0,'intval');
	    $areaid=I('post.areaid',0,'intval');
	    $nickname= I('post.nickname');
	    $tags= I('post.tags');
	    $catename=  I('post.catename','','strip_tags');
	    $model= I('post.model',0,'intval');
	    $content= I('post.content','','strip_tags');
	    $overtime= I('post.overtime',0,'intval');
	    $environ=I('post.environ') ? I('post.environ') : "";
	    $GroupInfo= D('GroupInfo');
	    $res=$GroupInfo->insert($sid,$cateid,$catename,$areaid,$nickname,$tags,$title,$priceid,$model,$content,$overtime,$environ);
	    echo json_encode($res);
	}
	
	/**
	 * 看有没有达到一天的发布课程上限
	 */
	public function checkGroupSend() {
	    $GroupInfo= D('GroupInfo');
	    $result = $GroupInfo->countGruopInfoToday(session('user.id'));
	
	    if ($result > 10) {
	        return $this->ajaxReturn([
	            'status'    =>  400,
	            'res'       =>  '对不起，您今日之内已经发送超过10条课程信息，请明天再发',
	        ]);
	    }
	
	    return $this->ajaxReturn([
	        'status'    =>  200,
	        'count'     =>  $result,
	    ]);
	}
	
	/**
	 * 获取配置文件的约课模式
	 */
	public function getMode(){
	    $Mode=C('mode');
	    $data=array('info'=>$Mode);
	    $this->ajaxReturn($data);
	}
	
	/*
	 *  发布心愿页面
	 *  用户新添加商家机构时上传的头像
	 */
	public function upload(){
	    if(isset($_FILES["myfile"]))
	    {
	        // 实例化并配置上传类
	        $upload = new \Think\Upload();
	        $upload->maxSize = 2145728;
	        $upload->rootPath = './Public/Uploads/shop_avatar/';
	        $upload->savePath = '';
	        $upload->saveName = array('uniqid','');
	        $upload->exts     = array('jpg', 'png', 'jpeg');
	        $upload->autoSub  = true;
	        $upload->subName  = array('date','Y/m/d');
	        // 上传哦！
	        $info = $upload->uploadOne($_FILES["myfile"]);
	        
	       $name= 'shop_avatar/'.$info['savepath'].$info['savename'];
	        echo $name;
	
	    }
	}
	
	
	/*
	 *  发布心愿页面
	 *  用户添加上传的场景图
	 */
	public function environUpload(){
	    if(isset($_FILES["myfile"]))
	    {

	        $upload = new \Think\UploadFile();// 实例化上传类
	        $upload->maxSize  = 3145728 ;// 设置附件上传大小
	        $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
	        $upload->savePath =  './Public/Uploads/user_environ/';// 设置附件上传目录
	        $upload->autoSub=true;
	        $upload->subType='date';
	        $upload->dateFormat='Y/m/d';
	        if(!$upload->upload()) {// 上传错误提示错误信息
	            echo false;
	        }else{// 上传成功 获取上传文件信息
	            $info =  $upload->getUploadFileInfo();
	            $name= 'user_environ/'.$info[0]['savename'];
	            echo $name;
	        }
	         
	    }
	}
	
	public function getAllArea(){
	    header("Content-type:text/html;charset=utf-8");
	    $cateid	   = I('post.cateid');
	    $category=D('Category');
	    $cateid=$category->where('id=%d',$cateid)->field('id,parent_id,catename')->find();
	    $allCate=$category->where('parent_id=%d',$cateid)->order('sort desc')->field('id,parent_id,catename')->select();
	     $data=array('list'=>$allCate,'catename'=>$cateid['catename'],'id'=>$cateid['id']);
	    $this->ajaxReturn($data);
	}
	
	public function sendArea(){
	    $cateid	   = I('post.cateid');
	    $category=D('Category');
	    $allCate=$category->where('parent_id=%d',$cateid)->order('sort desc')->field('id,parent_id,catename')->select();
	    $data=array('info'=>$allCate);
	    $this->ajaxReturn($data);
	}
	
	
	public function getPrice(){
	    $cateid	   = I('post.cateid');
	    $Price=D('Price');
	    $allPrice=$Price->where('cateid=%d',$cateid)->select();
	    $reference=$Price->where('cateid=%d',$cateid)->field("reference")->find();
        
	    $data=array('info'=>$allPrice,'reference'=>$reference['reference']);
	    $this->ajaxReturn($data);
	}
	
	public function getInfo(){
	    $sid	   = I('post.sid',0,'intval');
	    $mode	   = I('post.model');
	   $tags	   = I('post.tags','','strip_tags');
	    $overtime= I('post.overtime',0,'intval');
	    $priceid	   = I('post.priceid',0,'intval');
	    $User= D('User');
	    $info=$User->getText($sid,$mode,$tags,$overtime,$priceid);
	    $data=array('info'=>$info);
	    $this->ajaxReturn($data);
	}
	/**
	 * 检查用户的资料完善
	 *  
	 */
	public function Score(){
	    $user=D('User');
	    $res=$user->userScore();
	    echo json_encode($res);
	}
	
	
	/**
	 * 检查用户的是否上传了头像
	 *
	 */
	public function Avatars(){
	    $user=D('User');
	    $uid   = session('user.id');
	    $res=$user->userAvatars($uid);
	    echo json_encode($res);
	}
	
	public function addr(){
		//获取IP
		$nearby= I('post.nearby');
		//获取配置文件的AK值
		$ak=C('ak');
		$url="http://api.map.baidu.com/location/ip?ak={$ak}&ip={$nearby}&coor=bd09ll";
		$str=file_get_contents($url);
		$data=json_decode($str);
		$this->ajaxReturn ($data);
	}
	
	public function tagsAdd(){
		$tags	   = I('post.tags');
		$gid	   = I('post.gid')?I('post.gid'):1;
		$GroupInfo = D('GroupInfo');
		$result    = $GroupInfo->addtags($tags,$gid);
    	if ($result !== true) {// 增加失败
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(// 增加成功
    			'status'	=>	200,
    			'msg'		=>	$result,
    	));
	}
	
	public function area(){
		$tags	   = I('post.tags');
		$gid	   = I('post.gid')?I('post.gid'):1;
		$GroupInfo = D('GroupInfo');
		$result    = $GroupInfo->addtags($tags,$gid);
		if ($result !== true) {// 增加失败
			$this->ajaxReturn(array(
					'status'	=>	400,
					'msg'		=>	$result,
			));
		}
		$this->ajaxReturn(array(// 增加成功
				'status'	=>	200,
				'msg'		=>	$result,
		));
	}
	
	//=======
	public function addOverTime(){
	    $timelist = time_list_date_format();
	    return $this->ajaxReturn([
	        'status'    =>  200,
	        'timelist'     =>  $timelist,
	    ]);
	}
	
}