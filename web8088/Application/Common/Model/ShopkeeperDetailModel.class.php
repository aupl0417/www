<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家详细信息Model
 * @author jmjoy
 *
 */
class ShopkeeperDetailModel extends CommonModel {

	/**
	 * 商家的ID
	 */
	public $sid = 0;

	/**
	 * 验证规则
	 */
	protected $_validate = array(
			array('nickname', '/^[\-\w\x{4e00}-\x{9fa5}]{2,12}$/u', '昵称不正确！', 2, 'regex'),

			array('areaid', '/^\d+$/u', '地区不正确！', 2, 'regex'),

			array('remark', '/^.{0,50}$/u', '备注不正确！', 2, 'regex'),
	);

	/**
	 * 前置方法
	 * @see \Think\Model::_initialize()
	 */
	public function _initialize() {
		if (!session('?shopkeeper.id')) {
			return;
		}
		$this->sid = session('shopkeeper.id');
	}

	/**
	 * 根据post提交过来的商家id查询一条详细信息
	 * @return boolean|null|array
	 */
	public function getBySid($sid = null) {
		if ($sid === null) {
			$sid = I('post.sid', 0, 'intval');
		} else {
			$sid = intval($sid);
		}

        // 需要获取的字段
        $fields = [
            's.id',
            's.company_name',
            's.login_email',
            's.login_phone',
            's.password',
            's.company_name',
            's.tel',
            's.ctime',
            's.status',

            'sd.nickname',
            'sd.avatar',
            'sd.cateid',
            'sd.catename',
            'sd.area_detail',
            'sd.environ',
            'sd.features',
            'sd.remark',
            'sd.age',
            'sd.website',
            'sd.teacher_power',
        ];

        $resArr =  $this->alias('sd')
                        ->field($fields)
                        ->join('RIGHT JOIN __SHOPKEEPER__ s on s.id = sd.sid')
						->where('s.id = %d', $sid)
						->find();

		if (!$resArr) {
			return array();
		}

        if ($resArr['areaid']) {
            // 根据地区ID获取所有级地区名称！
            $area_row = M('Area')->alias("a1")
                                ->field(array(
                                    "a1.areaname a1_areaname",
                                    "a2.areaname a2_areaname",
                                    "a3.areaname a3_areaname"))
                                ->join("__AREA__ a2 on a1.id = a2.parentid")
                                ->join("__AREA__ a3 on a2.id = a3.parentid")
                                ->where("a3.id = %d", $resArr['areaid'])
                                ->find();
            // 将地区名称拼接到结果数组里面
            $resArr['areaname'] = $area_row['a1_areaname'] . " "
                                . $area_row['a2_areaname'] . " "
                                . $area_row['a3_areaname'] . " ";
        }

		return $resArr;
	}

	/**
	 * 更新认证信息（这个好像没用了）
	 * @return Ambigous <multitype:string NULL , multitype:NULL string >|string|boolean
	 */
	public function handleUpsert() {
		// 看看是否登陆了
		if (!$this->sid) {
			return '商家还没有登陆呢';
		}
		// 验证信息是否合法
		if (!$this->create()) {
			return $this->getError();
		}
		// 上传营业执照
		list($filepath, $err) = $this->validateCardPic();
		// 检测是否上传成功
		if ($err !== null) {
			return $err;
		}
		// 要插入数据库的数据
		$data = array_merge($this->data, array(
				'sid'		=>	$this->sid,
				'avatar'	=>	'shop_avatar/' . $filepath,
		));
		// 添加认证信息
		if (!$this->data($data)->add()) {
			return $this->getDbError();
		}
		// 修改成功
		return true;
	}

	/**
	 * 检测上传是否成功，成功会上传的
	 * @return multitype:NULL string |multitype:string NULL
	 */
	public function validateCardPic() {
		// 实例化并配置上传类
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->rootPath = './Public/Uploads/shop_avatar/';
		$upload->savePath = '';
		$upload->saveName = array('uniqid','');
		$upload->exts     = array('jpg', 'png', 'jpeg');
		$upload->autoSub  = true;
		$upload->subName  = array('date','Y/m/d');
		// 上传哦！
		$info = $upload->uploadOne($_FILES['avatar']);
		if (!$info) {
			// 上传失败
			return array(null, $upload->getError());
		}
		// 上传成功
		return array($info['savepath'].$info['savename'], null);
	}


