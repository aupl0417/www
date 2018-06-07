<?php
	interface animal {
		public function run();
		
		public function hunt();
	}
	
	interface plant {
		public function height();
		
		public function age();
	}
	
	class Biology implements animal,plant {
		public function run(){
			echo 'The animals can run on the ground<br>';
		}
		
		public function hunt(){
			echo "The animal can hunt for food<br>";
		}
		
		public function height(){
			echo "The plants have diffent height<br>";
		}
		
		public function age(){
			echo "The plants have diffent age<br>";
		}
	}
	
	$biology = new Biology();
	$biology->run();
	$biology->hunt();
	$biology->height();
	$biology->age();
?>