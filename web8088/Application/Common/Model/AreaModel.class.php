<?php

namespace Common\Model;
use Think\Model;

/**
 * 地区Model
 * @author jmjoy
 *
 */
class AreaModel extends Model {

	protected $fields = array('id', 'areaname');

	protected $pk     = 'id';

	/**
	 * 根据上一级的ID获取地区信息
	 * @param unknown $parentid
	 * @return unknown
	 */
	public function getByParentId($parent_id) {
		$resArr = $this->field("id, areaname")
						->where('parentid = %d', intval($parent_id))
						->select();
		if ($resArr === null) {
			return array();
		}
		return $resArr;
	}

	/**
	 * 根据地区ID获取地区和所有上一级的信息
	 * @param unknown $id
	 */
	public function getAllById($id) {
		// 获取地区深度
		$depth = $this->where("id = %d", intval($id))
					->getField('depth');
		if ($depth === null) {
			return array();
		}
		// 根据地区的深度拼接查询语句
		$this->alias('a0');
		$fields = array();
		// 拼接要查找的字段
		for ($i = 0; $i <= $depth; $i++) {
			$fields[] = "a$i.areaname a{$i}_areaname";
		}
		$this->field($fields);
		// 拼接要联合的表
		for ($i = 0; $i < $depth; $i++) {
			$j = $i + 1;
			$this->join("__AREA__ a$j on a$i.parentid = a$j.id");
		}
		// 加上条件
		$this->where('a0.id = %d', $id);
		// 获取结果
		$resArr = $this->find();
		if (!$resArr) {
			return array();
		}
		return array_values(array_reverse($resArr));
	}

	/**
	 * 获取同级别的所有地区
	 * @param unknown $areaid
	 * @return \Think\mixed
	 */
	public function getSameLevel($areaid) {
		$fmt = "select id, areaname from __AREA__ where parentid = (select parentid from __AREA__ where id = %d)";
		$sql = sprintf($fmt, intval($areaid));
		return $this->query($sql);
	}

	/**
	 * 条件in之下查询地区两级名字
	 * @param unknown $in
	 */
	public function getTwoLevelNameInArr($in) {
		if (is_array($in)) {
			$this->where('this.id in (%s)', implode(',', $in));
		} else {
			$this->where('this.id = %d', $in);
		}

		// 查询两级的地区
		$res = $this->alias('this')
					->field('this.id, parent.id pid, this.areaname this_arename, parent.areaname parent_arename')
					->join("LEFT JOIN __AREA__ parent on this.parentid = parent.id")
					->select();

		return $res;
	}

	/**
	 * 获取广州市的所有区
	 * @return \Think\mixed
	 */
	public function getGuangZhouZone() {
		return $this->field("id, areaname")->where("parentid = 1953")->select();
	}


	/**
	 * 根据地区id获取上一级的id
	 * @param unknown $areaid
	 */
	public function getParentId($areaid) {
		return $this->where('id = %d', $areaid)->getField('parentid');
	}

    /**
     * 获取广州市一下所有的地区
     */
    public function getGuangZhouAllZone() {
        $guangzhou = $this->getGuangZhouZone();
        $ids = array_column($guangzhou, 'id');

        $subArr = $this->field('id, areaname, parentid')
                       ->where('parentid in (%s)', implode(',', $ids))
                       ->select();

        $newArr = array();
        foreach ($guangzhou as $key => $row) {
            $newArr[$key]['item'] = $row;
            foreach ($subArr as $subKey => $subRow) {
                if ($subRow['parentid'] == $row['id']) {
                    $newArr[$key]['sub'][] = $subRow;
                    unset($subArr[$subKey]);
                }
            }
        }

        return $newArr;
    }

}
