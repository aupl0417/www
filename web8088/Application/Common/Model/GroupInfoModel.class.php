<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class GroupInfoModel extends CommonModel {

	//验证
	protected $_validate=array(

			array('uid', '/^\d+$/', '用户ID不是数字！', 1, 'regex'),

			array('sid', '/^\d+$/', '商家ID不是数字！', 1, 'regex'),

			array('title', '/^.{1,25}$/', '标题必须在25个字符以内！', 1, 'regex'),

			array('cateid', '/^\d+$/', '分类ID不是数字！', 1, 'regex'),

			array('areaid', '/^\d+$/', '地区ID不是数字！', 1, 'regex'),

			array('ltprice', '/^\d+$/', '价钱不合法！', 1, 'regex'),

			array('gtprice', '/^\d+$/', '价钱不合法！', 1, 'regex'),

			array('mode', '/^\d+$/', '模式ID不是数字！', 1, 'regex'),

			array('teacherneed', '/^.{1,50}$/', '教师需求必须在250个字符以内！', 1, 'regex'),

			array('content', '/^.{1,255}$/', '课程内容必须在255个字符以内！', 1, 'regex'),



	);


	protected $_auto = array (
			array('ctime', 'current_datetime', 1, 'function')
	);



	/**
	 *  发布组团课程
	 *  @param munber uid 用户ID
	 *  @param munber sid 所组团的商家ID
	 *  @param string title 组团标题
	 *  @param munber cateid 组团分类ID
	 *  @param munber areaid 地区ID
	 *  @param munber price 组团价格
	 *  @param munber mode 上课的模式，如星期六，星期日
	 *  @param string teacherneed 教师需求
	 *  @param string content 课程的内容
	 *  @param array tags 标签
	 * @return string|boolean
	 */
	public function addGroup() {
	/* 	$arrPrice=explode('--', I('post.price')); //  把接收过来的价格的分割成数组存储数据库
		$_POST['ltprice']=$arrPrice[0];
		$_POST['gtprice']=$arrPrice[1]; */
	    $Price=I('post.price');
	    $_POST['ltprice']=$Price;
		$_POST['tags']=implode('|', I('post.tags'));//把获取的tags标签的数组形式转换成以|分割的字符串,并且赋值给post使create获取创建对象
		$addg = $this->create();
		if (!$addg) {
			return $this->getError();
		}
		$userid=$this->add();
		if (!$userid) {
			return $this->getDbError();
		}
		return true;
	}


	/**
	 * 删除当前用户的约课信息，并且删除该约课信息的评论，推送，跟约等信息
	 * @param number $gid
	 * @return boolean
	 */
	public function delGroupByGid($gid=0){
	    $uid = session('user.id');
	    $checkMe = $this->where('id=%d and uid=%d',$gid,$uid)->getField('id');
	    if (!$checkMe){
	        return false;
	    }
	    $delass   = D('GroupAssist')->delAssiByGid($gid);
	    $delpush  = D('GroupPushed')->delPushByGid($gid);
	    $delcomm  = D('Groupcomment')->delCommByGid($gid);
	    if ($delass!==true||$delpush!==true||$delcomm!==true){
	        return $delass.'|'.$delpush.'|'.$delcomm;
	    }
	    $delInfo = $this->where('id=%d and uid=%d',$gid,$uid)->delete();
	    if (!$delInfo){
	        return false;
	    }
	    return true;
	}


	/**
	 * 获取组团的信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return string
	 */
	public function group($pageOffset=0,$perPage=3,$sort='asc'){
		//要查询的字段
		$array = array(
				'gi.id',
				'gi.uid',
				'gi.cateid',
				'gi.title',
				'gi.areaid',
		        'gi.priceid',
		       'gi.catename as cate_name',
				'gi.ltprice',
				'gi.gtprice',
				'gi.mode',
		    'gi.content',
				'gi.tags',
				'gi.ctime',
		    'gi.environ as user_environ',
		        'gi.overtime',
				'gi.view',
				'gi.number',
				'u.firstname',
				'u.lastname',
				'u.avatar',
				'u.telstatus',
				'u.profession',
				'uv.vstatus',
				'uv.vtype',
		      'cate.catename',
		    'cate.parent_id',
				'sk.company_name',
				'skd.environ',
				'skd.avatar as skavatar',
				'skd.nickname',
		);
		$alias = 'gi';//定义当前数据表的别名
		$join  = array(
				'__USER__ u on gi.uid = u.id',
		        'left join __USER_V__ uv on gi.uid = uv.uid',
				'__CATEGORY__ cate on gi.cateid = cate.id',
				'__SHOPKEEPER__ sk on gi.sid = sk.id',
				'__SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid'
		);
		//join可以使用array
		$where = 'gi.uid=u.id AND gi.sid=sk.id';
		$order = 'gi.id '.$sort;
		$res=$this  ->alias($alias)
					->join($join)
					->where($where)
					->order($order)
					->limit($pageOffset,$perPage)
					->field($array)
					->select();
		$res=$this->grouptags($res);
		// 实例化 Groupcomment 模块
		$Comm = D('Common/Groupcomment');
		$GroupPushed = D('Common/GroupPushed');
		$areaAllName=D('Common/Area');
		$Category=D('Category');
		$Price=D('Price');
		foreach ($res as $key=>$value){
		    $res[$key]['content']= nl2br($value['content']);
			$res[$key]['comment']= $Comm->Gcommentall($value['id']); // 调用 Groupcomment 模块中的方法Gcommentall,并且复制给$rel的comment，当前组团内的评论数
			$res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
			$res[$key]['environ']='/Public/Uploads/'.$value['environ'];//课程的头像
			$res[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];//商家的头像
			$res[$key]['coursemun']=$GroupPushed->coursenum($value['id']);//商家唉推送的课程总数
			$res[$key]['ctime'] = transDate($value['ctime']);
			$res[$key]['pricearr']=$Price->getId($value['priceid']);
			$res[$key]['ltprice']=floor($value['ltprice']);
			$parent_id=$value['parent_id'];
			$arr=$Category->where("id='$parent_id'")->field('catename')->find();
			$res[$key]['name']=$arr['catename']."/".$value['catename'];
			if (strtotime($value['overtime'])>time()){
			    $res[$key]['overtimes'] = 1;
			}else{
			    $res[$key]['overtimes'] = 0;
			}
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($value['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}
// 			$res[$key]['viewtrue'] = $this->addViewOne($value['id']);//增加view访问数1
		}
		return $res;
	}





	/**
	 * 根据uid或者gid约课信息
	 * @param number $uid如果uid!=0则通过uid用户的ID获取该用户的约课信息
	 * @param number $gid如果gid!=0则通过gid约课的ID获取该约课的约课信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return boolean|unknown
	 */
	public function userGroupInfo($uid=0,$gid=0,$pageOffset=0,$perPage=2,$sort='desc'){

	    $nows_uid   = session('user.id');
	    if ($uid!=$nows_uid){
	        $now_user=0;   //当0表示查看的不是登录的用户约课信息
	    }elseif ($uid==$nows_uid){
	        $now_user=1;   //查看的是登录的用户的信息
	    }


		$alias = 'gi';//定义当前数据表的别名
		//要查询的字段
		$array = array(
				'gi.id',
				'gi.uid',
				'gi.cateid',
				'gi.title',
				'gi.areaid',
				'gi.ltprice',
				'gi.gtprice',
		        'gi.priceid',
		        'gi.catename as cate_name',
				'gi.mode',
		    'gi.content',
				'gi.tags',
				'gi.ctime',
				'gi.overtime',
				'gi.view',
				'gi.number',
				'gi.pushed',
				'gi.gcomment',
		    'gi.environ as user_environ',
				'u.id as userid',
				'u.firstname',
				'u.lastname',
				'u.avatar',
				'u.profession',
				'u.interest',
				'u.telstatus',
				'uv.vtype',
				'uv.vstatus',
				'cate.catename',
		    'cate.parent_id',
				'sk.company_name',
				'skd.environ',
				'skd.avatar as skavatar',
				'skd.nickname',
		);
		$join  = array(
				'__USER__ u on gi.uid = u.id',
				'left join __USER_V__ uv on uv.uid = u.id',
				'__CATEGORY__ cate on gi.cateid = cate.id',
				'left join __SHOPKEEPER__ sk on sk.id = gi.sid',
				'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gi.sid'
		);
		//join可以使用array

		if ($uid!=0){
		          $where = 'gi.uid='.$uid;
		}elseif ($gid!=0){
		          $where = 'gi.id='.$gid;
		}else {
		          $where = 'gi.uid = u.id AND gi.cateid = cate.id AND gi.sid=sk.id AND sk.id = skd.sid';
		}


		$order = 'gi.ctime '.$sort;
		$res=$this  ->alias($alias)
					->join($join)
					->where($where)
					->order($order)
					->limit($pageOffset,$perPage)
					->field($array)
					->select();
		if (!$res){
		    return false;
		}
// 		return $res;
// 		$Group = D('Common/GroupInfo');
// 		$res=$Group->grouptags($res);
		$res=$this->grouptags($res);
		// 实例化 Groupcomment 模块
		$Comm 		= D('Common/Groupcomment');
		$GroupPushed 	= D('Common/GroupPushed');
		$areaAllName	= D('Common/Area');
		$UserV 			= D('UserV');
		$Price=D('Price');
		$Category=D('Category');
		foreach ($res as $key=>$value){
		    $res[$key]['content']= nl2br($value['content']);
			$res[$key]['comment']= $Comm->Gcommentall($value['id']); // 当前组团内的评论数
			$res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
			$res[$key]['environ']='/Public/Uploads/'.$value['environ'];//课程的头像
			$res[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];//商家的头像
			
			if ($value['user_environ']){
			    $res[$key]['user_environ']='/Public/Uploads/'.$value['user_environ'];//用户自己发布的场景图
			}
			
			$res[$key]['coursemun']=$GroupPushed->coursenum($value['id']);//商家唉推送的课程总数
			$res[$key]['ctime'] = transDate($value['ctime']);   				//个人申V，1通过，false失败
			//多级分类跟优惠价格
			$res[$key]['pricearr']=$Price->getId($value['priceid']);
			$parent_id=$value['parent_id'];
			$arr=$Category->where("id='$parent_id'")->field('catename')->find();
			$res[$key]['name']=$arr['catename']."/".$value['catename'];
			//约课过期时间
			if (strtotime($value['overtime'])>time()){
			    $res[$key]['overtimes'] = 1;
			}else{
			    $res[$key]['overtimes'] = 0;
			}
			//约课模式
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($value['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}

			$res[$key]['now_user'] = $now_user;   				//是否自己的信息

// 			$res[$key]['viewtrue'] = $this->addViewOne($value['id']);//增加view访问数1
		}
		return $res;
	}


	//根据约课的ID 获取该约课的详细信息，包括【报名人数，头像等】
	public function groupInfoOne($gid=0){
	    $alias = 'gi';//定义当前数据表的别名
	    $array = array(
	        'gi.id',
	        'gi.uid',
	        'gi.sid',
	        'gi.title',
	        'gi.cateid',
	        'gi.areaid',
	        'gi.ltprice',
	        'gi.gtprice',
	        'gi.priceid',
	        'gi.catename as cate_name',
	        'gi.mode',
		    'gi.environ as user_environ',
	        'gi.content',
	        'gi.tags',
	        'gi.view',
	        'gi.number',
	        'gi.pushed',
			'gi.gcomment',
	        'gi.ctime',
	        'gi.overtime',
	        'u.firstname',
	        'u.lastname',
	        'u.profession',
	        'u.telstatus',
	        'u.avatar',
	        'cate.catename',
		    'cate.parent_id',
	        'sk.company_name',
	        'skd.avatar as skavatar',
	        'skd.environ',
	        'skd.nickname',
	        'count(ga.groupid) as ykcount',
	        'count(gc.gid) as ykgccount',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = gi.uid',
	        'left join __CATEGORY__ cate on cate.id = gi.cateid',
	        'left join __SHOPKEEPER__ sk on sk.id = gi.sid',
	        'left join __SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid',
	        'left join __GROUP_ASSIST__ ga on ga.groupid = gi.id',
	        'left join __GROUPCOMMENT__ gc on gc.gid = gi.id',
	    );
	    $where = 'gi.id='.$gid;
      $res=$this -> alias($alias)
        	     -> join($join)
        	     -> where($where)
        	     -> field($array)
        	     -> find();
// dump($this->_sql());exit;
// print_r($res);exit;
      if (!$res['id']){
          return false;
      }

      //多级分类跟优惠价格
      $Price=D('Price');
      $Category=D('Category');
      $res['pricearr']  = $Price->getId($res['priceid']);
      $parent_id        = $res['parent_id'];
      $arr              = $Category->where("id='$parent_id'")->field('catename')->find();
      $res['name']      = $arr['catename']."/".$res['catename'];

      $Mode=C('mode');
      foreach ($Mode as $k => $v){
          if ($res['mode']==$k){
              $res['mode']= $v;
          }
      }

      if ($res['overtime']>date('Y-m-d H:i:s')){
          $res['overtimes'] = 1;//还没过期
      }else{
          $res['overtimes'] = 0;//已经过期
      }

        $res['tags'] = explode("|", $res['tags']);
	    $areaAllName	= D('Common/Area');
	    $res['areaname']=$areaAllName->getAllById($res['areaid']);//地区名字
	    if (session('?shopkeeper')){
	        $res['logintype']=1;//当前登录的是商家
	        $res['loginid']=session('shopkeeper.id');//当前登录的是商家ID
	        $res['userpushgroup']=0; //0不是用户登录， 1当前登录的用户第一次发布心愿单，2当前登录的用户不是第一次发布心愿单
	        $res['keeperavatar']=session('shopkeeper.avatar');
	        $res['keeperphone']=session('shopkeeper.login_phone');
	        $res['keepernickname']=session('shopkeeper.nickname');
	    }elseif (session('?user')){
	        $res['logintype']=2;//当前登录的是用户
	        $res['loginid']=session('user.id');//当前登录的是用户ID
	        $userpushgroup=$this->conutGidNumByUid($res['loginid']);//判断是否
	        if ($userpushgroup==1){ //0不是用户登录， 1当前登录的用户第一次发布心愿单，2当前登录的用户不是第一次发布心愿单,3是该用户未发布过,4当前约课不是当前用户发布的

	            if ($res['loginid']==$res['uid']){
	                $res['userpushgroup']=1;
	            }else{
	                $res['userpushgroup']=4;
	            }

	        }else if ($userpushgroup>1){
	           $res['userpushgroup']=2;
	        }else {
	            $res['userpushgroup']=3;
	        }
	        //判断用户是否有上传头像
	        $avatardef=D('User')->userAvatarBysession();
	        if ($avatardef===true){
	            $res['useravatarstatus']=1;
	        }else {
	            $res['useravatarstatus']=0;
	        }

	    }else {
	        $res['logintype']=0;//商家跟用户都没有登录
	        $res['loginid']=0;
	        $res['userpushgroup']=0; //0不是用户登录， 1当前登录的用户第一次发布心愿单，2当前登录的用户不是第一次发布心愿单
	    }
	    $GroupA=D('GroupAssist');
	    $GroupC=D('Groupcomment');
	    $GroupP=D('GroupPushed');
	    $UserC=D('UserCollect');
	    $res['ykasscount']=$GroupA->groupcount($res['id']);
	    $res['ykcomcount']=$GroupC->commCount($res['id']);
	    $res['ykpucount'] =$GroupP->coursenum($res['id']);
	    if ($res['logintype']==2){
	        $checkAssist=$GroupA->checkUserBygid($res['loginid'],$res['id']);//判断是否已经跟约
	        $checkCollectU=$UserC->checkUserBygidEict($res['loginid'],$res['id']);//判断是否已经收藏
	    }else {
	        $checkAssist=0;
	        $checkCollectU=0;
	    }
	    if (!$checkAssist) {
	    	$res['checkAssist']=0;//0该用户没有跟约
	    }else {
	    	$res['checkAssist']=$checkAssist;
	    }
	    if (!$checkCollectU) {
	    	$res['checkCollect']=0;//0该用户没有收藏
	    }else {
	    	$res['checkCollect']=$checkCollectU;
	    }
	    
	    if ($res['user_environ']){
	        $res['user_environ']='/Public/Uploads/'.$res['user_environ'];
	    }
	    $res['content']=nl2br($res['content']);//换行符转换/r/n成空格号
	    $res['contents']=clean_js_content($res['content']);//换行符转换/r/n成空格号
	    $res['viewtrue'] = $this->addViewOne($res['id']);//增加view访问数1

	    //当前服务器的记录的游客id
	    if (!session('?visitor')){
	        $res['visitor']=0;
	    }else {
	        $visitor   = session('visitor.id');
	        $res['visitor']=$visitor;
	    }



        return $res;
	}

	/**
	 * 统计某用户发布的约课数
	 * @param number $uid
	 * @return unknown
	 */
	public function conutGidNumByUid($uid=0){
	    $rel_g_num=$this->where('uid=%d',$uid)->count();
	    return $rel_g_num;
	}

// 	/**
// 	 * 查询地区详细，返回地区名称，小→大
// 	 * @param number $id
// 	 * @param unknown $array
// 	 * @return unknown
// 	 */
// 	public function areaname($id=0,$array=array()){
// 		$name=M('area');
// 		$rel =$name->where("id='%d'",$id)->find();
// 		if ($rel['depth']>0) {
// 			$array[]=$rel['areaname'];
// 			$array=$this->areaname($rel['parentid'],$array);
// 		}elseif ($rel['depth']==0){
// 			$array[]=$rel['areaname'];
// 		}
// 		return $array;
// 	}


	/**del
	 * 统计 当前组团信息的 商家推送课程的总推送数
	 * @param string $string
	 * @return number
	 */
// 	public function course($string=''){
// 		$array = explode(',', $string);
// 		$mun   = count($array);
// 		return $mun;
// 	}






	/**
	 * 增加组团信息的 标签
	 * @param array $tags
	 * @param number $id
	 */
	public function addtags($tags,$id=0){
		if (!empty($tags)) {
			$rules = array(
					array('tags', '/^[\w\x{4e00}-\x{9fa5}]{0,4}$/u', '标签内容必须在4个中文以内！', 1, 'regex'),
			);
			if (!$this->validate($rules)->create()) {
				return $this->getError();
			}
			$info= $this-> where("id=%d",$id)->field(array('id','tags'))->find();
			$tags= $info['tags'].'|'.$tags;
			$array=array('tags'=>$tags);
			$res = $this-> where("id=%d",$id)->filter('strip_tags')->setField($array);
			if (!$res) {
				return false;
			}
			return  true;
		}
	}



	/**
	 * 把组团信息的标签分割成数组
	 * @param array $info
	 * @return array $info
	 */
	public function grouptags($info=array()){
		//把组团标签以，逗号分割成数组
		$i=0;
		foreach ($info as $rows)
		{
			$rows['tags']=explode("|", $rows['tags']);
			$info[$i]['tags']=$rows['tags'];
			$i++;
		}
		return $info;
	}





	/**
	 * 返回总组团信息数
	 * @return int $res
	 */
	public function groupinfocount($uid=0){
		if ($uid==0) {//全部总数
			$res = $this->order('id')->count();
		}else {//某用户的约课信息总数
			$res = $this->where('uid=%d',$uid)->order('id')->count();
		}
		return  $res;
	}



	/**
	 * 组团分页数据
	 * 返回分页信息
	 * @param int $curPage
	 * @param int $perPage
	 * @return array $page
	 */
	public function grouppage($curPage=1,$perPage=5,$uid=0){
		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		$count= $this->groupinfocount($uid); // 查询满足要求的总记录数
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}


	/**
	 * 获取当前用户最新的一条约课信息，还有该约课信息的商家场景图
	 * @return boolean|unknown
	 */
	public function bestinfo($uid=0){
		$uid = session('user.id');
		$alias	= 'gi';
		$array	= array(
				'gi.sid',
				'gi.title',
				'se.path'
		);
		$join	= array(
				'__SHOPKEEPER_DETAIL__ sd on sd.sid = gi.sid',
				'__SHOPKEEPER_ENVIRON__ se on se.id = sd.environ_id',
		);
		$rel	= $this -> alias($alias)
					-> join($join)
					-> where('id=%d',$uid)
					-> order('ctime desc')
					-> field($array)
					-> find();
		if (!$rel) {
			return false;
		}
		return $rel;
	}



	/**
	 * 返回当前用户的约课总数
	 * @return unknown
	 */
	public function usermun($uid=0){
		$count = $this->where('uid=%d',$uid)->count();
		return $count;
	}

	//根据uid获取用户的约课信息
	public function groupByUser($uid=0,$curPage=1,$perPage=2,$sort='desc'){
	    $pageArray = $this->grouppage($curPage,$perPage,$uid); // 查询满足要求的总记录数
	    $rel   = $this->userGroupInfo($uid,0,$pageArray['pageOffset'],$pageArray['perPage'],$sort='desc');

	    $data=array(
	        'info'=>$rel,
	        'page'=>$pageArray,
	    );
	    return $data;
	}

	//增加一个访问数据
	public function addViewOne($gid=0){
// 	    $view=$this->where('id=%d',$gid)->setInc('view',1,300);
	    $view=$this->where('id=%d',$gid)->setInc('view');
	    return $view;
	}
	//增加一个跟约数据
	public function addAssistOne($gid=0){
// 	    $number=$this->where('id=%d',$gid)->setInc('number',1,300);
	    $number=$this->where('id=%d',$gid)->setInc('number');
	    return $number;
	}
	//减少一个跟约数据----暂时没用
	public function delAssistOne($gid=0){
// 	    $number=$this->where('id=%d',$gid)->setDec('number',1,300);
	    $number=$this->where('id=%d',$gid)->setDec('number');
	    return $number;
	}
	//增加一个推送数据
	public function addPushedOne($gid=0){
// 	    $pushed=$this->where('id=%d',$gid)->setInc('pushed',1,300);
	    $pushed=$this->where('id=%d',$gid)->setInc('pushed');
	    return $pushed;
	}
	//减少一个推送数据----暂时没用
	public function delPushedOne($gid=0){
// 	    $pushed=$this->where('id=%d',$gid)->setDec('number',1,300);
	    $pushed=$this->where('id=%d',$gid)->setDec('number');
	    return $pushed;
	}
	//增加一条评论数
	public function addCommOne($gid=0){
	    $comment = $this->where('id=%d',$gid)->setInc('gcomment');
	    return $comment;
	}
	//减少一条评论数
	public function delCommOne($gid=0){
	    $comment = $this->where('id=%d',$gid)->setDec('gcomment');
	    return $comment;
	}



	/**
	 * 根据用户的ID获取该用户发布的约课的ID
	 * @param number $uid
	 * @return Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
	 */
	public function groupidByUser($uid=0){
	    $countid = $this->where('uid=%d',$uid)->field('id')->select();
	    return $countid;
	}

	/**
	 * 查找某条约课的的发布人的联系方式
	 * @param number $gid
	 * @return unknown
	 */
	public function whoIssueByGid($gid=0){
        $alias='gi';
        $array = array(
            'gi.id',
            'gi.uid',
	        'u.firstname',
	        'u.lastname',
            'u.phone',
            'u.email',
        );

        $join  = array(
            'left join __USER__ u on u.id = gi.uid',
        );
        $GroupWho=$this     -> alias($alias)
                            -> join($join)
                            -> where('gi.id=%d',$gid)
                            -> field($array)
                            -> find();
        return $GroupWho;
	}

	/**
	 * 判断某个约课是否已经过期
	 * @param number $gid
	 * @return boolean|number
	 */
	public function getOvertimeByGid($gid=0){
	    $ov=$this->where('id=%d',$gid)->getField('overtime');
	    if (!$ov){
	        return false;
	    }
	    if ($ov>date('Y-m-d H:i:s')){
	        $ovstatus = 1;//还没过期
	    }else{
	        $ovstatus = 0;//已经过期
	    }
	    return $ovstatus;
	}

//=================================================admin======================================================


	public function getInfoAllBygid($gid=0,$curPage=1,$perPage=5){
		$alias = 'gi';//定义当前数据表的别名
		//要查询的字段
		$array = array(
				'gi.id',
				'gi.uid',
				'gi.cateid',
				'gi.title',
		        'gi.content',
				'gi.areaid',
				'gi.ltprice',
				'gi.gtprice',
				'gi.mode',
				'gi.tags',
				'gi.ctime',
				'gi.overtime',
				'gi.view',
				'gi.number',
				'u.id as userid',
				'u.firstname',
				'u.lastname',
				'u.avatar',
				'u.profession',
				'u.interest',
				'u.telstatus',
				'uv.vtype',
				'uv.vstatus',
				'cate.catename',
				'sk.company_name',
		        'skd.nickname',
				'skd.environ',
				'skd.avatar as skavatar',
				'skd.nickname',
    	        'count(ga.groupid) as ykcount',
    	        'count(gc.gid) as ykgccount',
	    );
		$join  = array(
				'__USER__ u on gi.uid = u.id',
				'left join __USER_V__ uv on uv.uid = u.id',
				'__CATEGORY__ cate on gi.cateid = cate.id',
				'left join __SHOPKEEPER__ sk on sk.id = gi.sid',
				'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gi.sid',
    	        'left join __GROUP_ASSIST__ ga on ga.groupid = gi.id',
    	        'left join __GROUPCOMMENT__ gc on gc.gid = gi.id',
		);
		//join可以使用array


		$res=$this  -> alias($alias)
					-> join($join)
					-> where('gi.id=%d',$gid)
					-> field($array)
					-> find();
		if (!$res){
		    return false;
		}
		$Mode=C('mode');
		foreach ($Mode as $k => $v){
		    if($res['mode']==$k){
		        $res['mode']= $v;
		    }
		}

		$res['tags'] = explode("|", $res['tags']);
		$res['areaname'] = D('Common/Area')->getAllById($res['areaid']);//地区名字

	    return $res;


	}













//============================	=======================	=======================




	/*
	 *  插入用户发布的心愿（组团信息）
	 *
	 */
	public function insert($sid,$cateid,$catename,$areaid,$nickname,$tags,$title,$priceid,$model,$content,$overtime,$environ){
	    // 检验是否达到了今天的发布课程上限
	    $count = $this->countGruopInfoToday(session('user.id'));
	    if (is_int($count) && $count > 10) {
	        //'您发布的课程已经达到了今天的上限'
	        return false;
	    }
	    $overtime=time()+$overtime*3600*24;
	    $overtime=date("Y-m-d 23:59:59",$overtime);

	    $ctime=date('Y-m-d H:i:s',time());
	    $data['uid']=session('user.id');
	    $data['sid'] =$sid;
	    $data['cateid'] = $cateid;
	    $data['areaid'] = $areaid;
	    $data['nickname'] = $nickname;
	    $data['catename'] =$catename;
	    $data['tags'] = $tags;
	    $data['title'] = $title;
	    $data['priceid'] = $priceid;
	    $data['mode'] = $model;
	    $data['overtime'] = $overtime;
	    $data['content'] = $content;
	    $data['environ'] = $environ;
	    $data['ctime'] = $ctime;
	    $res=$this->add($data);
	    if($res==true){

	        //给管理元发送短信
// 	        $managers=C('managers');
	        $body='有用户发布了新约课，请及时查看。登录17yueke.cn/g/'.$res;
	        $subject='【17约课】';
// 	        $phonecode=array();//保存返回的参数
// 	        require_once('./Api/sms/sms_send.php');
// 	        foreach ($managers as $telkey=>$telvalue){
// 	            $msg=$body.$subject;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
// 	            $phonecode[]=sendnote($telvalue,urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
// 	        }
	        //---------------------
	        //给管理元发送短信
	        $managersAmail=C('managersAmail');
	        $emailstatus=array();//保存返回的参数
	        foreach ($managersAmail as $emailkey=>$emailvalue){
	            $emailstatus[]=sendMail($emailvalue,$body, $subject);
	        }
	        //==========================

	        return true;
	    }else{
	        return false;
	    }

	}

    /**
     * 统计页面用
     */
    public function countGroupForSearch($map) {
        return $this->alias('gi')
                    ->join('__CATEGORY__ cate on gi.cateid = cate.id')
                    ->where($map)
                    ->count();
    }

	/**
	 * 获取组团的信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return string
	 */
	public function getGroup($pageOffset=0,$perPage=2,$sort='desc',$greet,$map,$parentCateId){
	    //要查询的字段
	    $array = array(
	        'gi.id',
	        'gi.uid',
	        'gi.cateid',
	        'gi.title',
	        'gi.areaid',
	        'gi.ltprice',
	        'gi.gtprice',
	        'gi.mode',
	        'gi.priceid',
	         'gi.catename as cate_name',
	        'gi.tags',
	        'gi.ctime',
	        'gi.overtime',
	        'gi.view',
	        'gi.number',
	        'gi.environ as user_environ',
	        'u.firstname',
	        'u.lastname',
	        'u.avatar',
	        'u.telstatus',
	        'u.profession',
	        'uv.vstatus',
	        'uv.vtype',
	        'cate.catename',
	        'cate.parent_id',
	        'sk.company_name',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	       'skd.environ',
	        'skd.area_detail',
	    );
	    $order = 'gi.cateid '.$sort;
	    $alias = 'gi';//定义当前数据表的别名
	    $join  = array(
	        '__USER__ u on gi.uid = u.id',
	        'left join __USER_V__ uv on gi.uid = uv.uid',
	        '__CATEGORY__ cate on gi.cateid = cate.id',
	        'left join __SHOPKEEPER__ sk on gi.sid = sk.id',
	        'left join __SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid'
	    );
	    //join可以使用array




	    if ($map) {
	        $where=$map;
	    }


	    if($greet){
	     $order = 'gi.number '.$sort;
	    }
	    $res=$this  ->alias($alias)
					->join($join)
					->where($where)
					->order($order)
					->limit($pageOffset,$perPage)
					->field($array)
					->select();
	    if (!$res){
	        return $res;
	    }
	    $res=$this->grouptags($res);
	    // 实例化 Groupcomment 模块
	    $Comm = D('Common/Groupcomment');
	    $GroupPushed = D('Common/GroupPushed');
	    $areaAllName=D('Common/Area');
	    $Price=D('Price');
	    $Category=D('Category');

	    //重新排序，把综合类的排在最下面
	    $parent_array=array();
	    $chirdren_array=array();
	    foreach ($res as $key=>$value){
	        $res[$key]['comment']= $Comm->Gcommentall($value['id']); // 调用 Groupcomment 模块中的方法Gcommentall,并且复制给$rel的comment，当前组团内的评论数
	        $res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
	        $res[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];//商家的头像
	        $res[$key]['environ']='/Public/Uploads/'.$value['environ'];//课程的头像
	        $res[$key]['coursemun']=$GroupPushed->coursenum($value['id']);//商家唉推送的课程总数
	        $res[$key]['ctime'] = transDate($value['ctime']);
	        $res[$key]['pricearr']=$Price->getId($value['priceid']);
			$res[$key]['ltprice']=floor($value['ltprice']);
	        $parent_id=$value['parent_id'];
	        $arr=$Category->where("id='$parent_id'")->field('catename')->find();
	        $res[$key]['name']=$arr['catename']."/".$value['catename'];
			if (strtotime($value['overtime'])>time()){
			    $res[$key]['overtimes'] = 1;
			}else{
			    $res[$key]['overtimes'] = 0;
			}
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($value['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}

			//重新排序，把综合类的排在最下面
			if ($value['cateid']==$parentCateId){
			    $parent_array[]=$value;
			}else {
			    $chirdren_array[]=$value;
			}
	    }

	    if ($parent_array){
	        array_push($chirdren_array, $parent_array);
	    }
	    return $chirdren_array;
	}


//-------------------------------------------根据分类的集合id去分页and统计总数--------------------------------------

	/**
	 * 返回筛选页面所选择类型的总记录数
	 * @param number $uid
	 * @return unknown
	 */
	public function cateStatisticsCountSearch( $map=array() , $keyword='' ){
	    $alias = 'gi';
	    $this  ->  alias($alias);
	    if ($keyword){
	        $join  =   array(
    	        '__USER__ u on gi.uid = u.id',
    	        'left join __USER_V__ uv on gi.uid = uv.uid',
    	        '__CATEGORY__ cate on gi.cateid = cate.id',
    	        'left join __SHOPKEEPER__ sk on gi.sid = sk.id',
    	        'left join __SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid'
	        );
	        $this  ->  join($join);
	    }
	    
	    $count = $this  ->  where($map)
                	    ->  count();
	    return $count;
	}
	
	public function  cateStatisticsPagesSearch($curPage=1,$perPage=5,$map=array(),$keyword=''){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $count= $this-> cateStatisticsCountSearch($map,$keyword); // 查询满足要求的总记录数
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return $pageArray;
	}
	
	
    //------------------------------------------------下面的方法还有用，暂时使用上面的方法进行测试 -----------------
	/**
	 * 返回筛选页面所选择类型的总记录数
	 * @param number $uid
	 * @return unknown
	 */
	 public function cateStatisticsCount($cateStatistics=''){
	     $where['cateid']=array('IN',$cateStatistics);
	     $count = $this->where($where)->count();
	     return $count;
	 }

     public function  cateStatisticsPages($curPage=1,$perPage=5,$cateStatistics=''){
         import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
         $count= $this-> cateStatisticsCount($cateStatistics); // 查询满足要求的总记录数
         $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
         $pageArray=$Page->getCounts();
         return $pageArray;
     }
//-------------------------------------	----------------------------------------------

	public function getGroupSearch($keywords){
	   //要查询的字段
	    $array = array(
	        'gi.id',
	        'gi.uid',
	        'gi.cateid',
	        'gi.title',
	        'gi.areaid',
	        'gi.ltprice',
	        'gi.gtprice',
	        'gi.mode',
	        'gi.priceid',
	         'gi.catename as cate_name',
	        'gi.tags',
	        'gi.ctime',
	        'gi.overtime',
	        'gi.environ as user_environ',
	        'gi.view',
	        'gi.number',
	        'u.firstname',
	        'u.lastname',
	        'u.avatar',
	        'u.telstatus',
	        'u.profession',
	        'uv.vstatus',
	        'uv.vtype',
	        'cate.catename',
	        'cate.parent_id',
	        'sk.company_name',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	        'skd.environ',
	        'skd.area_detail',
	    );
	    $alias = 'gi';//定义当前数据表的别名
	    $join  = array(
	        '__USER__ u on gi.uid = u.id',
	        'left join __USER_V__ uv on gi.uid = uv.uid',
	        '__CATEGORY__ cate on gi.cateid = cate.id',
	        'left join __SHOPKEEPER__ sk on gi.sid = sk.id',
	        'left join __SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid'
	    );
	    $res=$this->alias($alias)
					->join($join)
					->field($array)
					->limit(0,20)//skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or sk.company_name like  '%$keywords%'  or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or k.company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or sk.company_name like  '%$keywords'
					->where("gi.catename like '%$keywords%' or skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or sk.company_name like  '%$keywords%'  or gi.catename like '$keywords%' or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or sk.company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or sk.company_name like  '%$keywords'")
					->select();
	    $res=$this->grouptags($res);
	    // 实例化 Groupcomment 模块
	    $Comm = D('Common/Groupcomment');
	    $GroupPushed = D('Common/GroupPushed');
	    $areaAllName=D('Common/Area');
	    $Price=D('Price');
	    $Category=D('Category');
	    foreach ($res as $key=>$value){
	        $res[$key]['comment']= $Comm->Gcommentall($value['id']); // 调用 Groupcomment 模块中的方法Gcommentall,并且复制给$rel的comment，当前组团内的评论数
	        $res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
	        $res[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];//商家的头像
	        $res[$key]['environ']='/Public/Uploads/'.$value['environ'];//课程的头像
	        $res[$key]['coursemun']=$GroupPushed->coursenum($value['id']);//商家唉推送的课程总数
	        $res[$key]['ctime'] = transDate($value['ctime']);
	        if($value['priceid']!=0){
	        $res[$key]['pricearr']=$Price->getId($value['priceid']);
	        }
			$res[$key]['ltprice']=floor($value['ltprice']);
	        $parent_id=$value['parent_id'];
	        $arr=$Category->where("id='$parent_id'")->field('catename')->find();
	        $res[$key]['name']=$arr['catename']."/".$value['catename'];
			if (strtotime($value['overtime'])>time()){
			    $res[$key]['overtimes'] = 1;
			}else{
			    $res[$key]['overtimes'] = 0;
			}
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($value['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}
	    }
	    return $res;
	}

//=================================================================
	/**
	 * 返回筛选页面所选择类型的总记录数
	 * @param number $uid
	 * @return unknown
	 */
	 public function selectCount($cateid=0){
    	 $count = $this->where('cateid=%d',$cateid)->count();
    	 return $count;
	}

	public function  selectPages($curPage=1,$perPage=5,$cateid=0){
    	import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
    	$count= $this-> selectCount($cateid); // 查询满足要求的总记录数
    	$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
    	$pageArray=$Page->getCounts();
    	return $pageArray;
	}


	/**
	 * 统计某个商家一天之内发送了多少的课程
	 */
	public function countGruopInfoToday($uid) {
	    // 获取一天的时间始终
	    list($beginTime, $lastTime) = get_day_time_scope();
	    $beginTime = date('Y-m-d H:i:s', $beginTime);
	    $lastTime = date('Y-m-d H:i:s', $lastTime);

	    $count = $this->where('uid = %d and ctime >= "%s" and ctime < "%s"', $uid, $beginTime, $lastTime)
	    ->count();

	    if ($count === false) {
	        return $this->getDbError();
	    }
	    return intval($count);
	}


}
