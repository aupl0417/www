<?php

namespace Admin\Controller;
use Think\Page;

/**
 * 后台考勤管理控制器
 */
class AttendanceController extends AdminController {

    /**
     * 教师考勤管理
     */
    public function index(){
        $this->meta_title = '教师考勤管理';
        $this->attendanceData(1);
        $this->display();
    }
    
    //学生考勤管理
    public function lists(){
        $this->meta_title = '教师考勤管理';
        $this->attendanceData(0);
        $this->display('index');
    }
    
    /*  教师考勤和学生考勤
     *  param $type int 0:学生  1：教师
     * */
    public function attendanceData($type = 0){
        $model = D('Common/Attendance');
        //得到所在分院及下属分院的教师/学生的id
        $where['branchId'] = array('in', implode(',', $this->branchIds));
        $where['identityType'] = array('eq', $type);
        $userIds = M('ucenter_member')->where($where)->getField('id', true);
        
        //得到所在分院及下属分院的教师/学生的签到情况
        $field = array('att_id', 'userName', 'identityType', 'att_createTime', 'cta_startTime', 'cta_endTime');
        $map['att_userId'] = array('in', implode(',', $userIds));
        $count = $model->where($map)->count();
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        $result = $model->getAttendanceData($field, $map, $limit);
        foreach($result as $key=>$val){
            $result[$key]['state'] = $this->getAttendanceState($val['att_createTime'], $val['cta_startTime'], $val['cta_endTime']);
        }
        
        $this->result = $result;
        $this->show = $page->show();
    }
    
    //查看
    public function view(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        $field = array('att_id', 'username', 'identityType', 'cl_name as className', 'co_name as courseName', 'br_name as branchName', 'att_createTime', 'cta_startTime', 'cta_endTime');
        $where = array('att_id'=>$id);
        $result = D('Common/Attendance')->getAttendanceData($field, $where);
        
        foreach($result as $key=>$val){
            $result[$key]['state'] = $this->getAttendanceState($val['att_createTime'], $val['cta_startTime'], $val['cta_endTime']);
        }
        
        $this->meta_title = '考勤管理';
        $this->info = $result[0];
        $this->display();
    }
    
    /*
     * 通过签到时间、课程开始和课程结束时间来等到签到状态
     * $createTime 签到时间
     * $startTime  课程开始时间
     * $endTime    课程结束时间
     * return string;
     * */
    private function getAttendanceState($createTime, $startTime, $endTime){
        $att_time = strtotime($createTime);
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        $midTime = ($endTime + $startTime)/2;
        if($att_time < $startTime){
            $state = '已签到';
        }else if($att_time > $startTime && $att_time < $midTime){
            $state = '迟到';
        }else if($att_time > $midTime && $att_time < $endTime){
            $state = '早退';
        }else {
            $state = '旷课';
        }
        return $state;
    }
    
    /* 删除考勤
     * @param $id 考勤id集
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            
            $ids = !is_array($id) ? $id : implode(',', $id);
            $res = D('Common/Attendance')->delInfo($ids, $this->branchIds);
            $res == 2 && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限！'), 'json');
            $res == false && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('delete_Attendance', 'Attendance', $ids, UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
	
}
