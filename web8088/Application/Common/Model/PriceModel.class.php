<?php

namespace Common\Model;
use Think\Model;

/**
 * 地区Model
 * @author jmjoy
 *
 */
class PriceModel extends Model {

	public function getId($id) {
		$row = $this->where('id = %d', $id)->find();
        if ($row['ltprice']) {
            $row['ltprice'] = floor($row['ltprice']);
        }
        if ($row['reference']) {
            $row['reference'] = floor($row['reference']);
        }
        return $row;
	}

}
