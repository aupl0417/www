<?php
	
	function hanoi($n, $x, $y, $z){
		if($n == 1){
			move($x, 1, $z);
		}else {
			hanoi($n-1, $x, $z, $y);
			move($x, $n, $z);
			hanoi($n-1, $y, $x, $z);
		}
	}
	
	function move($x, $n, $z){
		echo "Move Disc " . $n . " From " . $x . " To " . $z . "<br>";
	}
	
	hanoi(14, 'x', 'y', 'z');

?>