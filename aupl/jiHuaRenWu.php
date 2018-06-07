<?php
	set_time_limit(0); // run script forever
	ignore_user_abort(); // run script in background
	$interval=15; // do every 15 minutes...
	do{
		$date = date('Y-m-d');
		$beginTime = $date . " 10:19:00";
		$endTime   = $date . " 10:25:00";
		$time = strtotime($date);
		if(time() <= $time){
			continue;
		}
		$fp = fopen('text3.txt','a');
		fwrite($fp,"test--".date('Y-m-d H:i:s')."\r\n");
		fclose($fp);
		sleep($interval); // wait 15 minutes
		if(time() > strtotime($endTime) + $interval){
			break;
		}
		
	}while(true);
?>