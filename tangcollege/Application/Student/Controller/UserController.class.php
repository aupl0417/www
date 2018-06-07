<?php

namespace Student\Controller;
/**
 * 用户中心控制器
 */
class UserController extends CommonController {
	
    public function index(){
        
    }
	
    //班级详情
	public function userClass(){
	    $field = 'cl_id,cl_name,cl_logo,cl_startTime,cl_endTime,cl_cost,br_name,cl_allowableNumber';
	    $classIds = M('class_student')->where(array('cs_studentId'=>$this->userId))->getField('cs_classId', true);
	    $where['cl_id'] = array('in', implode(',', $classIds));
        $classList = M('class')->where($where)->field($field)
                   ->join('LEFT JOIN __BRANCH__ on br_id=cl_branchId')
                   ->select();
        
		foreach($classList as $key=>&$val){
		    $val['count'] = M('class_student')->where(array('cs_classId'=>$val['cl_id']))->count();
            $startTime = strtotime($val['cl_startTime']);
            $endTime = strtotime($val['cl_startTime']);
            $remainingTime = ($startTime - NOW_TIME) / 86400;
            $val['remainingTime'] = $remainingTime;
            if(NOW_TIME < $startTime) {
                $state = 1;
            }else if($startTime > NOW_TIME && NOW_TIME < $endTime){
                $state = 2;
            }else {
                $state = 0;
            }
            $val['state'] = $state;
		}
		
		$this->list = $classList;
        $this->meta_title = '班级详情';
        $this->display('userClass');
	}
}
