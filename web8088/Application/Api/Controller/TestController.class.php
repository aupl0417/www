<?php

namespace Api\Controller;
use Api\Controller\CommonController;

class TestController extends CommonController {
	
	public function index() {
		$this->ajaxReturn(array(
				array(
						'status'	=>	404,
						'msg'		=>	'okdfjsadlkfjslkdjflksdjflksjdlfkjsdlkfjslssssssssssssssssssssssssssssssssdkfjlsdk',				
				),
				array(
						'status'	=>	404,
						'msg'		=>	'okdfjsadlkfjslkdjflksdjflksjdlfkjsdlkfjsldkfjlsdk',				
				),
				array(
						'status'	=>	404,
						'msg'		=>	'okdfjsadlkfjslkdjflksdjflksjdlfkjsdlkfjsldkfjlsdk',				
				),
				array(
						'status'	=>	404,
						'msg'		=>	'okdfjsadlkfjslkdjflksdjflksjdlfkjsdlkfjsldkfjlsdk',				
				),												
		));
	}
	
	public function post() {
		$this->ajaxReturn(I('post.'));
	}
  
	
}
