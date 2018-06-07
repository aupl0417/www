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
        //获取用户的签到记录
	    $Atten = D('Attendance')->getAttendanceData(true, array('att_userId'=>$this->userId), '', 'att_classTableId desc,att_createTime asc');
        foreach($Atten as $key=>$val){
            if(($key + 1) % 2 == 0){
                $AttenEnd[] = $val;
            }else {
                $AttenStart[] = $val;
            }
        }
        
        //获取签到的详细信息
        $field = array('cta_classId', 'cta_courseId', 'co_name as courseName', 'cta_startTime', 'cta_endTime','tra_name');
        foreach($AttenStart as $key=>&$val){
            foreach($AttenEnd as $k=>$v){
                if($val['att_userId'] == $v['att_userId'] && $val['att_classTableId'] == $v['att_classTableId']){
                    $val['endAttenTime'] = $v['att_createTime'];
                }
            }
            
            $val['date'] = date('Y-m-d', strtotime($val['att_createTime']));
            $where = array('cta_id'=>$val['att_classTableId']);
            $courseInfo = D('Common/ClassTable')->getList($where, $field);
            $val = array_merge($val, $courseInfo[0]);
            if(!isset($val['endAttenTime'])){//忘记扫码，判断是签到扫码还是退场扫码
                $attenTime = strtotime($val['att_createTime']);
                $startTime = strtotime($val['cta_startTime']);
                $endTime   = strtotime($val['cta_endTime']);
                if($attenTime > $endTime){//如果扫码时间大于课时结束时间，则为退场时间
                    $val['endAttenTime'] = $val['att_createTime'];
                    $val['att_createTime'] = '';
                }
            }
        }
        $this->meta_title = '签到记录';
        $this->list = $AttenStart;
        $this->display();
    }
	
	/*
	* 同一天、同一课、不同课时的考勤记录放在一起
	*
	*/
	public function index1(){
        //获取用户的签到记录
		$fields = array('att_id', 'att_userId', 'att_classTableId', 'att_createTime', 'cta_classId', 'cta_courseId', 'co_name as courseName', 'cta_startTime', 'cta_endTime','tra_name');
	    $Atten = D('Attendance')->getAttendanceData($fields, array('att_userId'=>$this->userId), '', 'cta_startTime asc,att_createTime asc');
		dump($Atten);die;
		$dateTime = array_column($Atten, 'cta_courseId', 'cta_startTime');
		foreach($dateTime as $key=>$val){
			$time = date("Y-m-d", strtotime($key));
			$res[$time] = $val;
		}
		
		$data = $this->attendDataHandle($res, $Atten);//将同一天，同一课程的签到组合到一起
		dump($data);die;
		foreach($data as $key=>$val){
			$temp[] = $this->arrayHandle($val);
		}
		dump($temp[0][0]);die;
        $this->meta_title = '签到记录';
        $this->list = $temp;
        $this->display('index1');
    }
    
    /*
	 *	签到成功
     * param $id 课时id
     * param $userId 用户id
     * 
     * */
    public function success(){
        $id = I('id', 0, 'intval');
        $userId = I('userId');
        $field = is_numeric($userId) ? 'id' : 'thirdPartyUserId';
        if(!M('ucenter_member')->where(array($field=>$userId))->count()){
            $userId = $this->searchUserToThirdPartyById($userId);
            !$userId && $this->error('该学员不存在');
        }
        session('userId', $userId);
        //课时数据
        $info['courseName'] = M('class_table')
                    ->join('LEFT JOIN __COURSE__ on cta_courseId=co_id')
                    ->where(array('cta_id'=>$id))
                    ->getField('co_name');
					
        $info['attenTime'] = M('attendance')->where(array('att_userId'=>$userId, 'att_classTableId'=>$id))
						   ->order('att_id desc')->limit(1)
						   ->getField('att_createTime');
						   
        $info['courseId'] = $id;
        $info['userId'] = $userId;
        $this->info = $info;
        
        $this->meta_title = '签到成功';
        $this->display();
    }
	
	private function attendDataHandle($timeArr, $dataArr){
		if(empty($timeArr) || empty($dataArr)){
			return array();
		}
		// dump($timeArr);
		dump($dataArr);
		$i = 0;
		foreach($timeArr as $key=>$val){
			$data[$i] = array();
			foreach($dataArr as $k=>&$v){
				$date = date("Y-m-d", strtotime($v['cta_startTime']));
				if($key == $date){
					$v['date'] = $key;
					if($val == $v['cta_courseId']){
						$data[$i][] = $v;
					}
				}
			}
			$i++;
			// dump($data['2016-09-24']);die;
		}
		dump($data);die;
		return $data;
	}
	
	private function arrayHandle($array) {
		$count = count($array);
		if($count > 1){
			$courseIds = array_column($array, 'att_classTableId');
			$courseIds = array_unique($courseIds);
			$data = $this->attendMerge($courseIds, $array);
		}else{
			// dump($array);die;
			$attenTime = strtotime($array[0]['att_createTime']);
			$startTime = strtotime($array[0]['cta_startTime']);
			$endTime   = strtotime($array[0]['cta_endTime']);
			if($attenTime > $endTime){//如果扫码时间大于课时结束时间，则为退场时间
				$array[0]['attenEndTime'] = $array[0]['att_createTime'];
				$array[0]['attenStartTime'] = '';
				unset($array[0]['att_createTime']);
			}
			$data[] = $array[0];
		}
		
		return $data;
	}
	
	private function attendMerge($courseIds, $attendData){
		$i = 0;
		foreach($courseIds as $key=>$val){
			$data[$i] = array();
			foreach($attendData as $k=>&$v){
				if($val == $v['att_classTableId']){
					foreach($v as $kk=>$vv){
						if(in_array($kk, array('cta_classId', 'cta_courseId', 'courseName', 'tra_name', 'date'))){
							$data[$i][$kk] = $vv;
						}else if($kk == 'att_createTime'){
							$data[$i]['attendTime'][][$kk] = $vv;
						}
					}
					unset($attendData[$k]);
				}
			}
			$i ++;
		}
		$atten = array_column($data, 'attendTime');
		
		foreach($data as $key=>&$val){
			unset($data[$key]['attendTime']);
		}
		// dump($atten);
		foreach($atten as $key=>&$val){
			// dump($val);
			$val['attenStartTime'] = $val[0]['att_createTime'];
			$val['attenEndTime']   = $val[1]['att_createTime'];
			unset($atten[$key][0]);
			unset($atten[$key][1]);
		}
		$data = array_unique($data);
		$data[0]['attenTime'] = $atten;
		return $data;
	}
}
