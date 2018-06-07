<?php
namespace Desktop\Controller;
use Think\Controller;

class PcController extends Controller {


    public function __construct(){
        parent::__construct();
        if (is_mobile_request()){
            $this->redirect('Home/Index/index');
        }
    }

    public function index(){
        $this->display('Pc:index');
    }

    public function contact(){
        $this->display('Pc:contact');
    }

    public function join(){
        $this->display();
    }

    public function occupation(){
        $this->display();
    }
    public function popularize(){
        $this->display();
    }
    public function problem(){
        $this->display();
    }

}
