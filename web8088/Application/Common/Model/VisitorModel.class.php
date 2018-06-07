<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class VisitorModel extends CommonModel {

    
    
//-------------------------------------------游客信息处理---------------------------------------------
    /**
     * 增加游客
     * @return boolean|Ambigous <\Think\mixed, boolean, unknown, string>
     */
    public function addOneVisitor(){
        $name='约客'.mt_rand(1000,9999);
        $data=array();
        $data['name']=$name;
        $data['ctime']=date('Y-m-d H:i:s');
        $rel = $this->data($data)->add();
        if (!$rel){
            return false;
        }
        return $rel;
    }


    /**
     * 获取游客的信息
     * @param number $vid
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function getOneInfo($vid=0){
        $info = $this->where('id=%d',$vid)->find();
        if (!$info){
            return false;
        }
        return $info;
    }
    
 

}