	/**
	 * 获取商家的公司简称和LOGO
	 * @return array
	 */
	public function getShopName($offset=0, $limit=12){
	    $alias='sd';
		//要查询的字段
		$array = array(
		         'sd.sid',
				'sd.nickname',
				'sd.avatar',

		);
		$order = 'sd.id desc';
		$join  = array(
		    'left join __SHOPKEEPER__ sk on sk.id = sd.sid',
		);
		$res=$this->alias($alias)
		->order($order)
		->field($array)
		->join($join)
		->where("sk.status>=1")
		->limit($offset, $limit)
		->select();
		return $res;
	}

	/**
	 * 获取商家的信息
	 * @return array
	 */
	public function getshopkeeper($pageOffset,$perPage,$area_id,$cate_id,$greet,$nearby,$map,$sort='desc',$parentCateId){
	    //$where="cateid='$cate_id' and sk.status='1'";

		$order = 'sd.cateid '.$sort;
		//要查询的字段
		$array = array(
				'sd.sid',
				'sd.nickname',
				'sd.avatar',
				'sd.cateid',
				'sd.areaid',
				'sd.comment',
				'sd.area_detail',
				'sk.login_phone',
				'sk.status',
		);
		$alias = 'sd';//定义当前数据表的别名
		$join  = array(
				'left join __SHOPKEEPER__ sk on sk.id = sd.sid',
				'left join __CATEGORY__ cate on cate.id = sd.cateid',
		);



		if($map){
			$where=$map;
		}else {
		    $where="cateid='$cate_id'";
		}

		if($greet){
		$order = 'sd.comment '.$sort;
		}
		$res=$this  -> alias($alias)
            		-> join($join)
            		-> where($where)
            		-> order($order)
            		-> field($array)
            		-> limit($pageOffset,$perPage)
            		-> select();
		return $res;
		if (!$res){
		    return false;
		}
		//重新排序，把综合类的排在最下面
		$parent_array=array();
		$chirdren_array=array();
	    foreach ($res as $key => $row) {
	        //重新排序，把综合类的排在最下面
	        if ($row['cateid']==$parentCateId){
	            $parent_array[]=$row;
	        }else {
	            $chirdren_array[]=$row;
	        }
	    }
	    if (!empty($parent_array)){
	        $allShopkeeper = array_merge($chirdren_array, $parent_array);
	    }else {
	        return $chirdren_array;
	    }
	    return $allShopkeeper;
	}

//-------------------------------------------根据分类的集合id去分页and统计总数--------------------------------------
	/**
	 * 返回筛选页面所选择类型的总记录数
	 * @param number $uid
	 * @return unknown
	 */
	 public function cateStatisticsCount($cateStatistics='',$area_id=0){
	     $where['cateid']=array('IN',$cateStatistics);
	     if ($area_id!=0){
	         $where['areaid'] = array('EQ',$area_id);
	     }
	     $count = $this->where($where)->count();
	     return $count;
	     }

     public function  cateStatisticsPages($curPage=1,$perPage=5,$cateStatistics='',$area_id){
         import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
         $count= $this-> cateStatisticsCount($cateStatistics,$area_id); // 查询满足要求的总记录数
         $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
         $pageArray=$Page->getCounts();
         return $pageArray;
     }
//-------------------------------------	----------------------------------------------


    public function getSearchInfoPage( $keywords , $curPage , $perPage ){
        import("Common.Util.AjaxPage");
        $where = "sk.company_name like '%$keywords%' or sd.nickname like '%$keywords%' or cate.catename like  '%$keywords%' or cate.desc like  '%$keywords%' or sk.company_name like '$keywords%' or sd.nickname like '$keywords%' or cate.catename like  '$keywords%' or cate.desc like  '$keywords%' or sk.company_name like '%$keywords' or sd.nickname like '%$keywords' or cate.catename like  '%$keywords' or cate.desc like  '%$keywords'";

        $alias = 'sd';//定义当前数据表的别名
        $join  = array(
            'left join __SHOPKEEPER__ sk on sk.id = sd.sid',
            '__CATEGORY__ cate on cate.id = sd.cateid',
        );
        $count= $this     -> alias($alias)
            		      -> join($join)   
            		      -> where($where)
                          -> count(); 
        $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  
        $pageArray=$Page->getCounts();
        return $pageArray;
    }

