<?php

namespace Student\Controller;
// use Think\Controller;

/**
 * 班级首页控制器
 */
class ClassController extends CommonController {
	
    protected function _initialize(){
        $data = I('request.');
        $this->app = isset($data['app']) ? 1 : 0;
    }
    
    public function index(){
        $field = 'cl_id,gr_name as gradeName,cl_name as className,cl_logo,cl_startTime,cl_cost,br_name as branchName';
        $classList = D('Common/Class')->lists('', $field);
        $this->list = $classList;
        $this->meta_title = '班级列表首页';
        $this->display();
    }
	
    //班级详情
	public function detail(){
		$id = I('id', 0, 'intval');
        $id == 0 && $this->error('非法参数');
        //班级信息
        $fields = 'cl_id,cl_name,cl_startTime,cl_endTime,cl_allowableNumber,cl_cost,br_name,gr_name,tra_name';
        $classInfo = D('Common/Class')->info($id, $fields);
        if(empty($classInfo)){
            $this->error('暂无该课程');
        }
        
        //课程数据列表
        $field = array('cta_id', 'cta_description', 'cta_startTime', 'cta_endTime', 'co_name', 'co_description', 'username', 'tra_address');
        $classTableData = D('Common/ClassTable')->getList(array('cta_classId'=>$id), $field);
        $classInfo['count'] = M('class_student')->where(array('cs_classId'=>$id))->count();
        
        $this->info = $classInfo;
        $this->list = $classTableData;
        $this->meta_title = '班级详情';
        $this->display();
	}
	
	public function search(){
	    $keyword = I('keyword', '');
	    $keyword == '' && $this->ajaxReturn(array('status'=>0, 'msg'=>'success', 'data'=>array()), 'json');
	    
	    $condition['br_name'] = array('like', "%$keyword%");
	    $branchIds = M('branch')->where($condition)->getField('br_id', true);
	    
	    $where = 'cl_name like "%' . $keyword . '%"';
	    if(!empty($branchIds)){
	        $where .= ' or cl_branchId in (' . implode(',', $branchIds) . ')';
	    }
	    //$where = 'cl_name like "%' . $keyword . '%" or cl_branchId in (' . implode(',', $branchIds) . ')';
	    $field = 'cl_id,gr_name as gradeName,cl_name as className,cl_logo,cl_startTime,cl_cost,br_name as branchName';
	    $classList = D('Common/Class')->lists($where, $field);
	    foreach($classList as $key=>&$val){
	        $val['cl_startTime'] = date('Y年m月d日', strtotime($val['cl_startTime']));
	    }
	    $this->ajaxReturn(array('status'=>1, 'msg'=>'success', 'data'=>$classList), 'json');
	}
}
