<?php
namespace Api\Controller;

class AttendanceController extends ApiController {
    
	/*  签到接口
	 *  param userId 教师/学员id       must
	 *  param periodId 课时Id         must
	 *  param signValue 签名串        must
	 * */
	public function index() {
// 	    header("Content-type:text/html;charset=utf-8");
		$periodId = $this->data['periodId'];
		$userId = $this->data['userId'];
		(empty($userId) || empty($periodId)) && $this->ajaxReturn(['code' => 200, 'msg' => '参数不能为空']); //用户id和课时id必填
		$branchId = M('class_table')->where(array('cta_id'))->getField("cta_branchId");
		$userInfo = M('ucenter_member')->where(is_numeric($userId) ? 'id=' . $userId : 'thirdPartyUserId="' . $userId . '"')->getField('id');
		if (empty($userInfo) && is_string($userId)) {
			$userId = $this->searchUserToThirdPartyById($userId, $branchId);
			if (!$userId) {
				$this->ajaxReturn(['code' => 201, 'msg' => '用户不存在！']); //用户不存在		
			}
		} else {
			$userId = $userInfo;
		}
		
		$attendStatTime = date('Y-m-d') . ' 06:00:00';
		$attendEndTime = date('Y-m-d') . ' 22:00:00';
		$where['cta_startTime'] = array('egt', $attendStatTime);
		$where['cta_endTime'] = array('elt', $attendEndTime);
		$where['cta_id'] = array('eq', $periodId);
		$periodData = D('Common/ClassTable')->info($where);
		empty($periodData) && $this->ajaxReturn(['code' => 202, 'msg' => '今天没有该课！']);
		
		//将用户加入该课时所对应的班级
		$q = M('class_student')->where('cs_classId=' . $periodData['cta_classId'] . ' and cs_studentId=' . $userId)->count();
		if (!$q) {
		    $data = ['cs_classId' => $periodData['cta_classId'], 'cs_studentId' => $userId, 'cs_createTime' => date('Y-m-d H:i:s', time()), ];
		    M('class_student')->add($data);
		}
		
		$startTime = strtotime($periodData['cta_startTime']);
		$endTime   = strtotime($periodData['cta_endTime']);
		if(date('Y-m-d') != date('Y-m-d', $startTime)){
		    $this->ajaxReturn(['code' => 203, 'msg' => '请在开课当天打卡']);
		}
		if(NOW_TIME + 10 * 60 < $startTime){
		    $this->ajaxReturn(['code' => 204, 'msg' => '未到签到时间']);
		}else if(NOW_TIME >= $startTime && NOW_TIME <= $endTime){//中间打卡问题暂时设定为不能打卡
		    $this->ajaxReturn(['code' => 204, 'msg' => '上课期间不能打卡']);
		}else if(NOW_TIME > $endTime + 10 * 60){
		    $this->ajaxReturn(['code' => 204, 'msg' => '已过签到时间']);
		}else if(($startTime - NOW_TIME < 10 * 60) && ($startTime - NOW_TIME > 0)){
		    $where = array('att_userId' => $userId, 'att_classTableId' =>$periodId);
			$where['_string'] = 'att_createTime >= "' . date('Y-m-d H:i:s', ($startTime - 10 * 60)) . '" and att_createTime <= "' . date('Y-m-d H:i:s', $startTime) . '"';
			$count = M('attendance')->where($where)->count();
			//是否已经签到
			if($count){
			    $this->ajaxReturn(['code' => 207, 'msg' => '您已签到了']);
			}
		}else if((NOW_TIME > $endTime) && (NOW_TIME < $endTime + 10 * 60)){
		    $where = array('att_userId' => $userId, 'att_classTableId' =>$periodId);
		    $where['_string'] = 'att_createTime >= "' . date('Y-m-d H:i:s', $endTime) . '" and att_createTime <= "' . date('Y-m-d H:i:s', ($endTime + 10 * 60)) . '"';
		    $count = M('attendance')->where($where)->count();
		    //是否已经签退
		    if($count){
		        $this->ajaxReturn(['code' => 207, 'msg' => '您已签退了']);
		    }
		}
		
		$data = array(
		    'att_userId' => $userId,
		    'att_classTableId' => $periodId,
		    'att_branchId' => $periodData['cl_branchId'],
		    'att_createTime' => date('Y-m-d H:i:s', NOW_TIME)
		);
		$res = M('attendance')->add($data);
		!$res && $this->ajaxReturn(['code' => 208, 'msg' => '打卡失败']);
		
		//签到/签退成功
		$this->ajaxReturn(['code' => 214, 'msg' => $this->_ouputInfo($periodId) ]);
	}
	
	protected function _ouputInfo($classTableId) {
		$classTableModel = D('Common/ClassTable');
		$info = $classTableModel->info($classTableId);
		$returnData = ['courseNmae' => $info['co_name'], 'attendanceTime' => date('H:i', NOW_TIME), 'description' => $info['cta_description'], ];
		return $returnData;
	}
}
