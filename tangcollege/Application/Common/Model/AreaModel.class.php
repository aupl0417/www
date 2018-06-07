<?php
namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
/**
 * 分院模型
 */
class AreaModel extends Model{
	use AutoCache;
	 //根据最后一级地区分类取出所有上级地区select及其option;$id:地区的code;$return: 1-返回options,2-返回数组,;
	public  function initAreaOptions($id = '11', $selected = true, $return = 1){
		$sql = "SELECT 
				area2.*, IF(area.a_code = area2.a_code, 1, 0) AS selected
				FROM 
				".C('DB_PREFIX')."area AS area
				RIGHT JOIN
				".C('DB_PREFIX')."area AS area2 ON area2.a_fkey = area.a_fkey
				WHERE 
				LENGTH(area2.a_code) = 6 
				AND
				TRIM(TRAILING '00' FROM area.a_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM area.a_code)))";
		$Area = $this->query($sql);
		if($Area){
			$_Area = [];
			foreach($Area as $v){
				$_Area[$v['a_fkey']][] = array(
					'id'	   => $v['a_id'],
					'code'	   => $v['a_code'],
					'name'	   => $v['a_name'],
					'fkey'	   => $v['a_fkey'],
					'gdp'	   => $v['a_gdp'],
					'selected' => $v['selected'],
				);
			}
			if($return == 2){
				return $_Area;
			}else{
				$_options = [];
				foreach($_Area as $key => $value){
					$_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
					$_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
				}
				return $_options;
			}
		}else{//如果没有,返回空值
			return ($return == 2) ? [] : '';
		}
		//dump($_options);
	}
	
	//获取下一级地区分类
	public function getAreaChildren($id = ''){
		$where = empty($id) ? 'area.a_level=1' : 'area.a_code="'.$id.'"';
		if(empty($id) || $id == 1) {	
			$sql = "SELECT * FROM  `".C('DB_PREFIX')."area` WHERE a_level =1 ORDER BY `a_gdp` DESC ";
		}else{
		    $sql = "SELECT 
				   area2.*, area.a_id
				   FROM 
				   ".C('DB_PREFIX')."area AS area 
				  RIGHT JOIN
				 ".C('DB_PREFIX')."area AS area2
				  ON area2.a_fkey = area.a_id
				 WHERE area.a_code='".$id."'";
			}		
		$Area = $this->query($sql);
		return $Area;
	}	
	
	//根据最后一级行政区划取出完整行政区划路径
	public  function getFullArea($id = '', $return = 1, $split = ' &gt; '){	
		$sql = "SELECT a_id, a_code, a_name, a_fkey, a_gdp FROM ".C('DB_PREFIX')."area WHERE TRIM(TRAILING '00' FROM a_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM a_code))) ORDER BY a_code ASC";
		$areas = $this->query($sql);
		
		return empty($areas) ? [] : ($return == 0 ? array_column($areas, 'a_name') : array_column($areas, 'a_code'));
	}
	
}
?>
