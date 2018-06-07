<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author jmjoy
 *
 */

class ShopInfoModel extends CommonModel {

    /**
     * （验证规则，有可能没用了）
     */
	//protected $_validate=array(

	//        array('title', '/^.{1,25}$/u', '标题必须在25个字符以内！', 1, 'regex'),

	//        array('cateid', '/^\d+$/', '分类ID不是数字！', 1, 'regex'),

	//        array('areaid', '/^\d+$/', '地区ID不是数字！', 1, 'regex'),

	//        array('price', '/^\d+\.*\d*$/', '价钱不合法！', 1, 'regex'),

	//        array('mode', '/^\d+$/', '模式ID不是数字！', 1, 'regex'),

	//        array('teacherneed', '/^.{1,50}$/u', '教师需求必须在250个字符以内！', 1, 'regex'),

	//        array('content', '/^.{1,255}$/u', '课程内容必须在255个字符以内！', 1, 'regex'),

	//        array('tags', '/^.{0,120}$/u', '标签内容必须在255个字符以内！', 1, 'regex'),

	//);


	/**
	 * 获取发布课程的信息（年少无知，写出这种方法，是我的错）
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @param mixed $info_id 如果为null， 表示不用， 如果为一个值，表示where id = x, 如果为数组， 表示where id in (x, x, x)
	 * @return string
	 */
	public function listInfo($pageOffset = 0, $perPage = 3, $sort = 'desc', $info_id = null, $shopkeeper_id = null, $showContent = false, $is_top = false) {
		// 过滤
		$pageOffset = intval($pageOffset);
		$perPage = intval($perPage);
		if ($sort != 'asc' && $sort != 'desc') {
			$sort = 'desc';
		}
		// 要查询的字段
		$array = array(
				'si.id',
				'si.sid',
				'si.cateid',
				'si.title',
				'si.areaid',
				'si.price',
		       'si.price',
				'si.mode',
				'si.tags',
				'si.ctime',
				'si.view',
				'si.area_detail',
				'si.phone_tel',
                'si.overtime',
                'si.preferent',
                'si.teacher_age',
                'si.teacher_exp',
                'si.teacher_feature',
                'si.teacher_remark',

				'skd.avatar',
				'si.environ',
				'cate.catename',
				's.company_name',
				's.login_phone',
				's.tel',
		        's.status',
				'skd.nickname',
// 				'count(siu.id) `user_count`',
// 				'count(sc.id) `comment_count`',
				'si.number user_count',
				'si.comment_count',
		);

		// 要不要显示课程信息的内容
		if ($showContent) {
			$array[] = 'si.content';
		}

		// 定义当前数据表的别名
		$alias = 'si';
		// 联合查询
		$join  = array(
				'LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id',
				'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
				'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
// 				'LEFT JOIN __SHOPKEEPER_ENVIRON__ se on skd.environ_id = se.id',
// 				'LEFT JOIN __SHOP_INFO_USER__ siu on si.id = siu.shop_info_id',
// 				'LEFT JOIN __SHOP_COMMENT__ sc on si.id = sc.iid and sc.parent_id = 0',
		);

        $order = 'si.id ' . $sort;
        // 是否置顶课程显示在最前面
        if ($is_top) {
            $order = 'si.is_top desc, ' . $order;
        }

		$this->alias($alias)
			->field($array)
			->join($join)
			->order($order)
			->limit($pageOffset, $perPage);
// 			->group('si.id');

		// 是不是根据课程信息的Id查找
		if ($info_id !== null) {
			if (is_array($info_id)) {
				$this->where("si.id in (%s)", implode(',', $info_id));
			} else {
				$this->where("si.id = %d", $info_id);
			}
		}

		// 是不是根据商家Id查找
		if ($shopkeeper_id !== null) {
			$this->where('si.sid = %d', $shopkeeper_id);
		}

		$res = $this->select();

		if ($res === null) {
			return null;
		}

		// 获取tags
		$res = $this->grouptags($res);

		// 获取结果数组的所以地区ID
		$areaids = array();
		foreach ($res as $key => $row) {
			$areaids[] = $row['areaid'];
		}
		// 获取两级地区名称
		$areanames = D('Common/Area')->getTwoLevelNameInArr($areaids);
		// 将地区信息放进结果数组
		$tmpAreanames = array();
		foreach ($areanames as $row) {
			$tmpAreanames[$row["id"]][] = $row["parent_arename"];
			$tmpAreanames[$row["id"]][] = $row["this_arename"];
		}

        $nowTime = time();

        // 处理返回数组
		foreach ($res as $key => $row) {
            // 获取地区名称数组
			$res[$key]['areanames'][] = $tmpAreanames[$row["areaid"]];
            // 课程内容格式化成有<br/>
			$res[$key]['content'] = clean_br_content($row['content']);

            // 美化创建时间
			$res[$key]['ctime'] = transDate($row['ctime']);

            // 获取上课模式
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($row['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}

            // 判断是否过期
            if ($row['overtime']) {
                $res[$key]['isTimeOut'] = ($nowTime > strtotime($row['overtime']));
            } else {
                $res[$key]['isTimeOut'] = false;
            }

            // 将价钱去掉小数点后位数
            $res[$key]['price'] = floor($row['price']);
		}

		return $res;
	}

	/**
	 * 增加组团信息的 标签
	 * @param array $tags
	 * @param number $id
	 */
	public function addtags($tags,$id=0){
		if (!empty($tags)) {
			$info= $this-> where("id=%d",$id)->field(array('id','tags'))->find();
			$tags= $info['tags'].'|'.$tags;
			$array=array('tags'=>$tags);
			$res = $this-> where("id=%d",$id)->filter('strip_tags')->setField($array);
			print_r($res);exit;
			return  $res;
		}
	}



	/**
	 * 把组团信息的标签分割成数组
	 * @param array $info
	 * @return array $info
	 */
	public function grouptags($info = array()){
		//把组团标签以，逗号分割成数组
		foreach ($info as $i => $rows)
		{
			$rows['tags']=explode("|", $rows['tags']);
			$info[$i]['tags']=$rows['tags'];
		}
		return $info;
	}

	/**
	 * 返回总组团信息数
	 * @return int $res
	 */
	public function groupinfocount(){
		$res = $this->order('id')->count();
		return  $res;
	}


	/**
	 * 组团分页数据
	 * 返回分页信息
	 * @param int $curPage
	 * @param int $perPage
	 * @return array $page
	 */
	public function grouppage($curPage=1,$perPage=5){

		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		$count= $this->groupinfocount(); // 查询满足要求的总记录数
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}

	/**
	 * （有可能废弃了）
	 * @return string|boolean
	 */
	//public function addOne() {
	//    if (!$this->create()) {
	//        return $this->getError();
	//    }
	//    if (!$this->add()) {
	//        return $this->getDbError();
	//    }
	//    return true;
	//}

	/**
	 * 主页的课程推广（底部信息）
	 * 返回数组
	 * @return array $page
	 */
	public function gener($limit=2, $is_detail=false) {
		//要查询的字段
		$array = array(
				'si.id',
				'si.title',
				'si.sid',
				'skd.nickname',
				'skd.avatar',
		);
        if ($is_detail) {
            $array = array_merge($array, array(
                'si.environ',
                'si.mode',
				'si.number user_count',
                'si.comment_count',
                'si.teacher_feature',
                'si.price',
            ));
        }
		$alias = 'si';//定义当前数据表的别名
		$join  = array(
				'__SHOPKEEPER_DETAIL__ skd on skd.sid =si.sid '
		);
		$where="si.sid=skd.sid";
		$order = 'si.number desc';
		$res=$this->alias($alias)
		->join($join)
		->where($where)
		->order($order)
		->limit($limit)
		->field($array)
		->cache(true,3600)
		->select();

        // 获取上课模式
        if ($is_detail) {
            $mode = C('mode');
            foreach ($res as $key => $row) {
                $res[$key]['mode'] = $mode[$row['mode']];
            }
        }

		return $res;
	}

	/**
	 * 找一条组团信息，根据ID
	 * @param unknown $info_id
	 * @return NULL|Ambigous <>
	 */
	public function getInfoById($info_id) {
		$res = $this->listInfo(0, 1, 'desc', $info_id);
		if (!$res) {
			return null;
		}
		return $res[0];
	}

	/**
     * 商家添加一条课程信息
     * @param $starTime int 课程准备填写的时间
	 * @return string|Ambigous <multitype:NULL , multitype:string NULL , multitype:NULL string >|boolean
	 */
	public function addCourse($starTime) {

        // 检验是否达到了今天的发布课程上限
        $count = $this->countShopInfoToday(session('shopkeeper.id'));
        if (is_int($count) && $count > 10) {
            return '您发布的课程已经达到了今天的上限';
        }

		// 验证规则
		$rules = array(
			array('cateid', '/^\d+$/', '请选择培训类型', 1, 'regex'),
			array('phone_tel', '/(^1\d{10}$)|(^\d{3,4}\-\d{7,8}$)/', '联系电话格式不正确', 1, 'regex'),
			array('area_detail', '/^[\s\S]{1,32}$/u', '联系地址不能超过32个字符', 1, 'regex'),
			array('price', 'is_numeric', '请选择价钱', 1, 'function'),
			array('mode', '/^\d+$/', '请选择上课模式', 1, 'regex'),
			array('title', '/^[\s\S]{1,30}$/u', '课程标题不能超过30个字符', 1, 'regex'),
			array('content', '/^[\s\S]{1,500}$/u', '课程内容不能超过500个字符', 1, 'regex'),
            array('overtime', [1, 91], '截止时间填写不正确' ,1, 'between'),
			array('preferent', '/^[\s\S]{0,500}$/u', '优惠内容不能超过500个字符', 1, 'regex'),
			array('teacher_age', '/^\d+$/', '教师教龄需要是个整数', 1, 'regex'),
			array('teacher_exp', '/^[\s\S]{1,125}$/u', '教师经验不能超过125个字符', 1, 'regex'),
			array('teacher_remark', '/^[\s\S]{1,512}$/u', '教师备注不能超过512个字符', 1, 'regex'),
			array('teacher_feature', '/^[\s\S]{1,512}$/u', '教师特点不能超过512个字符', 1, 'regex'),
		);

		// 检验
		if (!$this->validate($rules)->create()) {
			return $this->getError();
		}

        //  校验标签
        if ($this->tags === '') {
            return '标签不能为空';
        }
        $count = count(explode('|', $this->tags));
        if ($count < 1 || $count > 3) {
            return '标签数量不对';
        }

		// 检验上传的场景图是不是正确的
		list($filepath, $err) = $this->validateUpload(1*1024*1024, 'shop_environ', 'file', [130,100]);
		if ($err !== null) {
			return $err;
		}

		// 额外的数据
		$this->sid = session('shopkeeper.id');
		$this->ctime = current_datetime();
		$this->environ = $filepath;
        // 过期时间
        $overtime = time() + ($this->overtime - 1) * 3600 * 24;
        $this->overtime = date("Y-m-d H:i:s", $overtime);

		// 这里的area_detail灰常
		$areanames = D('Common/Area')->getTwoLevelNameInArr($this->areaid);

		if (is_array($areanames)) {
			$this->area_detail = $areanames[0]['parent_arename'] . $areanames[0]['this_arename'] . $this->area_detail;
		}

		// 增加
		if (!$insert_id = $this->add()) {
			return $this->getDbError();
		}

        // 发送消息给管理员
        $msg = '有商家发布了新课程，请及时查看。登录17yueke.cn/s/'.$insert_id.'。【17约课】';
        $this->sendEmailToManager($msg);

		// 成功
		return true;
	}
	
	/**
		这个函数是暂时用来测试的（少了图片上传操作）
	*/
	public function addCourse1($starTime) {

        // 检验是否达到了今天的发布课程上限
        $count = $this->countShopInfoToday(session('shopkeeper.id'));
        if (is_int($count) && $count > 10) {
            return '您发布的课程已经达到了今天的上限';
        }

		// 验证规则
		$rules = array(
			array('cateid', '/^\d+$/', '请选择培训类型', 1, 'regex'),
			array('phone_tel', '/(^1\d{10}$)|(^\d{3,4}\-\d{7,8}$)/', '联系电话格式不正确', 1, 'regex'),
			array('area_detail', '/^[\s\S]{1,32}$/u', '联系地址不能超过32个字符', 1, 'regex'),
			array('price', 'is_numeric', '请选择价钱', 1, 'function'),
			array('mode', '/^\d+$/', '请选择上课模式', 1, 'regex'),
			array('title', '/^[\s\S]{1,30}$/u', '课程标题不能超过30个字符', 1, 'regex'),
			array('content', '/^[\s\S]{1,500}$/u', '课程内容不能超过500个字符', 1, 'regex'),
            array('overtime', [1, 91], '截止时间填写不正确' ,1, 'between'),
			array('preferent', '/^[\s\S]{0,500}$/u', '优惠内容不能超过500个字符', 1, 'regex'),
			array('teacher_age', '/^\d+$/', '教师教龄需要是个整数', 1, 'regex'),
			array('teacher_exp', '/^[\s\S]{1,125}$/u', '教师经验不能超过125个字符', 1, 'regex'),
			array('teacher_remark', '/^[\s\S]{1,512}$/u', '教师备注不能超过512个字符', 1, 'regex'),
			array('teacher_feature', '/^[\s\S]{1,512}$/u', '教师特点不能超过512个字符', 1, 'regex'),
		);

		// 检验
		if (!$this->validate($rules)->create()) {
			return $this->getError();
		}

        //  校验标签
        if ($this->tags === '') {
            return '标签不能为空';
        }
        $count = count(explode('|', $this->tags));
        if ($count < 1 || $count > 3) {
            return '标签数量不对';
        }

		// 检验上传的场景图是不是正确的
		// list($filepath, $err) = $this->validateUpload(1*1024*1024, 'shop_environ', 'file', [130,100]);
		// if ($err !== null) {
			// return $err;
		// }

		// 额外的数据
		$this->sid = session('shopkeeper.id');
		$this->ctime = current_datetime();
		// $this->environ = $filepath;
		$this->environ = "shop_environ/2015/03/20/550bc2b1c0efd.jpg";
        // 过期时间
        $overtime = time() + ($this->overtime - 1) * 3600 * 24;
        $this->overtime = date("Y-m-d H:i:s", $overtime);

		// 这里的area_detail灰常
		$areanames = D('Common/Area')->getTwoLevelNameInArr($this->areaid);

		if (is_array($areanames)) {
			$this->area_detail = $areanames[0]['parent_arename'] . $areanames[0]['this_arename'] . $this->area_detail;
		}

		// 增加
		if (!$insert_id = $this->add()) {
			return $this->getDbError();
		}

        // 发送消息给管理员
        $msg = '有商家发布了新课程，请及时查看。登录17yueke.cn/s/'.$insert_id.'。【17约课】';
        $this->sendEmailToManager($msg);

		// 成功
		return true;
	}

	/**
	 * 最后一条发布信息
	 * @param unknown $id
	 */
	public function lastOne($id = null) {
		if ($id !== null) {
			$this->where('sid = %d', $id);
		}
		return $this->field(array('id', 'title', 'number', 'environ'))
					->order('ctime desc')
					->find();
	}

	/**
	 * 统计信息数量
	 * @param unknown $info_id
	 */
	public function countUserInfo($info_id) {
		return M('ShopInfoUser')->where('shop_info_id = %d', $info_id)->count();
	}

	/**
	 * 获取报名的人的信息
	 * @param unknown $info_id
	 */
	public function getUsersInfo($info_id, $nowPage, $perPage) {
		return M('ShopInfoUser')->alias('siu')
									->field('u.id, u.avatar,siu.visitor_id')// 游客
									->join('left join __USER__ u on siu.user_id = u.id')
									->where('siu.shop_info_id = %d', $info_id)
									->page($nowPage, $perPage)
									->select();
	}

	/**
	 * 返回简单的几条信息
	 * @param unknown $shopkeeper_id
	 * @return unknown
	 */
	public function getSimpleInfo($shopkeeper_id, $nowPage, $perPage) {
		$this->field(array(
			'si.id',
			'si.sid',
			'si.title',
			'si.content',
			'si.ctime',
			's.tel',
			'sd.avatar',
            'si.environ',
		));
		$resArr = $this->alias('si')
						->join('LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id')
						->join('LEFT JOIN __SHOPKEEPER_DETAIL__ sd on si.sid = sd.sid')
						->where('si.sid = %d', $shopkeeper_id)
						->order('si.id desc')
						->page($nowPage, $perPage)
						->select();
		foreach ($resArr as $keyArr=>$valueArr){
		    $resArr[$keyArr]['content']=clean_br_content($valueArr['content']);
		}
		return $resArr;
	}

	/**
	 * 统计某个商家的
	 * @param unknown $shopkeeper_id
	 */
	public function countInfoBySid($shopkeeper_id) {
		return $this->where('sid = %d', $shopkeeper_id)->count();
	}

	/**
	 * 用户报名
	 * @param unknown $info_id
	 * @param unknown $user_id
	 * @return boolean
	 */
	public function enroll($info_id, $user_id, $user_phone, $smsVerify = null) {
        // 校验手机的合法性
        if (!preg_match('/^1\d{10}$/', $user_phone)) {
            return '手机号码不合法';
        }

        if (!$this->checkRealPhone($user_phone, $smsVerify)) {
            return '验证码不正确';
        }

		// 先看看有没有报名
		$count = M('ShopInfoUser')->where('shop_info_id = %d and user_id = %d', $info_id, $user_id)
									->count();
		if ($count === false) {
			return $this->getDbError();
		}

		if ($count > 0) {
			return '您已经报名了';
		}

		// 增加报名的人
		M('ShopInfoUser')->data(array(
				'shop_info_id'	=>	$info_id,
				'user_id'		=>	$user_id,
				'ctime'			=>	time(),
                'user_phone'    =>  $user_phone,
		));

		$res = M('ShopInfoUser')->add();

		if ($res === false) {
			return $this->getDbError();
		}

		// 修改这张表种的报名人数的数量
		$count = M('ShopInfoUser')->where('shop_info_id = %d', $info_id)->count();
		if ($count === false) {
			return $this->getDbError();
		}

		$res = $this->where('id = %d', $info_id)->setField('number', $count);
		if ($res === false) {
			return $this->getDbError();
		}

        // 成功报名了，发送短信给那位幸运的商家
        $this->sendSmsNotification($info_id);

        // 发送消息给管理员
        $msg = '有用户报名了商家课程，请及时查看。登录17yueke.cn。【17约课】';
        $this->sendEmailToManager($msg);

        // 没有手机号码的用户更新手机号码
        if (!session('?user.phone')) {
            session('user.phone', $user_phone);
            M('User')->where('id = %d', $user_id)->setField('phone', $user_phone);
        }

		return true;
	}

//-------------------------------------------------游客-----↓--------------------------------------------
    public function visitorEnroll($info_id, $visitorid) {

        // 先看看有没有报名-----visitor_id
        $count = M('ShopInfoUser')->where('shop_info_id = %d and visitor_id = %d', $info_id, $visitorid)->count();
        if ($count === false) {
            return $this->getDbError();
        }
		// 先看看有没有报名----visitor_check_id
		$count1 = M('ShopInfoUser')->where('shop_info_id = %d and visitor_check_id = %d', $info_id, $visitorid)
									->count();
		if ($count1 === false) {
			return $this->getDbError();
		}

		if ($count > 0 ||$count1>0) {
			return '您已经报名了';
		}

		// 增加报名的人
		M('ShopInfoUser')->data(array(
				'shop_info_id'	=>	$info_id,
				'ctime'			=>	time(),
                'visitor_id'    =>  $visitorid,
		));
		$res = M('ShopInfoUser')->add();
		if ($res === false) {
			return $this->getDbError();
		}

		// 修改这课程的报名人数的数量
		$count = M('ShopInfoUser')->where('shop_info_id = %d', $info_id)->count();
		if ($count === false) {
			return $this->getDbError();
		}
		$res = $this->where('id = %d', $info_id)->setField('number', $count);
		if ($res === false) {
			return $this->getDbError();
		}


        // 发送消息给管理员
//         $msg = '有用户报名了商家课程，请及时查看。登录17yueke.cn。【17约课】';
//         $this->sendEmailToManager($msg);

        // 没有手机号码的用户更新手机号码
//         if (!session('?user.phone')) {
//             session('user.phone', $user_phone);
//             M('User')->where('id = %d', $user_id)->setField('phone', $user_phone);
//         }

		return true;
    }


    /**
     * 更新两个字段值，用户的id跟游客的id关联
     * @param unknown $uid
     * @param number $visitorid
     * @return boolean|Ambigous <boolean, unknown>
     */
    public function updataVisitorToUserId($uid,$visitorid=0){


        if ($uid==0||$visitorid==0){
            return false;
        }
        $visitShopGid=M('ShopInfoUser')->field('shop_info_id')->where('visitor_id=%d',$visitorid)->select();
        if (!$visitShopGid){
            return false;
        }

        $userShopGid=M('ShopInfoUser')->field('shop_info_id')->where('user_id=%d',$uid)->select();
        if ($userShopGid){
            $userGidList=array_column($userShopGid,'shop_info_id');
            $visitGidList=array_column($visitShopGid,'shop_info_id');
            $comuinter=array_diff($visitGidList,$userGidList);
            $delList=array_intersect($visitGidList,$userGidList);
            $comPode=implode(',',$comuinter);//差集
            $delListPode=implode(',',$delList);//交集
        }else {
            $visitGidList=array_column($visitShopGid,'shop_info_id');
            $comPode=implode(',',$visitGidList);//差集
        }

        if (!empty($delListPode)){
            $map['shop_info_id'] = array('IN',$delListPode);
            $map['user_id'] = array('EQ',0);
            $map['visitor_id'] = array('EQ',$visitorid);
            $delVisit=M('ShopInfoUser')->where($map)->delete();
        }
        if (!empty($comPode)){
            $data=array(
                'user_id'     =>$uid,
                'visitor_id'=>0,
                'visitor_check_id'=>$visitorid,
            );
            if (session('?user.phone')){
                $data['user_phone'] = session('user.phone');
            }
            $where['shop_info_id'] = array('IN',$comPode);
            $map['user_id'] = array('EQ',0);
            $where['visitor_id'] = array('EQ',$visitorid);
            $saveVisit=M('ShopInfoUser')->where($where)->setField($data);
        }

        if (!$delVisit||!$saveVisit){
            return $delVisit.'|'.$saveVisit;
        }
        return true;
    }





//-------------------------------------------------游客-----------↑--------------------------------------------


	/**
	 * 收藏商家课程信息
	 * @param unknown $info_id
	 * @param unknown $user_id
	 */
	public function star($info_id, $user_id) {
		// 先看看有没有收藏
		$id = M('UserCollect')->where('shopid = %d and uid = %d', $info_id, $user_id)
                                ->getField('id');
		if ($id === false) {
			return $this->getDbError();
		}

        // 用户已经收藏了
        if ($id) {
            M('UserCollect')->where('id = %d', $id)->delete();
            return '收藏已取消';
        }

		// 收藏
		M('UserCollect')->data(array(
				'uid'		=>	$user_id,
				'shopid'	=>	$info_id,
				'ctime'		=>	current_datetime(),
		));

		if (!M('UserCollect')->add()) {
			return $this->getDbError();
		}

		return true;
	}

    /**
     * 统计某一个商贾被报名的次数
     */
    public function countEnroll($shopkeeperId) {
        return $this->alias('si')
                    ->join('__SHOP_INFO_USER__ siu on si.id = siu.shop_info_id')
                    ->where('si.sid = %d', $shopkeeperId)
                    ->count();
    }

	/**
	 * 获取某一个商家的报名信息
	 * @param unknown $shopkeeperId
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 */
	public function getEnroll($shopkeeperId, $nowPage, $perPage) {
		$this->field(array(
				'si.title',
				'u.id uid',
				'u.firstname',
				'u.lastname',
				'u.avatar',
				'u.profession',
				'u.telstatus',
				'siu.ctime',
                'siu.user_phone phone',
				'uv.vstatus',
		));

		$resArr =  $this->alias('si')
						->join('__SHOP_INFO_USER__ siu on si.id = siu.shop_info_id')
						->join('__USER__ u on siu.user_id = u.id')
						->join('LEFT JOIN __USER_V__ uv on uv.uid = siu.user_id')
						->where('si.sid = %d', $shopkeeperId)
						->page($nowPage, $perPage)
						->order('siu.id desc')
						->select();

		if (!$resArr) {
			return $resArr;
		}

		// 更新红点时间
		if ($nowPage == 1) {
			D("Shopkeeper")->updateRedot($shopkeeperId, "enroll", $resArr[0]['ctime']);
		}

		return $resArr;
	}

	/**
	 * 根据商家的ID获取评论数量
	 */
    public function countComment($shopkeeperId) {
        return $this->alias('si')
                    ->join('__SHOP_COMMENT__ sc on si.id = sc.iid')
                    ->where('si.sid = %d', $shopkeeperId)
                    ->count();
    }

	/**
	 * 根据商家的ID获取评论
	 * @param unknown $shopkeeperId
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 * @return Ambigous <string, unknown>
	 */
	public function getComment($shopkeeperId, $nowPage, $perPage) {
		$this->field([
				'si.id info_id',
				'si.title',
				'si.environ',

				'sc.uid',
				'sc.sid',
				'sc.content',
				'sc.ctime',

				'u.avatar u_avatar',
				'u.firstname u_firstname',
				'u.lastname u_lastname',

				'sd.avatar s_avatar',
				'sd.nickname s_nickname',
				's.company_name s_company_name',
		]);

		$resArr = $this->alias('si')
						->join('__SHOP_COMMENT__ sc on si.id = sc.iid')
						->join('LEFT JOIN __USER__ u on sc.uid = u.id')
						->join('LEFT JOIN __SHOPKEEPER__ s on sc.sid = s.id')
						->join('LEFT JOIN __SHOPKEEPER_DETAIL__ sd on sc.sid = sd.sid')
						->where('si.sid = %d', $shopkeeperId)
						->order('sc.id desc')
						->page($nowPage, $perPage)
						->select();

		if (!$resArr) {
			return $resArr;
		}

		// 真实数据
		foreach ($resArr as $key => $value) {
			// 这是用户评论的
			if ($value['uid']) {

				$resArr[$key]['avatar'] = $value['u_avatar'];
				$resArr[$key]['nickname'] = $value['u_firstname'] . $value['u_lastname'];

				// 这是商家评论的
			} else if ($value['sid']) {

				$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['s_avatar']);

				if ($value['s_nickname']) {
					$resArr[$key]['nickname'] = $value['s_nickname'];
				} else {
					$resArr[$key]['nickname'] = $value['company_name'];
				}

			}
		}

		// 更新红点时间
		if ($nowPage == 1) {
			D("Shopkeeper")->updateRedot($shopkeeperId, "comment", $resArr[0]['ctime']);
		}

		return $resArr;
	}

    /**
     * 统计搜索页数用
     */
    public function countShopForSearch($map) {
        return $this->alias('si')
                    ->join('LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id')
                    ->where($map)
                    ->count();
    }

	/**
	 * 搜索页面获取发布课程的信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @param mixed $info_id 如果为null， 表示不用， 如果为一个值，表示where id = x, 如果为数组， 表示where id in (x, x, x)
	 * @return string
	 */
	public function getShop($pageOffset = 0, $perPage = 4, $sort = 'desc',$greet,$map,$parentCateId) {
	    // 过滤
	    $pageOffset = intval($pageOffset);
	    $perPage = intval($perPage);
	    if ($sort != 'asc' && $sort != 'desc') {
	        $sort = 'desc';
	    }
	    // 要查询的字段
	    $array = array(
	        'si.id',
	        'si.sid',
	        'si.cateid',
	        'si.title',
	        'si.areaid',
	        'si.price',
	        'si.mode',
	        'si.tags',
	        'si.ctime',
	        'si.view',
	        'si.area_detail',
            'si.preferent',
	        'skd.avatar',
	        'si.environ',
	        'cate.catename',
	        's.company_name',
	        's.status',
	        's.login_phone',
	        'skd.nickname',
	        // 				'count(siu.id) `user_count`',
	    // 				'count(sc.id) `comment_count`',
	        'si.number user_count',
	        'si.comment_count',
	    );





        //分类越细的越排前 
	    $order = 'si.cateid ' . $sort;
	    // 定义当前数据表的别名
	    $alias = 'si';
	    // 联合查询
	    $join  = array(
	        'LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id',
	        'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
	        'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
	        // 				'LEFT JOIN __SHOPKEEPER_ENVIRON__ se on skd.environ_id = se.id',
	    // 				'LEFT JOIN __SHOP_INFO_USER__ siu on si.id = siu.shop_info_id',
	    // 				'LEFT JOIN __SHOP_COMMENT__ sc on si.id = sc.iid and sc.parent_id = 0',
	    );


     //所有的搜索条件 
	 if ($map) {
	     $where=$map;
	 }
     //是否最受欢迎 -报名数为准-
	 if($greet){
	     $order = 'si.number  '.$sort;
	 }
	    $this   ->alias($alias)
        	    ->field($array)
        	    ->join($join)
        	    ->where($where)
        	    ->order($order)
        	    ->limit($pageOffset, $perPage);
	    // 			->group('si.id');

	    $res = $this->select();

	    if ($res === null) {
	        return null;
	    }

	    // 获取tags
	    $res = $this->grouptags($res);

	    // 获取结果数组的所以地区ID
	    $areaids = array();
	    foreach ($res as $key => $row) {
	        $areaids[] = $row['areaid'];
	    }
	    // 获取两级地区名称
	    $areanames = D('Common/Area')->getTwoLevelNameInArr($areaids);
	    // 将地区信息放进结果数组
	    $tmpAreanames = array();
	    foreach ($areanames as $row) {
	        $tmpAreanames[$row["id"]][] = $row["parent_arename"];
	        $tmpAreanames[$row["id"]][] = $row["this_arename"];
	    }

	    //重新排序，把综合类的排在最下面
	    $parent_array=array();
	    $chirdren_array=array();

	    foreach ($res as $key => $row) {
	        $res[$key]['areanames'][] = $tmpAreanames[$row["areaid"]];
	        $res[$key]['ctime'] = transDate($row['ctime']);
	        $Mode=C('mode');
	        foreach ($Mode as $k => $v){
	            if($row['mode']==$k){
	                $res[$key]['mode']= $v;
	            }
	        }

	        //重新排序，把综合类的排在最下面
	        if ($row['cateid']==$parentCateId){
	            $parent_array[]=$row;
	        }else {
	            $chirdren_array[]=$row;
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
	 * @param array $where多条件查询时
	 * @return unknown
	 */
	public function cateStatisticsCountSearch($map=array(),$keyword=''){
	    $alias = 'si';
	    $this  ->  alias($alias);
	    if ($keyword){
	        $join  =   array(
	            'LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id',
	            'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
	            'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
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
	
	//-------------------------------------------------下面的方法还有用，但现在测试上面方法是否有错---------
	/**
	 * 返回筛选页面所选择类型的总记录数
	 * @param number $uid
	 * @param array $where多条件查询时
	 * @return unknown
	 */
	public function cateStatisticsCount($cateStatistics='',$where=array()){
	    if (!empty($where)){
    	    $alias = 'si';
    	    $join  =   array(
    	        'LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id',
    	        'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
    	        'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
    	    );
	    }
	    $where['cateid']=array('IN',$cateStatistics);
	    $count = $this ->  alias($alias)
	                   ->  join($join)
	                   ->  where($where)
	                   ->  count();
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
	public function getShopSearch($keywords){

	    // 要查询的字段
	    $array = array(
	        'si.id',
	        'si.sid',
	        'si.cateid',
	        'si.title',
	        'si.areaid',
	        'si.price',
	        'si.mode',
	        'si.tags',
	        'si.ctime',
	        'si.view',
	        'si.area_detail',
            'si.preferent',
	        'skd.avatar',
	        'si.environ',
	        'cate.catename',
	        's.company_name',
	        's.status',
	        's.login_phone',
	        'skd.nickname',
	        // 				'count(siu.id) `user_count`',
	    // 				'count(sc.id) `comment_count`',
	        'si.number user_count',
	        'si.comment_count',
	    );
	    // 定义当前数据表的别名
	    $alias = 'si';
	    // 联合查询
	    $join  = array(
	        'LEFT JOIN __SHOPKEEPER__ s on si.sid = s.id',
	        'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
	        'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
	        // 				'LEFT JOIN __SHOPKEEPER_ENVIRON__ se on skd.environ_id = se.id',
	    // 				'LEFT JOIN __SHOP_INFO_USER__ siu on si.id = siu.shop_info_id',
	    // 				'LEFT JOIN __SHOP_COMMENT__ sc on si.id = sc.iid and sc.parent_id = 0',
	    );


	    $this->alias($alias)
	    ->field($array)
	    ->join($join)//"skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or company_name like  '%$keywords%'  or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or company_name like  '%$keywords'"
	    ->where("skd.nickname like '%$keywords%' or cate.catename like '%$keywords%' or company_name like  '%$keywords%'  or skd.nickname  like '$keywords%' or cate.catename like '$keywords%' or company_name like  '$keywords%'  or skd.nickname  like '%$keywords' or cate.catename like '%$keywords' or company_name like  '%$keywords'")
	    ->limit(0, 20);
	    // 			->group('si.id');

	    $res = $this->select();

	    if ($res === null) {
	        return null;
	    }

	    // 获取tags
	    $res = $this->grouptags($res);

	    // 获取结果数组的所以地区ID
	    $areaids = array();
	    foreach ($res as $key => $row) {
	        $areaids[] = $row['areaid'];
	    }
	    // 获取两级地区名称
	    $areanames = D('Common/Area')->getTwoLevelNameInArr($areaids);
	    // 将地区信息放进结果数组
	    $tmpAreanames = array();
	    foreach ($areanames as $row) {
	        $tmpAreanames[$row["id"]][] = $row["parent_arename"];
	        $tmpAreanames[$row["id"]][] = $row["this_arename"];
	    }
	    foreach ($res as $key => $row) {
	        $res[$key]['areaname'][] = $tmpAreanames[$row["areaid"]];
	        $res[$key]['ctime'] = transDate($row['ctime']);
	        $Mode=C('mode');
	        foreach ($Mode as $k => $v){
	            if($row['mode']==$k){
	                $res[$key]['mode']= $v;
	            }
	        }
	    }

	    return $res;
	}

	/**
	 * 获取报名更新的数字
	 * @param unknown $shopkeeperId
	 * @param unknown $time
	 * @return string|unknown
	 */
	public function numEnrollUpdate($shopkeeperId) {
		// 获取最后查看评论的时间
		$time = M('ShopRedot')->where('sid = %d and type = "enroll"', $shopkeeperId)->getField('time');
		if ($time === false) {
			return $this->getDbError();
		}
		if ($time === null) {
			$time = 0;
		}

		$num = $this->alias('si')
					->join('__SHOP_INFO_USER__ siu on siu.shop_info_id = si.id')
					->where('si.sid = %d and siu.ctime > %d', $shopkeeperId, $time)
					->count();

		if ($num === false) {
			return $this->getDbError();
		}

		return intval($num);
	}

	/**
	 * 获取非常简单的商家课程信息
	 * @param unknown $shopId
	 * @param string $nowPage 如果为null的话，就不分页了
	 * @param number $perPage
	 * @return \Think\mixed
	 */
	function getVerySimpleInfo($shopId, $nowPage = null, $perPage = 5) {
		$this->field('id, title');
		$this->where('sid = %d', $shopId);

		if ($nowPage === null) {
			$this->page($nowPage, $perPage);
		}

		return $this->select();
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
	 * 删除课程信息，这是一个非常大的动作
	 */
	public function deleteInfo($shopkeeperId, $infoId) {
		$this->startTrans();

		$res = $this->where('id = %d and sid = %d', $infoId, $shopkeeperId)->delete();

		// 课程信息不在
		if (!$res) {
			$this->rollback();
			return '课程信息不存在或已经被删除';
		}

		// 删除和课程
		M('ShopInfoUser')->where('shop_info_id = %d', $infoId)->delete();
		M('ShopComment')->where('iid = %d', $infoId)->delete();

		// 看成不成功
		if ($this->getDbError()) {
			$this->rollback();
			return $this->getDbError();
		}

		$this->commit();
		return true;
	}

    /**
     * 统计某个商家一天之内发送了多少的课程
     */
    public function countShopInfoToday($shopkeeperId) {
        // 获取一天的时间始终
        list($beginTime, $lastTime) = get_day_time_scope();
        $beginTime = date('Y-m-d H:i:s', $beginTime);
        $lastTime = date('Y-m-d H:i:s', $lastTime);

        $count = $this->where('sid = %d and ctime >= "%s" and ctime < "%s"', $shopkeeperId, $beginTime, $lastTime)
                      ->count();

        if ($count === false) {
            return $this->getDbError();
        }
        return intval($count);
    }

    /**
     * 发送验证码短信
     */
    public function sendSmsVerify($phone) {
        if (!$phone) {
            return '手机号码不能为空';
        }

        if (!preg_match('/^1\d{10}$/', $phone)) {
            return '手机号码不正确';
        }

        return $this->createSmsToken($phone);
    }

    /**
     * 检验用户的手机号码是不是真实的
     */
    protected function checkRealPhone($phone, $smsverifyCode = null) {
        if (session('user.phone') && $phone == session('user.phone')) {
            return true;
        }

        if (!session("shop_sms.sms_token")) {
            return false;
        }

        if (session("shop_sms.sms_token") != $smsverifyCode) {
            return false;
        }

        return true;
    }


    //=======================================================

    /*
     * 　获取对应的跟约人的数据信息
     */
    public function getAllUser($id){
          $SHOPINFOUSER=D('ShopInfoUser');
        $User=D('User');
        $InfoUser= $SHOPINFOUSER->where("shop_info_id='$id'")->field('user_id')->select();
        $arr=array();
            foreach ($InfoUser as $key1 =>$value1){
                array_push($arr,$value1['user_id']);
            }
            $s=implode(',',$arr);
            $map['id']  = array('in',$s);
            $array= $User
            ->where($map)
            ->field('firstname,lastname,phone,email')->select();
        return $array;
    }

    /**
     * 对用户数据的对应课程推送信息进行分页
     * @param unknown $curPage
     * @param unknown $perPage
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function kcTnfoDataPage($curPage,$perPage,$sid){
        $count= $this->where("sid=%d",$sid)->count();

        // 查询总记录数
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray=$Page->getCounts();
        return  $pageArray;
    }
        /**
         * 获取某一个商家发布的课程详细
         */
    public function getKcInfo($pageOffset = 0, $perPage = 3, $shopid=0) {
        // 过滤

        // 要查询的字段
        $array = array(
            'si.id',
            'si.sid',
            'si.cateid',
            'si.title',
            'si.areaid',
            'si.price',
            'si.mode',
            'si.tags',
            'si.content',
           // 'si.ctime',
            //'si.view',
            'si.area_detail',
            'si.phone_tel',
             's.company_name',
          //  'skd.avatar',
            'si.number user_count',
            'si.comment_count',
            'sd.nickname',
          // 'siu.user_id'
          'cate.catename'
        );


        // 定义当前数据表的别名
        $alias = 'si';
        // 联合查询
        $join  = array(
            'right  JOIN __SHOPKEEPER__ s on si.sid = s.id',
            'right join __SHOPKEEPER_DETAIL__ sd on sd.sid =s.id',
            'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
           // 'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
            // 				'LEFT JOIN __SHOPKEEPER_ENVIRON__ se on skd.environ_id = se.id',
        			//'LEFT JOIN __SHOP_INFO_USER__ siu on si.id = siu.shop_info_id',
        // 				'LEFT JOIN __SHOP_COMMENT__ sc on si.id = sc.iid and sc.parent_id = 0',
        );
        $order = 'si.id desc' ;
        $where=("si.sid='$shopid'");
       $res= $this->alias($alias)
        ->field($array)
        ->join($join)
        ->order($order)
        ->where($where)
        ->limit($pageOffset, $perPage)
         ->select();
       foreach ($res as $key=>$value){
           $id=intval($value['id']);

           $res[$key]['user']=$this->getAllUser($id);

       }
       $res=$this->grouptags($res);
       return $res;
    }

    /**
     * 根据课程信息的id寻找报名者的信息
     */
    public function getEnrollerInfo($shopInfoId, $pageOffset, $perPage) {
        $fields = [
            'siu.ctime enroll_time',
            'siu.user_phone',
            'u.*',
        ];
        $resArr = M('ShopInfoUser')->alias('siu')
                                   ->field($fields)
                                   ->join('__USER__ u on u.id = siu.user_id')
                                   ->order('id desc')
                                   ->limit($pageOffset, $perPage)
                                   ->where('siu.shop_info_id = %d', $shopInfoId)
                                   ->select();

        foreach ($resArr as $key => $row) {
            //  处理时间格式
            $resArr[$key]['enroll_time'] = transDate($row['enroll_time']);
            // 处理性别
            $sexMap = ['保密', '男', '女'];
            $resArr[$key]['sex'] = $sexMap[$row['sex']];
        }

        return $resArr;
    }

// ===============================================================================================================
// ============================================= protected functions =============================================
// ===============================================================================================================

    /**
     * 根据课程信息的id发送短信给商家
     */
    protected function sendSmsNotification($infoId) {
        $row = $this->field(['login_email', 'login_phone'])
                      ->alias('si')
                      ->join('__SHOPKEEPER__ s on s.id = si.sid')
                      ->where('si.id = %d', $infoId)
                      ->find();

        if ($row === false) {
            return $this->getDbError();
        }

        if ($row === null) {
            return '商家您的联系方式不存在';
        }

        // 发送邮件或者短信
        $msg = '您好！您在17约课上发布的课程，已经有用户报名您的课程，请及时查看。登录17yueke.cn/s/'.$infoId.'。【17约课】';

        if ($row['login_phone']) {
            // 发送短信
            require_once(realpath('Api/sms/sms_send.php'));
            sendnote($row['login_phone'], urlencode(iconv('utf-8', 'gbk', $msg)));

        } else if ($row['login_email']) {
            // 发送邮件
            sendMail($row['login_email'], $msg, '17约课：已经有用户报名您的课程了');

        } else {
            return '商家您的联系方式不存在';
        }

    }
    
    /**
     * author :aupl
     * 根据搜索关键字来获取areaid和cateid
     * */
    public function getAreaidAndCateid($keyword){
        $condition["title"] = array('like', '%'.$keyword.'%');
        return $this->field('areaId,cateId')->where($condition)->select();
    }
    
    
    /**
     * author :aupl
     * 根据cateid获取课程信息
     * 由于shopkeeper中搜索功能，可能不用这个函数
     * param cateid 类别id
     * param order 排序
     * */
    public function getCourseInfo($pageOffset = 0, $perPage = 3, $cateid, $order = 'Id'){
        // 要查询的字段
        $array = array(
            'si.id',
            'si.cateid',
            'si.title',
            'si.areaid',
            'si.price',
            'si.mode',
            'si.tags',
            'si.content',
            'si.area_detail',
            'si.phone_tel',
            'skd.avatar',
            'si.number user_count',
            'si.comment_count',
            'sd.nickname',
            'cate.catename'
        );
        // 定义当前数据表的别名
        $alias = 'si';
        // 联合查询
        $join  = array(
            'right  JOIN __SHOPKEEPER__ s on si.sid = s.id',
            'right join __SHOPKEEPER_DETAIL__ sd on sd.sid =s.id',
            'LEFT JOIN __CATEGORY__ cate on si.cateid = cate.id',
            'LEFT JOIN __SHOPKEEPER_DETAIL__ skd on si.sid = skd.sid',
        );
        $order = 'si.'.$order.' desc' ;
        $where=("si.cateid='$cateid'");
        $res= $this->alias($alias)
        ->field($array)
        ->join($join)
        ->order($order)
        ->where($where)
        ->limit($pageOffset, $perPage)
        ->select();
        return $res;
    }

}
