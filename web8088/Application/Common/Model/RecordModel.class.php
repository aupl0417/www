<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * @author user
 */

class RecordModel extends CommonModel {
    
    
    public function addRecord($content){
        $recordData = array(
            'content'   =>  $content,
            'ctime'     =>  time(),
        );
        $recordAdd = $this->add($recordData);
        if (!$recordAdd){
            return $this->getDbError();
        }
        return true;
    }
    
    
}