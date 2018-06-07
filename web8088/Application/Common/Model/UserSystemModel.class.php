<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 * 系统消息
 */

class UserSystemModel extends CommonModel {
	
    
    protected $_validate = array(
			array('title', '/^.{1,255}$/', '系统消息必须在255个字符以内！', 1, 'regex'),
    );
    protected $_auto = array (
			array('ctime', 'current_datetime', 1, 'function')
    );
    


//========================================给用户发布一条系统的消息=====
    //$uid==0&&$allStatus==1     //给所有用户一条系统消息
    public function sendSysNew($uid=0,$title='',$allStatus=0){
        $obj['title'] = $title;
        if ($uid==0&&$allStatus==1){
            $obj['uid']   = 0;
        }elseif ($uid!=0){
            $obj['uid']   = $uid;
        }else {
            return '请检查参数!';
        }
        if(!$this->create($obj)){
            return $this->getError();
        }
        if (!$this->add()){
            return $this->getDbError();
        }
        return true;
    }
    
    
    
    /**
     * 根据用户的ID，获取该用户的系统消息
     * @param number $curPage
     * @param number $perPage
     * @param number $uid
     * @param string $order
     * @return string
     */
    public function sysByUid($curPage=1, $perPage=5,$uid=0,$order='desc'){
        $page = $this->sysPage($curPage, $perPage, $uid);
        $alias = 'us';//定义当前数据表的别名

        $map['us.uid']  = array('IN',$uid.',0');//uid=0时为 为全部用户推送的系统消息
        $order='us.ctime '.$order;
        $res=$this  -> alias($alias)
                    -> where($map)
                    -> order($order)
                    -> limit($page['pageOffset'],$page['perPage'])
                    -> select();
        if (!$res){
            return $res;
        }
        $userNews=D('UserNews');
        $addnews=$userNews->addCheckTime($uid,4);//更新读取消息的时间
        foreach ($res as $key=>$value){
            $res[$key]['ctime'] = transDate($value['ctime']);
        }
        $data = array(
            'info'=>$res,
            'page'=>$page,
        );
        return $data;
    }
    
    
    
    /**
     * 根据用户的ID获取该用户的系统消息的分页
     * @param number $curPage
     * @param number $perPage
     * @param number $uid
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function sysPage($curPage=1,$perPage=5,$uid=0){
        $count = $this->sysNum($uid);
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray=$Page->getCounts();
        return $pageArray;
    }
    
    /**
     * 根据uid获取用户的系统消息的总数
     * @param number $uid
     * @return unknown
     */
    public function sysNum($uid=0){
        if ($uid!=0){
            $count = $this->where('uid=%d',$uid)->count();
        }else {
            $count = $this->count();
        }
        return $count;
    }
    
    


    /**
     * 返回该uid的还没读的数据记录数
     * @param unknown $uid
     */
    public function newsNumSystem($uid){
        $userNews=D('UserNews');
        $newsOldTime=$userNews->checktime($uid,4);
        $newsNum=$this->dayutime($uid,$newsOldTime['checktime']);
        return $newsNum;
    }
    
    
    
    /**
     * 返回新的插入的数据的记录数，根据uid
     * @param string $oldtime
     * @return unknown
     */
    public function dayutime($uid=0,$oldtime=''){
        if ($uid===0){
            return false;
        }
        $map['uid']  = array('IN',$uid.',0');//uid=0时为 为全部用户推送的系统消息
        $map['ctime']=array('GT',$oldtime);
        $nums=$this->where($map)->count();
        return $nums;
    }

    
    
    
    
    
}