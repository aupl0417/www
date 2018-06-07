<?php
	header("Content-type:text/html;charset=utf-8");
	$number = '0321230000000456007809.2543654';
	
	echo $str = getMoneyNumberString(ltrim($number, '0'));
	/*
		结果为：叁万亿亿贰千壹百贰十叁亿亿零肆亿伍千陆百万柒千捌百玖元贰角伍分肆厘
	*/
	
	function getMoneyNumberString($number){
		$moneyNum = explode('.', $number);
		$numberInt = $moneyNum[0];
		$numberArr = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
		
		$numberString = getIntMoney($numberInt, $numberArr);
		if(isset($moneyNum[1])){
			$numberSmall = $moneyNum[1];//小数点后面的数字
			$numberString .= getSmallMoney($numberSmall, $numberArr, strlen($moneyNum[1]));
		}
		
		return $numberString;
	}
	
	//处理整数
	function getIntMoney($number, $numberArr, $size = 4){
		$moneyUnitSmall = array('', '十', '百', '千');//去掉元：不是重复使用的单位
		$moneyUnitBig = array('', '万', '亿', '万亿', '亿亿', '万亿亿');
		
		$data = array();
		$cycleSize = ceil(strlen($number) / $size) - 1; //外循环次数
		for($i = $cycleSize; $i >= 0; $i--){
			$data[$i] = strrev(substr(strrev($number), $i * $size, $size));//每4位一组
			$data[$i] = str_split($data[$i]);//拆分成数组
			$keySize = count($data[$i]) - 1;//子数组键的最大值
			$data[$i] = array_map(function($val){ return intval($val);}, $data[$i]);
			if(array_sum($data[$i]) == 0){//如果遇到4位以上连续的0，则跳过一次外循环
				$data[$i] = '';
				$cycleSize --;
				continue;
			}else {
				foreach($data[$i] as $key=>&$val){//使用引用，减少内存开销
					$val = $val > 0 ? $numberArr[$val] . $moneyUnitSmall[$keySize] : '零'; //拼接小单位，如果是0，则
					$keySize--;
				}
			}
			
			$data[$i][] = $moneyUnitBig[$cycleSize]; //拼接大单位
			$data[$i] = implode('', $data[$i]);
			$data[$i] = preg_replace("/(零)+(" . $moneyUnitBig[$cycleSize] . ")/", $moneyUnitBig[$cycleSize], $data[$i]);//大单位前有连续的零，则去掉
			$data[$i] = preg_replace("/(零)+/", '零', $data[$i]);//每4位中如果有出现1个或多个零，则只显示一个零
			$cycleSize --;
		}
		
		return implode('', $data) . '元';
	}
	
	//处理小数（小数点后三位）
	function getSmallMoney($number, $numberArr, $size = 3){
		$moneyUnitArr = array('角','分','厘', '毫');
		$count = strlen($number);
		$data = str_split(substr($number, 0, $size > 4 ? 4 : $size));
		
		$string = '';
		foreach($data as $key=>$val){
			$string .= $numberArr[$val] . $moneyUnitArr[$key];
		}
		
		return $string;
	}
	
?>