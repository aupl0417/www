<?php

namespace Student\Model;
use Think\Model;



class ClassStudentModel extends Model {
    
    /*  获取学生班级表中数据
     * param 
     * */
    public function getClassStudentList($where, $field = true, $order='', $limit=''){
        return $this->field($field)
                    ->join('LEFT JOIN __CLASS__ on cs_classId=cl_id')
                    ->join('LEFT JOIN __CLASS_TABLE__ on cta_classId=cs_classId')
                    ->join('LEFT JOIN __BRANCH__ on br_id=cl_branchId')
                    ->order($order)
                    ->where($where)
                    ->select();
    }

}
