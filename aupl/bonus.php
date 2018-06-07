<?php
	$res = randBonus(20, 10, 1);
	var_dump($res);
	$cacheKey = "randBonus";
	// S($cacheKey, $data, 20);
	// $result = S($cacheKey);
	$cache = new Redis();
	$cache->connect('127.0.0.1',6379);
	$cache->auth('aupl0401');
	$cache->set($cacheKey, json_encode($res), 20);
	$cache->set('username', 'aupl0401', 20);
	$result = $cache->get($cacheKey);
	$username = $cache->get('username');
	// echo '<pre>';
	var_dump(json_decode($result, true));
	echo $username;
	function randBonus($bonus_total = 0, $bonus_count = 3, $bonus_type = 1){
		$bonus_items = array();
		$bonus_balance = $bonus_total;
		$i = 1;
		while($i < $bonus_count){
			$safe_total = ($bonus_balance - ($bonus_count - $i) * 0.01) / ($bonus_count - $i);
			$rand = $bonus_type ? (mt_rand(1, $safe_total * 100) / 100) : number_format($bonus_total / $bonus_count, 2);
			$bonus_items[] = $rand;
			$bonus_balance -= $rand;
			$i ++;
		}
		$bonus_items[$bonus_count - 1] = $bonus_balance;
		
		return $bonus_items;
	}
?>