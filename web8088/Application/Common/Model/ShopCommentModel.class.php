<?php

namespace Common\Model;
use Common\Model\CommonModel;

class ShopCommentModel extends CommonModel {

	/**
	 * 分页展示条目数量
	 */
	public $rowlist = 10;

	/**
	 * 验证规则
	 */
	protected $_validate = array(
			array('content', 'require', '评论内容不能为空！', 1),
			array('content', '/^.{1,255}$/u', '评论内容过长！', 1, 'regex'),

			array('iid', 'require', '组团信息ID不能为空！', 1),
			array('iid', '/^\d+$/u', '组团信息ID不是数字！', 1, 'regex'),

			array('parent_id', '/^\d+$/u', '课程信息父级ID不是数字！', 1, 'regex'),
	);

	/**
	 * 处理添加评论请求
	 * @return string|mixed
	 */
	public function handleAdd() {
		// 获取上级评论ID
		$data['parent_id'] = I('post.parent_id', 0, 'intval');
		// 看看是用户评论还是商家评论
		if (session("?user")) {
			$data["uid"] = session("user.id");
		} else if (session("?shopkeeper")) {
			$data["sid"] = session("shopkeeper.id");
		} else {
			return "没有登陆呢！";
		}
		// 自动验证
		if (!$this->create()){
			return $this->getError();
		}
		// 然后拼好数据
		$data['content'] = $this->content;
		$data['iid'] = $this->iid;
		$data['ctime'] = time();
		// 插进数据库
		$bool = $this->data($data)->add();
		if (!$bool) {
			return $this->getDbError();
		}
		// 最后成功了
		return true;
	}

	/**
	 * 分页获取评论
	 * @return array()
	 */
	public function listComment() {
		// 获取评论的ID
		$comment_id = I("get.commentid", 0, 'intval');
		$page = I("get.page", 0, 'intval');
		// 获取相关的总数
		$count = $this->where("parent_id = %d", $comment_id)->count();
		// 获取数据
		$list = $this->where("parent_id = %d", $comment_id)
					->page($page, $this->rowlist)
					->select();
		if ($list === null) {
			return array();
		}
		return $list;
	}

	/**
	 * 列出用户或商家的评论
	 * @param unknown $info_id
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 * @return \Think\mixed
	 */
	public function listUserOrShopComment($info_id, $nowPage, $perPage, $userId, $shopkeeperId) {
		$this->field(array(
                'sc.id',
				'sc.uid',
				'sc.sid',
				'sc.content',
				'sc.ctime',
                'sc.parent_nickname',
		        'sc.visitor_id',

				'u.firstname u_firstname',
				'u.lastname u_lastname',
				'u.avatar u_avatar',

				'sd.nickname s_nickname',
				'sd.avatar s_avatar',
		    
		        'vi.name as visitname',
		));
		$resArr = $this->alias('sc')
						->join('LEFT JOIN __USER__ u on u.id = sc.uid')
						->join('LEFT JOIN __SHOPKEEPER_DETAIL__ sd on sd.sid = sc.sid')
						->join('LEFT JOIN __VISITOR__ vi on vi.id = sc.visitor_id')
						->where('sc.iid = %d', $info_id)
						->page($nowPage, $perPage)
						->order('sc.id desc')
						->select();

		if ($resArr === null) {
			return $resArr;
		}

        // 转化成数字好判断
        $userId = intval($userId);
        $shopkeeperId = intval($shopkeeperId);

        //游客头像
        $visit_def_avatar = C('visitor_config')['avatar'];
        
		// 很厉害地遍历查询数据库，看看是用户还是商家发表的评论
		// <b>后来良心发现了（其实是找到了更好的方法），决定不这样做了</b>
		foreach ($resArr as $key => $value) {
// 			$resArr[$key] = array_merge($value, $this->getCommenterInfo($value['uid'], $value['sid']));

			// 判断是谁评论的
			if ($value['uid']) {
				// 这是用户评论的
				$resArr[$key]['nickname'] = $value['u_firstname'] . $value['u_lastname'];
				$resArr[$key]['avatar'] = $value['u_avatar'];

			} else if ($value['sid']) {
				// 这是商家评论的
				$resArr[$key]['nickname'] = $value['s_nickname'];
				$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['s_avatar']);

			}elseif ($value['visitor_id']){
				// 这是商家评论的
				$resArr[$key]['nickname'] = $value['visitname'];
				$resArr[$key]['avatar'] = $visit_def_avatar;
				$resArr[$key]['vid'] = $value['visitor_id'];
			}

            // 判断是不是我评论的
            $resArr[$key]['isMe'] = ($userId == $value['uid'] && $shopkeeperId == $value['sid']);
		}

