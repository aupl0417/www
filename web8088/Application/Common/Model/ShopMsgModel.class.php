<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家系统消息Model
 * @author jmjoy
 *
 */
class ShopMsgModel extends CommonModel {

    public $validateRules = array(
        array('sid', 'require', '接受信息的商家不能为空', 1),

        array('sender', '/^\S{1,16}$/u', '发送者名字1~16个字', 1, 'regex'),

        array('content', '/^[\s\S]{1,255}$/u', '系统消息1~255个字', 1, 'regex'),
    );

    /**
     * 统计某一个商家的系统消息的数量
     */
    public function countMsg($shopId) {
        return $this->where('sid = %d or sid = 0', $shopId)->count();
    }

    /**
     * 获取系统消息
     */
	public function listMsg($shopId, $nowPage, $perPage) {
        // 包括自己的和群发的
		$resArr = $this->field(true)
				->where('sid = %d or sid = 0', $shopId)
				->page($nowPage, $perPage)
				->order('id desc')
				->select();

		// 更新红点时间
		if ($nowPage == 1) {
			D("Shopkeeper")->updateRedot($shopId, "sysmsg", $resArr[0]['ctime']);
		}

		return $resArr;
	}

	/**
	 * 获取系统消息最新的数字
	 * @param unknown $shopkeeperId
	 * @param unknown $time
	 * @return string|unknown
	 */
	public function numUpdate($shopkeeperId) {
		// 获取最后查看评论的时间
		$time = M('ShopRedot')->where('sid = %d and type = "sysmsg"', $shopkeeperId)->getField('time');
		if ($time === false) {
			return $this->getDbError();
		}
		if ($time === null) {
			$time = 0;
		}

		// 		$num = $this->alias('sc')
		// 					->join('__SHOP_INFO__ si on si.id = sc.iid')
		// 					->where('si.sid = %d and si.ctime > "%s"', $shopkeeperId, date('Y-m-d H:i:s', $time))
		// 					->count();
		$num = $this->where('ctime > %d', $time)->count();

		if ($num === false) {
			return $this->getDbError();
		}

		return intval($num);
	}

    /**
     * 发送消息
     */
    public function addMsg($inputs) {
        // 验证合法性
        $bool = $this->validate($this->validateRules)->create();
        if (!$bool) {
            return $this->getError();
        }

        // 创建时间
        $this->ctime = time();

        // 添加到数据库
        $result = $this->add();
        if (!$result) {
            return $this->getDbError();
        }

        return true;
    }

}