	public function getSearchInfo($keywords ,  $pageOffset , $perPage){
		//要查询的字段
		$array = array(
				'sd.sid',
				'sd.nickname',
				'sd.avatar',
				'sd.area_detail',
				'sk.login_phone',
		        'sk.company_name',
				'cate.catename',
				'cate.desc',
		);
		$alias = 'sd';//定义当前数据表的别名
		$join  = array(
				'left join __SHOPKEEPER__ sk on sk.id = sd.sid',
				'__CATEGORY__ cate on cate.id = sd.cateid',
		);
		$res=$this  -> alias($alias)
            		-> join($join)
            		-> field($array)
            		-> limit($pageOffset,$perPage)
            		-> where("sk.company_name like '%$keywords%' or sd.nickname like '%$keywords%' or cate.catename like  '%$keywords%' or cate.desc like  '%$keywords%' or sk.company_name like '$keywords%' or sd.nickname like '$keywords%' or cate.catename like  '$keywords%' or cate.desc like  '$keywords%' or sk.company_name like '%$keywords' or sd.nickname like '%$keywords' or cate.catename like  '%$keywords' or cate.desc like  '%$keywords'")
            		-> select();
		return $res;
	}

	/**
	 * 获取商家的信息
	 * @return array
	 */

	public function getBusinessInfo($sid){
		//要查询的字段
		$array = array(
				'sd.nickname',
				'sd.avatar',
		        'sd.areaid',
				'sd.remark',
				'sd.area_detail',
				'sk.login_phone',
		);
		$alias = 'sd';//定义当前数据表的别名
		$join  = array(
				'__SHOPKEEPER__ sk on sk.id = sd.sid',
		);
		$where="sd.sid=".$sid;
		$res=$this->alias($alias)
		->join($join)
		->field($array)
		->where($where)
		->find();
		return $res;
	}


	/**
	 * 商家详细信息
	 * @param unknown $id
	 * @return multitype:boolean |unknown
	 */
	public function info($id, $getLastInfo = true) {
		// 检测是不是商家本人
		$isMe = false;
		if (session("shopkeeper.id") == $id) {
			$isMe = true;
		}

		// 查询商家基本信息
		$fields = array(
				'sd.nickname',
				'sd.remark',
				'sd.avatar',
				'sd.features',
				'sd.areaid',
				'sd.area_raw',
				'sd.area_detail',
				'sd.environ',
                'sd.age',
                'sd.website',
                'sd.teacher_power',
                'sd.cateid',
                'sd.catename',
				's.id',
				's.company_name',
				's.login_phone',
				's.tel',
				's.status',
		);
		$resArr = $this->alias('sd')
		->field($fields)
		->join("RIGHT JOIN __SHOPKEEPER__ s ON sd.sid = s.id")
		->where("s.id = %d", $id)
		->find();
		// 这位商家还没有填写资料
		if ($resArr === null) {
			return array("isMe" => $isMe);
		}

		// 获取两级基本地区名称
		if ($resArr["areaid"]) {
			$areanames = D("Common/Area")->getTwoLevelNameInArr(array($resArr["areaid"]));
			$resArr["areanames"] = $areanames[0];
		}

		// 返回数据
		$resArr["isMe"] = $isMe;

		// 最后一条发布课程信息
		if ($getLastInfo) {
			$resArr["shopInfo"] = D('Common/ShopInfo')->lastOne($id);
		}

        // 商家有多少课程
        $resArr['infoCount'] = M('ShopInfo')->where('sid = %d', $id)->count();

		return $resArr;
	}

