<?php
namespace Api\Controller;

class TestController extends ApiController {
	
    public function index(){
		jsonpReturn(1001, $this->data);
    }
	
	
}