		return $resArr;
	}

	/**
	 * 根据课程信息的ID统计评论数量
	 * @param unknown $info_id
	 */
	public function countCommentByInfoId($info_id) {
		return $this->where('iid = %d', $info_id)->count();
	}

	/**
	 * 发布一条评论
	 * @param unknown $content
	 * @param unknown $iid
	 * @param unknown $uid
	 * @param string $sid
	 * @return string|\Think\mixed|boolean
	 */
	public function postComment($content, $iid, $uid, $sid, $parent_id) {
        // 看看有没有登录
        $uid = intval($uid);
        $sid = intval($sid);
		if (!$uid && !$sid) {
			return '请先登录';
		}

        // 这里是限制评论频率和一天内的评论数量
        $oldPostTime = session('shopkeeper.post_comment_time');
        $nowTime = time();
        if ($nowTime - $oldPostTime < 5) {
            return '5秒之内不能再次评论';
        }
        session('shopkeeper.post_comment_time', $nowTime);

        list($todayBeginTime, $todayEndTime) = get_day_time_scope();
        $this->where(
            'uid = %d and sid = %d and ctime >= %d and ctime <= %d',
            $uid, $sid, $todayBeginTime, $todayEndTime
        );
        $count = $this->count();

        if ($count === false || $count === null) {
            return '评论系统出错，请重试';
        }
        if ($count > 100) {
            return '您今天已达到评论上限100条，请明天再评论';
        }

		// 过滤输入
		$input = array(
				'content'	=>	$content,
				'iid'		=>	$iid,
                'parent_id' =>  $parent_id,
		);
		if (!$this->create($input)) {
			return $this->getError();
        }

        // 组装数据
		$this->ctime = time();
        $this->uid = $uid;
        $this->sid = $sid;

		if ($this->add() === false) {
			return $this->getDbError();
		}

        // 获取这条评论的id
        $lastId = $this->getLastInsID();

        // 判断是不是子评论
        if ($parent_id) {
            $row = $this->field(['uid', 'sid'])->where('id = %d', $parent_id)->find();

            // 这里是数据库错误或者找不到
            if (!$row) {
                return '评论失败';
            }
            $value = $this->getCommenterInfo($row['uid'], $row['sid']);

            // 组装数据
            $this->where('id = %d', $lastId)->setField('parent_nickname', $value['nickname']);
        }

		// 这里课程信息那里的修改评论总数
		$count = $this->where('iid = %d', $iid)->count();
		M('ShopInfo')->where('id = %d', $iid)->setField('comment_count', $count);

        return [
            'lastId'            =>  intval($lastId),
            'parent_nickname'   =>  $value['nickname'],
        ];
	}

	/**
	 * 获取最后一个人的头像和昵称
	 * @param unknown $uid
	 * @param string $sid
	 */
	public function getCommenterInfo($uid, $sid = null) {

		if ($uid) {

				$user =	M('User')->field(array('firstname', 'lastname', 'avatar'))
								->where('id = %d', $uid)
								->find();
				$value['nickname'] = $user['firstname'] . $user['lastname'];
				$value['avatar'] = $user['avatar'];

		} else if ($sid) {

				$shopkeeper = M('ShopkeeperDetail')->field(array('nickname', 'avatar'))
													->where('sid = %d', $sid)
													->find();
				$value['nickname'] = $shopkeeper['nickname'];
				$value['avatar'] = A('Home/Shopkeeper')->getAvatar($shopkeeper['avatar']);
		}

		return $value;
	}

	/**
	 * 根据User的Id列出他的评论信息
	 * @param unknown $user_id
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 */
	public function listByUserId($user_id, $nowPage, $perPage) {
		$this->field(array(
				'sc.iid info_id',
				'sc.content',
				'sc.ctime',
				'si.environ',
				'si.title',
		));

		return $this->alias('sc')
					->join('left join __SHOP_INFO__ si on sc.iid = si.id')
					->where('sc.uid = %d', $user_id)
					->page($nowPage, $perPage)
					->select();
	}

	/**
	 * 获取评论更新的数字
	 * @param unknown $shopkeeperId
	 * @param unknown $time
	 * @return string|unknown
	 */
	public function numUpdate($shopkeeperId) {
		// 获取最后查看评论的时间
		$time = M('ShopRedot')->where('sid = %d and type = "comment"', $shopkeeperId)->getField('time');
		if ($time === false) {
			return $this->getDbError();
		}
		if ($time === null) {
			$time = 0;
		}

		$num = $this->alias('sc')
					->join('__SHOP_INFO__ si on si.id = sc.iid')
					->where('si.sid = %d and sc.ctime > "%d"', $shopkeeperId, $time)
					->count();

		if ($num === false) {
			return $this->getDbError();
		}

		return intval($num);
	}

    /**
     * 根据ID删除评论
     * @param $commentId int 评论的id
     */
    public function deleteById($commentId, $userId, $shopkeeperId) {
        // 评论的id不能为空或0
        if (!$commentId) {
            return '评论不存在(0)';
        }

        // 获取那条评论
        $comment = $this->field(['uid', 'sid'])
                        ->where('id = %d', $commentId)
                        ->find();

        if ($comment === false) {
            return $this->getDbError();
        }

        if ($comment === null) {
            return '评论不存在';
        }

        // 判断这个评论是不是本人的，注意$userId和$shopkeeperId其中一个必为0
        if ($comment['uid'] != intval($userId) || $comment['sid'] != intval($shopkeeperId)) {
            return '非法操作';
        }

        // 删除这条评论信息
        $this->where('id = %d', $commentId)->delete();

        return true;
    }

	/**
	 * 后台列出用户或商家的评论
	 * @param unknown $info_id
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 * @return \Think\mixed
	 */
	public function listCommenterInfo($info_id, $nowPage, $perPage) {
		$this->field(array(
                'sc.id',
				'sc.uid',
				'sc.sid',
				'sc.content',
				'sc.ctime',
                'sc.parent_nickname',

				'u.firstname u_firstname',
				'u.lastname u_lastname',
				'u.avatar u_avatar',
                'u.email',
                'u.phone',

                's.login_email',
                's.login_phone',
				'sd.nickname s_nickname',
				'sd.avatar s_avatar',
		));
		$resArr = $this->alias('sc')
						->join('LEFT JOIN __USER__ u on u.id = sc.uid')
						->join('LEFT JOIN __SHOPKEEPER_DETAIL__ sd on sd.sid = sc.sid')
                        ->join('LEFT JOIN __SHOPKEEPER__ s on s.id = sd.sid')
						->where('sc.iid = %d', $info_id)
						->page($nowPage, $perPage)
						->order('sc.id desc')
						->select();

		if ($resArr === null) {
			return $resArr;
		}

		foreach ($resArr as $key => $value) {

			// 判断是谁评论的
			if ($value['uid']) {
				// 这是用户评论的
				$resArr[$key]['nickname'] = $value['u_firstname'] . $value['u_lastname'];
				$resArr[$key]['avatar'] = $value['u_avatar'];
                $resArr[$key]['email'] = $value['email'];
                $resArr[$key]['phone'] = $value['phone'];

			} else if ($value['sid']) {
				// 这是商家评论的
				$resArr[$key]['nickname'] = $value['s_nickname'];
				$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['s_avatar']);
                $resArr[$key]['email'] = $value['login_email'];
                $resArr[$key]['phone'] = $value['login_phone'];

			}

		}

		return $resArr;
	}

