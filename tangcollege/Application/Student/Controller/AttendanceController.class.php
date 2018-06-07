<?php

namespace Student\Controller;
/**
 * 学生签到控制器
 */
class AttendanceController extends CommonController {
	
	/*
	 * 签到记录
	 * param $userId 用户Id
	 * */
    public function index(){
        $fields = array('att_id', 'att_userId', 'att_classTableId', 'att_createTime', 'cta_classId', 'cta_courseId', 'co_name as courseName', 'cta_startTime', 'cta_endTime','tra_name');
        $Atten = D('Common/Attendance')->getAttendanceData($fields, array('att_userId'=>$this->userId), '', 'cta_startTime asc,att_createTime asc');
        
        $classTableIds = array_column($Atten, 'att_classTableId');
        $classTableIds = array_unique($classTableIds);
        $attendData = $this->attendDataHandle($Atten, $classTableIds);
		
        $this->meta_title = '签到记录';
        $this->list = $attendData;
        $this->display();
    }
	
    /*
	 *	签到成功
     * param $id 课时id
     * param $userId 用户id
     * 
     * */
    public function success(){
        $id = I('id', 0, 'intval');
        
        //课时数据
        $info['courseName'] = M('class_table')
                    ->join('LEFT JOIN __COURSE__ on cta_courseId=co_id')
                    ->where(array('cta_id'=>$id))
                    ->getField('co_name');
					
        $info['attenTime'] = M('attendance')->where(array('att_userId'=>$this->userId, 'att_classTableId'=>$id))
						   ->order('att_id desc')->limit(1)
						   ->getField('att_createTime');
						   
        $info['courseId'] = $id;
        $info['userId'] = $this->userId;
        $this->info = $info;
        $this->meta_title = '签到成功';
        $this->display();
    }
    
    /*
     * 将同一课时的签到数据放到一起
     * param $Atten 签到记录数据
     * param $classTableIds 课时id集
     * return array;
     * */
    private function attendDataHandle($Atten, $classTableIds){
        if(empty($Atten) || empty($classTableIds)){
            return array();
        }
        $i = 0;
        foreach($classTableIds as $key=>$val){
            foreach($Atten as $k=>$v){
                if($val == $v['att_classTableId']){
                    $data[$i][] = $v;
                }
            }
            $i++;
        }
        $data = $this->mergeAttenByClassTableId($data);
        return $data;
    }
    
    /*
     * 将同一课时的签到组合到同一数组
     * array(
     *  'att_classTableId'
     *  'cta_courseId'
     *  'courseName'
     *  'tra_name',
     *  'date',
     *  'attendTime' => array(
     *          array('attendStartTime','attendEndTime')
     *   )
     * );
     * */
    private function mergeAttenByClassTableId($data){
        foreach($data as $key=>$val){
            $createTime = array_column($val, 'att_createTime');
            $result[$key]['att_classTableId'] = $val[0]['att_classTableId'];
            $result[$key]['cta_courseId'] = $val[0]['cta_courseId'];
            $result[$key]['courseName'] = $val[0]['courseName'];
            $result[$key]['tra_name'] = $val[0]['tra_name'];
            $result[$key]['date'] = date('Y-m-d', strtotime($val[0]['cta_startTime']));
            
            
            $result[$key]['attendTime'] = $this->arrangeAttendTime($createTime, $val[0]['cta_startTime'], $val[0]['cta_endTime']);
        }
        return $result;
    }
    
    /*
     * 判断是签到时间，还是签退时间，漏签判断及其初始化
     * */
    private function arrangeAttendTime($createTime, $startTime, $endTime){
        $startTime = strtotime($startTime);//上课时间
        $endTime = strtotime($endTime);     //下课时间
        $attendTime = array();
        if(count($createTime) == 1){
            if(strtotime($createTime[0]) < $startTime){
                $attendTime = array('attendStartTime'=>$createTime[0], 'attendEndTime'=>'00:00:00');
            }else if(strtotime($createTime[0]) > $endTime) {
                $attendTime = array('attendStartTime'=>'00:00:00', 'attendEndTime'=>$createTime[0]);
            }
        }else{
            if(strtotime($createTime[0]) < strtotime($createTime[1])){
                $attendTime = array('attendStartTime'=>$createTime[0], 'attendEndTime'=>$createTime[1]);
            }else {
                $attendTime = array('attendStartTime'=>$createTime[1], 'attendEndTime'=>$createTime[0]);
            }
        }
        
        return $attendTime;
    }
	
}
