<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class InviteGroupModel extends CommonModel {
    
    /**
     * 添加邀请码使用记录
     * @param number $inviteduid
     * @param unknown $code
     * @return boolean|string|unknown
     */
    public function addInvite($inviteduid=0 ,$code){
        if ($inviteduid==0){
            return false;
        }
        if (empty($code)){
            return false;
        }

        $inviteOfficial = D('InviteOfficial');
        $codeInfo = $inviteOfficial->checkCode($code);
        if ($codeInfo===false){
            return '不存在该邀请码';
        }
        if ($codeInfo['status']!=1){
            return '该邀请码已被使用';
        }
        if ($codeInfo['endtime']<time()){
            return '该邀请码已经过期';
        }
        
        $dataCode['iid'] = $codeInfo['id'];
        $dataCode['uid'] = 0;
        $dataCode['inviteduid'] = $inviteduid;
        $dataCode['invitecode'] = $code;
        $dataCode['ctime'] = time();
        $res = $this->filter('strip_tags')->add($dataCode);
        if (!$res){
            return false;
        }

        if ($codeInfo['uniqueness']==1){
            $codeUpdata = $inviteOfficial->updataCodeStatus( $codeInfo['id'] );
            if ($codeUpdata!==true){
                return $codeUpdata;
            }
        }
        return true;
    }
    
    
    
    
    
    
    
    
    
    
//-----------------------------后台---------下↓-----------------------------------------------------------------------
    /**
     * 获取某个code的使用次数
     * @param string $code
     * @return number|unknown
     */
    public function getCodeCount($code=''){
        $rel = $this->where('invitecode="%s"',$code)->count();
        if (!$rel){
            return 0;
        }
        return $rel;
    }
    
    
    
    


    /**
     * 获取对应code的使用记录--
     * @param unknown $pageOffset
     * @param number $perPage
     * @param number $iid
     * @param string $order
     * @param string $desc
     * @param number $uid
     * @param number $inviteduid
     * @param string $invitecode
     * @return boolean
     */
    public function getCodeInfo( $pageOffset,$perPage=10 ,$iid=0 , $order='id' , $desc='DESC' , $uid=0 , $inviteduid=0 , $invitecode='' ){
        if ($iid!=0){
            $where['iid'] = array('EQ',$iid);
        }
        if ($uid!=0 ){
            $where['uid'] = array('EQ',$uid);
        }
        if ($inviteduid!=0){
            $where['inviteduid'] = array('EQ',$inviteduid);
        }
        if ($invitecode!=''){
            $where['invitecode'] = array('EQ',$invitecode);
        }
    
        $order = $order.' '.$desc;
    
        $alias = 'ig';
        $array = array(
            'ig.id',
            'ig.iid',
            'ig.uid',
            'ig.inviteduid',
            'ig.invitecode',
            'ig.ctime',
            'u.firstname',
            'u.lastname',
            'u.phone',
            'u.email',
        );
        $join = array(
            'left join __USER__ u on u.id = ig.inviteduid',
        );
        $res = $this    -> alias($alias)
                        -> join($join)
                        -> where($where)
                        -> field($array)
                        -> limit($pageOffset,$perPage)
                        -> order($order)
                        -> select();
    
        if (!$res){
            return false;
        }
        return $res;
        
    }
    
    
    

    /**
     * 对**出现的次数进行分页
     * @param number $curPage
     * @param number $perPage
     * @param number $iid
     * @param number $uid
     * @param number $inviteduid
     * @param string $invitecode
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function pageInfo($curPage=1,$perPage=10,$iid=0,$uid=0,$inviteduid=0,$invitecode=''){
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $counts = $this->counts($iid,$uid,$inviteduid,$invitecode); // 查询满足要求的总记录数
        $Page  = new \Common\Util\AjaxPage($counts,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray = $Page->getCounts();
        return  $pageArray;
    }
    /**
     * 获取总数
     * @param number $iid
     * @param number $uid
     * @param number $inviteduid
     * @param string $invitecode
     * @return number|unknown
     */
    public function counts($iid=0,$uid=0,$inviteduid=0,$invitecode=''){
        if ($iid!=0){
            $where['iid'] = array('EQ',$iid);
        }
        if ($uid!=0 ){
            $where['uid'] = array('EQ',$uid);
        }
        if ($inviteduid!=0){
            $where['inviteduid'] = array('EQ',$inviteduid);
        }
        if ($invitecode!=''){
            $where['invitecode'] = array('EQ',$invitecode);
        }
        $countNum = $this   -> where($where)
                            -> count();
        if (!$countNum){
            return 0;
        }
        return $countNum;
    }
    
    
    
    
    
    
    
}