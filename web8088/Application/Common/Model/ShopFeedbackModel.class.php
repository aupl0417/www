<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家意见反馈Model
 * @author jmjoy
 *
 */
class ShopFeedbackModel extends CommonModel {

	/**
	 * 商家发起一条反馈信息
	 * @param unknown $shopId
	 * @param unknown $content
	 * @return string|boolean
	 */
	public function addFeedback($shopId, $content) {
		// 过滤
		$content = mb_substr($content, 0, 255, "utf-8");
		$content = addslashes(htmlspecialchars($content));

		// 组装数据
		$data = [
				'sid'		=>	$shopId,
				'content'	=>	$content,
				'ctime'		=>	time(),
		];

		// 插进数据库
		$res = $this->data($data)->add();

		if (!$res) {
			return $this->getDbError();
		}

		return true;
	}

    /**
     * 列出反馈信息
     */
    public function listFeedback($curPage, $listRows, $order='desc', $fields=null) {
        // 要查找的字段
        if (!$fields) {
            $fields = array(
                'sf.id',
                'sf.sid',
                'sf.content',
                'sf.ctime',

                'sd.nickname',
                'sd.avatar',
            );
        }

        // 查询
        $result = $this->alias('sf')
                       ->join('__SHOPKEEPER_DETAIL__ sd on sd.sid = sf.sid')
                       ->page($curPage, $listRows)
                       ->order('sf.id ' . $order)
                       ->select();

        // 数据库除错
        if ($result === false) {
            return $this->getDbError();
        }

        // 没有数据
        if ($result === null) {
            return '没有反馈信息';
        }

        foreach ($result as $key => $value) {
            // 转换时间成好看的格式
            $result[$key]['ctime'] = transDate($value['ctime']);
        }

        return $result;
    }
}
