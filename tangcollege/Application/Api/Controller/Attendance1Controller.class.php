<?php
namespace Api\Controller;
class AttendanceController extends ApiController {
	/*  签到接口
	 *  param userId 教师/学员id       must
	 *  param periodId 课时Id         must
	 *  param signValue 签名串                          must
	 * */
	public function index() {
		define('NOW_TIME_L', NOW_TIME);
		//define('NOW_TIME_L',strtotime('2016-09-25 11:50:00'));
		$periodId = $this->data['periodId'];
		$userId = $this->data['userId'];
		(empty($userId) || empty($periodId)) && $this->ajaxReturn(['code' => 200, 'msg' => '参数不能为空']); //用户id和课时id必填
		$userInfo = M('ucenter_member')->where(is_numeric($userId) ? 'id=' . $userId : 'thirdPartyUserId="' . $userId . '"')->field('id,identityType,branchId,thirdPartyUserId')->find();
		if (empty($userInfo) && is_string($userId)) {
			$userId = $this->searchUserToThirdPartyById($userId);
			if (!$userId) {
				$this->ajaxReturn(['code' => 201, 'msg' => '用户不存在！']); //用户不存在		
			}
		} else {
			$userId = $userInfo['id'];
		}
		
		$time0 = (string)date('Y-m-d') . ' 06:00:00';
		$time1 = (string)date('Y-m-d') . ' 12:50:00';
		$time2 = (string)date('Y-m-d') . ' 12:51:00';
		$time3 = (string)date('Y-m-d') . ' 22:10:00';
		$whereStr = 'cta_id='.$periodId.' and cta_startTime > "'.$time0.'" and cta_endTime <"'.$time3.'"';
		$periodData = D('Common/ClassTable')->info($whereStr);
		empty($periodData) && $this->ajaxReturn(['code' => 202, 'msg' => '今天没有该课！']);
		$q = M('class_student')->where('cs_classId=' . $periodData['cta_classId'] . ' and cs_studentId=' . $userId)->count();
		if (!$q) {
			$data = ['cs_classId' => $periodData['cta_classId'], 'cs_studentId' => $userId, 'cs_createTime' => date('Y-m-d H:i:s', time()), ];
			M('class_student')->add($data);
		}
		
		$isam = strtotime($time0) < NOW_TIME_L && strtotime($time1) > NOW_TIME_L;
		$ispm = strtotime($time2) < NOW_TIME_L && strtotime($time3) > NOW_TIME_L;
		if ($isam || $ispm) {
			if ($isam) {
				$t0 = $time0;
				$t1 = $time1;
			} else {
				$t0 = $time2;
				$t1 = $time3;
			}
			$classTableData = M()->query('select * from tang_class_table  where (cta_startTime >= "' . $t0 . '"  and cta_endTime <= "' . $t1 . '") and (cta_classId =' . $periodData['cta_classId'] . ') order by cta_startTime ASC');
			if (!empty($classTableData)) {
				$first = current($classTableData);
				$amStartTime = strtotime($first['cta_startTime']);
				$end = end($classTableData);
				$amEndTime = strtotime($end['cta_endTime']);
				// exit($first['cta_startTime'].'---'.$end['cta_endTime']);
				if (NOW_TIME_L + 30 * 60 < $amStartTime) {
					$this->ajaxReturn(['code' => 203, 'msg' => '非签到时间']);
				}
				$totalTime = $amEndTime - $amStartTime;
				$middleTime = floor(($amStartTime + $amEndTime) / 2);
				if ((NOW_TIME_L > $amStartTime + 15 * 60) && NOW_TIME_L < $middleTime) {
					$this->ajaxReturn(['code' => 204, 'msg' => '非签到时间']);
				}
				if (NOW_TIME_L > $middleTime && NOW_TIME_L < $amEndTime) {
					$this->ajaxReturn(['code' => 205, 'msg' => '非退场时间']);
				}
				if (NOW_TIME_L > $amEndTime + 30 * 60) {
					$this->ajaxReturn(['code' => 206, 'msg' => '非打卡时间']);
				}
				$where = array('att_userId' => $userId);
				$where['_string'] = 'att_createTime >= "' . date('Y-m-d H:i:s', ($amStartTime - 30 * 60)) . '" and att_createTime <= "' . date('Y-m-d H:i:s', ($amEndTime + 30 * 60)) . '"';
				$attendanceinfos = M('attendance')->field('att_createTime')->where($where)->select();
				if (!empty($attendanceinfos)) {
					$lastTime = $info['att_createTime'];
					if (count($attendanceinfos) >= 2) {
						$this->ajaxReturn(['code' => 207, 'msg' => '您已打卡了']);
					}
					if (count($attendanceinfos) == 1 && NOW_TIME_L < $middleTime) {
						$this->ajaxReturn(['code' => 208, 'msg' => '您已签到了']);
					}
				}
				if (NOW_TIME_L < $middleTime) {
					$data = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $first['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', NOW_TIME_L), ];
					M('attendance')->add($data); //打上班卡
					$this->ajaxReturn(['code' => 215, 'msg' => $this->_ouputInfo($periodId) ]);
				}
				$length = count($classTableData);
				if (NOW_TIME_L > $middleTime) {
					if (empty($attendanceinfos)) { //说明没打上班卡，自动补上
						$data = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $first['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', strtotime($first['cta_startTime']) - 60 * 1), ];
						M('attendance')->add($data);
					}
					if ($length == 1) {
						$data = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $first['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', NOW_TIME_L), ];
						M('attendance')->add($data); //打下班卡
						$this->ajaxReturn(['code' => 214, 'msg' => $this->_ouputInfo($periodId) ]);
					} else {
						$data = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $first['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', strtotime($first['cta_endTime']) + 60 * 1), ];
						M('attendance')->add($data); //打下班卡
						$data = [];
						$data[0] = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $end['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', strtotime($end['cta_startTime']) - 60 * 1), ];
						$data[1] = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $end['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', NOW_TIME_L), ];
						M('attendance')->addAll($data);
						if ($length == 2) $this->ajaxReturn(['code' => 214, 'msg' => $this->_ouputInfo($periodId) ]);
						unset($classTableData[0]);
						unset($classTableData[$length - 1]);
						$data = [];
						foreach ($classTableData as $val) {
							$data[0] = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $val['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', strtotime($val['cta_startTime']) - 60 * 1), ];
							$data[1] = ['att_userId' => $userId, 'att_branchId' => 0, 'att_classTableId' => $val['cta_id'], 'att_createTime' => date('Y-m-d H:i:s', strtotime($val['cta_endTime']) + 60 * 1), ];
							M('attendance')->addAll($data);
						}
						$this->ajaxReturn(['code' => 214, 'msg' => $this->_ouputInfo($periodId) ]);
					}
				}
			} else {
				$this->ajaxReturn(['code' => 216, 'msg' => '今天暂时没有该课']);
			}
		} else {
			$this->ajaxReturn(['code' => 206, 'msg' => '非打卡时间']);
		}
	}
	protected function _ouputInfo($classTableId) {
		$classTableModel = new \Common\Model\ClassTableModel();
		$info = $classTableModel->info($classTableId);
		$returnData = ['courseNmae' => $info['co_name'], 'attendanceTime' => date('H:i', NOW_TIME), 'description' => $info['cta_description'], ];
		return $returnData;
	}
}