//---------------------------------------------以下为游客------------------------------------------------------------------------
	/**
	 * 游客对于商家课程的评论
	 * @param unknown $content
	 * @param unknown $iid
	 * @param unknown $uid
	 * @param unknown $sid
	 * @param unknown $parent_id
	 * @param unknown $visitorId
	 * @return string|multitype:number unknown
	 */
	public function addShopComByVisitor($content,$iid,$uid,$sid,$parent_id,$visitorId){

	    // 这里是限制评论频率和一天内的评论数量
	    $oldPostTime = session('s_com_answer_time');
	    $nowTime = time();
	    if ($nowTime - $oldPostTime < 5) {
	        return '5秒之内不能再次评论';
	    }
	    session('s_com_answer_time', $nowTime);
	    $count = $this->where('uid=%d and sid=%d and iid=%d and visitor_id=%d',0,0,iid,$visitorId)->count();
	    if ($count>200){
	        return '回复评论超过200条，该约课禁止回复评论';
	    }

	    // 过滤输入
	    $input = array(
	        'content'	=>	$content,
	        'iid'		=>	$iid,
	        'parent_id' =>  $parent_id,
	        'visitor_id' => $visitorId,
	    );
	    if (!$this->create($input)) {
	        return $this->getError();
	    }
	    
	    // 组装数据
	    $this->ctime = time();
	    $this->uid = 0;
	    $this->sid = 0;
	    
	    if ($this->add() === false) {
	        return $this->getDbError();
	    }

	    // 获取这条评论的id
	    $lastId = $this->getLastInsID();
	    
	    // 判断是不是子评论
	    if ($parent_id) {
	        $row = $this->field(['uid', 'sid','visitor_id'])->where('id = %d', $parent_id)->find();
	    
	        // 这里是数据库错误或者找不到
	        if (!$row) {
	            return '评论失败';
	        }
	        if (!$row['uid']&&!$row['sid']&&$row['visitor_id']){
	            $value = D('Visitor')->getOneInfo($row['visitor_id']);
	            $value['nickname']=$value['name'];
	        }else {
	            $value = $this->getCommenterInfo($row['uid'], $row['sid']);
	        }
	        // 组装数据
	        $this->where('id = %d', $lastId)->setField('parent_nickname', $value['nickname']);
	    }
	    
	    // 这里课程信息那里的修改评论总数
	    $count = $this->where('iid = %d', $iid)->count();
	    M('ShopInfo')->where('id = %d', $iid)->setField('comment_count', $count);
	    
	    return [
	        'lastId'            =>  intval($lastId),
	        'parent_nickname'   =>  $value['nickname'],
	    ];
	    
	}
	
	/**
	 * 更新两个字段值，用户的id跟游客的id关联
	 * @param number $uid
	 * @param number $visitorid
	 * @param number $gid
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function updataVisitorTouid($uid=0,$visitorid=0){
	
	    if ($uid==0||$visitorid==0){
	        return false;
	    }
	    $visitComGid=$this->field('iid')->where('visitor_id=%d',$visitorid)->select();
	    if (!$visitComGid){
	        return false;
	    }
	    $visitGidList=array_column($visitComGid,'iid');
	    $comPode=implode(',',$visitGidList);
	
	    $data=array(
	        'uid'     =>$uid,
	        'visitor_id'=>0,
	        'visitor_check_id'=>$visitorid,
	    );
	    $where['iid'] = array('IN',$comPode);
	    $map['uid'] = array('EQ',0);
	    $map['sid'] = array('EQ',0);
	    $where['visitor_id'] = array('EQ',$visitorid);
	    $saveVisit=$this->where($where)->setField($data);
	     
	    if (!$saveVisit){
	        return $saveVisit;
	    }
	    return true;
	}
	
	
	
//-----------------------游客------------------------------------------------------------------------------------------
	/**
	 * 根据ID删除评论
	 * @param $commentId int 评论的id
	 */
	public function deleteByvisitorid($commentId, $visitorid) {
	    // 评论的id不能为空或0
	    if (!$commentId) {
	        return '评论不存在(0)';
	    }
	
	    // 获取那条评论
	    $comment = $this->where('id = %d', $commentId)
                	    ->find();
	
	    if ($comment === false) {
	        return $this->getDbError();
	    }
	
	    if ($comment === null) {
	        return '评论不存在';
	    }
	
	    // 判断这个评论是不是本人的，注意$userId和$shopkeeperId其中一个必为0
	    if ($comment['visitor_id'] != intval($visitorid) ) {
	        return '非法操作';
	    }
	
	    // 删除这条评论信息
	    $this->where('id = %d', $commentId)->delete();
	
	    return true;
	}
	
}
