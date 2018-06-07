<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class UserNewsModel extends CommonModel {
    
    
    /**
     * 根据访问的类型插入 在不同的类型中加入不同的访问时间 1 2 3 4   
     * @param number $uid
     * @param string $type
     * @return boolean
     */
    public function addCheckTime($uid=0,$type=0){
            $_POST['uid']=$uid;
            $_POST['checktype']=$type;
            if (!$this->create()){
                return false;
            }
            $newsdel=$this->checkOne($uid,$type);
            if ($newsdel!==false){
                $nowstime=current_datetime();
                $typenews=$this->where('uid=%d and checktype=%d',$uid,$type)->setField('checktime',$nowstime);
                return $typenews;
            }
            $data['uid']=$this->uid;
            $data['checktype']=$this->checktype;
            $data['checktime']=current_datetime();
            $rel=$this->data($data)->add();
            if (!$rel){
                return false;
            }
            return $rel;
    }
    
    
    /**
     * 是否有该类型的记录
     * @param number $uid
     * @param unknown $type
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkOne($uid=0,$type){
        $resule=$this->where('uid=%d and checktype=%d',$uid,$type)->find();
        if (!$resule){
            return false;
        }
        return $resule;
    }
    
    
    /**
     * 获取$type此类型的访问时间
     * @param number $uid
     * @param number $type 1 2 3 4
     * @return boolean|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
     */
    public function checktime($uid=0,$type=0){
            $where='uid='.$uid.' and checktype='.$type;
            $rel=$this->where($where)->order('checktime desc')->find();
            if (!$rel){//没有时间记录则返回当前时间
                $rel['checktime']="0000-00-00 00:00:00";
                return $rel;
            }
            return $rel;
    }
    
    
    
    
    
    
    /**
     * 根据id删除记录
     * @param number $id
     * @return boolean
     */
    public function checkdel($id=0){
        $delcheck=$this->where('id=%d',$id)->delete();
        if (!$delcheck){
            return false;
        }
        return true;
    }
    
    
    
    
    
    
    
    
    
    
    
}