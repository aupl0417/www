<?php

namespace Admin\Controller;
use Common\Model\StudyDirectionModel;

class StudyDirectionController extends AdminController {
   protected $studyDirectionModel;
   public function __construct() {
	   $this->studyDirectionModel =  new StudyDirectionModel();	
       parent::__construct();
    }
   
   
    public function lists(){
        $lists = $this->studyDirectionModel->lists();
        $this->ajaxReturn($lists,'JSON');
    }
	
}