	/**
	 * 处理商家个人信息修改
	 */
	public function handleEdit() {
        // 看看这个商家的企业邮箱有没有激活，没有的话，不给修改！
        if (!session('shopkeeper.status')) {
            return '您的企业邮箱还没有激活';
        }

		// 验证规则
		$validate = array(
				array('nickname', '/^[\-\w\x{4e00}-\x{9fa5}]{2,12}$/u', '昵称应该在2~12个字之间！', 1, 'regex'),
				array('remark', '/^[\s\S]{0,512}$/u', '机构简介不能超过512个字！', 2, 'regex'),
				array('tel', '/^\d{3,4}\-\d{7,8}$/', '固定电话不正确！', 2, 'regex'),
				array('areaid', '/^\d{1,8}$/', '基础地址不正确！', 2, 'regex'),
                array('cateid', '/^\d+$/', '请选择正确的机构类别！', 1, 'regex'),
				array('area_raw', '/^[\s\S]{0,40}$/u', '详细地址不能超过40个字！', 2, 'regex'),
				array('age', '/^\d+$/', '机构年龄应该是一个整数！', 1, 'regex'),
				array('age', [1, 65535], '机构年龄应该大于0！', 1, 'between'),
				array('website', '/^[\s\S]{0,255}$/u', '机构网站的长度应该为0~255字符！', 2, 'regex'),
				//array('website', 'validate_url', '机构网站不是一个正确的地址！', 2, 'function'),
				array('teacher_power', '/^[\s\S]{0,255}$/u', '师资力量的长度应该为0~255字符！', 2, 'regex'),
		);

		// 验证
		if (!$this->validate($validate)->create()) {
			return $this->getError();
		}

		// 验证features
		if ($this->features != '') {
			$strArr = explode('|', $this->features);
			if (count($strArr) > 3) {
				return "特色过多";
			}
		}

		// 处理上传操作
		$avatarPath = "";
		if (!empty($_FILES['file'])) {
			list($avatarPath, $err) = $this->validateUpload(2*1024*1024, 'shop_avatar', 'file', [75,75]);
			if ($err !== null) {
				return $err;
			}
		}

		// 商家的场景图
		$environPath = "";
		if (!empty($_FILES['environFile'])) {
			list($environPath, $err) = $this->validateUpload(1024*1024, 'shop_environ', 'environFile', [130,100]);
			if ($err !== null) {
				return $err;
			}
		}

		// 执行数据修改
		M('Shopkeeper')->where("id = %d", session("shopkeeper.id"))
                        ->setField("tel", $_POST["tel"]);

		// 判断是增加操作还是更新操作
		$count = $this->where("sid = %d", session("shopkeeper.id"))->count();
		// 组装数据，这里写成这样是以前以为$this->xxx=xxxx不能用
		$data['nickname'] = $this->nickname;
		$data['features'] = $this->features;
		$data['remark'] = $this->remark;
        $data['cateid'] = $this->cateid;
		$data['areaid'] = $this->areaid;
		$data['ftime'] = current_datetime();
		$data['area_raw'] = $this->area_raw;
        $data['age'] = $this->age;
        $data['website'] = $this->website;
        $data['teacher_power'] = $this->teacher_power;

		// 这里的area_detail灰常
        $data['area_detail'] = $this->area_raw;

        if ($this->areaid) {
            $areanames = D('Common/Area')->getTwoLevelNameInArr($this->areaid);

            if (is_array($areanames)) {
                $data['area_detail'] = $areanames[0]['parent_arename'] .
                                        $areanames[0]['this_arename'] .
                                        $this->area_raw;
            }

        }

        // 获取分类的名称
        $catename = M('Category')->where('id = %d', $data['cateid'])->getField('catename');
        if ($catename) {
            $data['catename'] = $catename;
        } else {
            $data['catename'] = '';
        }

        // 放到session
        session('shopkeeper.nickname', $this->nickname);
        session('shopkeeper.features', $this->features);
        session('shopkeeper.remark', $this->remark);
        session('shopkeeper.areaid', $this->areaid);
        session('shopkeeper.ftime', $this->ftime);
        session('shopkeeper.area_detail', $this->area_detail);
		session('shopkeeper.age', $this->age);
		session('shopkeeper.cateid', $this->cateid);

		// 图片路径
		if ($avatarPath) {
			$data['avatar'] = $avatarPath;
            session('shopkeeper.avatar', $avatarPath);
		}
		if ($environPath) {
			$data['environ'] = $environPath;
            session('shopkeeper.environ', $environPath);
		}

		if (!$count) {
			$data['sid'] = session("shopkeeper.id");
			$this->add($data);
		} else {
			$result = $this->where("sid = %d", session("shopkeeper.id"))
							->save($data);
		}

		return true;
	}

	/**
	 * 注册的时候给商家一个默认的头像
	 * @param unknown $shopkeeper_id
	 * @param unknown $company_name
	 */
	public function addDefault($shopkeeper_id, $company_name) {
		// 注册的时候给商家一个默认的头像
		$this->data([
				'sid'		=>	$shopkeeper_id,
				'nickname'	=>	mb_substr($company_name, 0, 8, 'utf-8'),
				'avatar'	=>	'shop_avatar/shop_default_avatar.jpg',
				'ftime'		=>	current_datetime(),
		]);

		$this->add();
	}

	//=================================================================
	/**
	 * 返回发布心愿用户所选择类型的总记录数
	 * @param number $uid
	 * @return unknown
	 */
	 public function infoCount($cateid=0,$area_id=0){
	     if ($area_id){
	         $where['cateid'] = array('EQ',$cateid);
	         $where['areaid'] = array('EQ',$area_id);
	         $count = $this->where($where)->count();
	     }else {
	         $count = $this->where('cateid=%d',$cateid)->count();
	     }
	     return $count;
	}

	public function getInfopages($curPage=1,$perPage=5,$cateid=0,$area_id){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $count= $this->infoCount($cateid,$area_id); // 查询满足要求的总记录数
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return $pageArray;
	}


}


