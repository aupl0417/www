<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * @author user
 */

class InviteOfficialModel extends CommonModel {
	

    //自动验证
    protected $_validate=array(
    );
    //自动完成
    protected $_auto = array (
    );
    
    /**
     * 生成邀请码    
     * @return string|boolean
     */
    public function addCode(){
        $type = I('post.type')?I('post.type'):0; //1为仅一次性，0否
        $num = I('post.num')?I('post.num'):0;
        $endtime = I('post.endtime')?I('post.endtime'):0;
        $create['endtime'] = strtotime($endtime);
        
        if ($create['endtime']<=time()){
            return '请填写往后的日期作为过期时间';
        }
        
        if (!$this->create($create)){
            return $this->getError();
        }
        if ($type!=1 && $endtime){
            $data['code']   = strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8));
            $data['status'] = 1;
            $data['uniqueness'] = 0;
            $data['ctime']  = time();
            $data['endtime']= $this->endtime;
            $res = $this->add($data);
            if (!$res){
                return $this->getDbError();
            }
            $data['id'] = $res;
            session('addinvite',array('0'=>$data));
            return true;
        }else if ($type==1 && $num!=0 ){

            $data['code']   = '';
            $data['status'] = 1;
            $data['uniqueness'] = 1;
            $data['ctime']  = time();
            $data['endtime']= $this->endtime;
            $dataList = array();
            for ( $i=0 ; $i<$num ; $i++ ){
                $code = strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8));
                $data['code'] = $code;
                $dataList[$i] = $data;
            }
            $res = $this->addAll($dataList);
            if (!$res){
                return $this->getDbError();
            }
            $dataList[0]['id'] = $res;
            session('addinvite',$dataList);
            return true;
            
        }
        return '请完整填写';
        
    }
    /**
     * 某条邀请码的详情
     * @param number $id
     * @return boolean|number
     */
    public function getCodeOne($id=0){
        $alias = 'io';
        $array = array(
            'io.id',
            'io.code',
            'io.status',
            'io.uniqueness',
            'io.ctime',
            'io.endtime',
            'count(ig.invitecode) as codenum',
        );
        $join = array(
            'left join __INVITE_GROUP__ ig on ig.invitecode = io.code',
        );
        $where['io.id'] = array('EQ',$id);
        $one = $this -> alias($alias)
        	         -> join($join)
                     -> where($where)
                     -> field($array)
                     -> find();
        if (empty($one['id'])){
            return false;
        }
        if ($one['endtime']>time()){
            $one['overtime'] = 1;
        }else {
            $one['overtime'] = 0;
        }
        $one['ctime'] = date('Y-m-d H:i:s',$one['ctime']);
        $one['endtime'] = date('Y-m-d H:i:s',$one['endtime']);
        return $one;
    }

    /**
     * 获取code信息列表
     * @param number $uniqueness
     * @param unknown $pageOffset
     * @param number $perPage
     * @param string $desc
     * @param string $order
     * @param number $status
     * @return boolean|string
     */
    public function getCodeList($uniqueness=10 , $pageOffset,$perPage=10 , $status=10 , $desc='desc',$order='id' , $overtime=10 ){

        if ($uniqueness!=10){
            $where['uniqueness'] = array('EQ',$uniqueness);
        }
        if ($status==1 || $status==0 ){
            $where['status'] = array('EQ',$status);
        }
        if ($overtime==1){
            $where['endtime'] = array('GT',time());
        }else if ($overtime==0){
            $where['endtime'] = array('LT',time());
        }
    
        $order = $order.' '.$desc;
        $res=$this  -> where($where)
                    -> order($order)
                    -> limit($pageOffset,$perPage)
                    -> select();
        if (!$res){
            return false;
        }
        $inviteGroup = D('InviteGroup');
        foreach ($res as $key=>$value){
            $res[$key]['codenum'] = $inviteGroup->getCodeCount($value['code']);
            $res[$key]['ctime'] = date('Y-m-d H:i:s',$value['ctime']);
            $res[$key]['endtime'] = date('Y-m-d H:i:s',$value['endtime']);
            if ($value['endtime']>time()){
                $res[$key]['overtime'] = 1;
            }else {
                $res[$key]['overtime'] = 0;
            }
            
        }
        return $res;
    }
    /**
     * 对**出现的次数进行分页
     * @param number $curPage
     * @param number $perPage
     * @param unknown $uniqueness
     * @param unknown $status
     * @param unknown $overtime
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function pageAll($curPage=1,$perPage=10,$uniqueness,$status,$overtime){
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $counts = $this->counts($uniqueness,$status,$overtime); // 查询满足要求的总记录数
        $Page  = new \Common\Util\AjaxPage($counts,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray = $Page->getCounts();
        return  $pageArray;
    }
    /**
     * 获取总数
     * @param unknown $uniqueness
     * @return number|unknown
     */
    public function counts($uniqueness,$status,$overtime){
        if ($uniqueness!=10){
            $where['uniqueness'] = array('EQ',$uniqueness);
        }
        if ($status==1 || $status==0 ){
            $where['status'] = array('EQ',$status);
        }
        if ($overtime==1){
            $where['endtime'] = array('GT',time());
        }else if ($overtime==0){
            $where['endtime'] = array('LT',time());
        }
        $countNum = $this   -> where($where)
                            -> count();
        if (!$countNum){
            return 0;
        }
        return $countNum;
    }
    
//-----------------------------------用户--↓---------------------------------------------------------------------------------------    
    /**
     * 检查某个code的情况，
     * @param string $code
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkCode($code=''){
        if (empty($code)){
            return false;
        }
        $res = $this->where("code='%s'",$code)->find();
        if (!$res){
            return false;
        }
        return $res;
    }
    /**
     * 更改邀请码的状态
     * @param string $code
     * @return boolean
     */
    public function updataCodeStatus($id=0){
        if ($id==0){
            return false;
        }
        $field = array(
            'status'    =>  0,
        );
        $res = $this->where("id=%d",$id)->setField($field);
        if (!$res){
            return false;
        }
        return true;
    }
    
    
}