<?php

namespace Student\Controller;
/**
 * 课程控制器
 */
class CourseController extends CommonController {
    
    /*
     * param $userId type : mixed  用户id
     * param $id    type : int 课时id
     * */
    public function index(){
        $id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        
        //课时数据
        $fields = array('cta_id', 'cta_description', 'cta_startTime', 'cta_endTime', 'co_name', 'co_description', 'username', 'tra_address','cl_id','cl_name','cl_startTime','cl_endTime','cl_allowableNumber','tra_name');
        $classTableData = D('Common/ClassTable')->getList(array('cta_id'=>$id), $fields);
        !$classTableData && $this->error('该课时不存在');
        
        $classTableData[0]['count'] = M('class_student')->where(array('cs_classId'=>$classTableData[0]['cl_id']))->count();
        $count = M('class_student')->where(array('cs_classId'=>$classTableData[0]['cl_id'], 'cs_studentId'=>$this->userId))->count();
        !$count && $this->error('该学生不上该课时');
        $this->list = $classTableData['0'];
        $this->meta_title = '课程详情';
		
		$this->assign('uid',$this->userId);
        $this->display();
    }
	
    
}
