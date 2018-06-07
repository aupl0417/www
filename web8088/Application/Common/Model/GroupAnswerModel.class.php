<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 整个类=====++++=======================================暂时废弃================================
 * @author user
 * 
 */

class GroupAnswerModel extends CommonModel {
	

    //自动验证
    protected $_validate=array(
        array('a_info', '/^.{1,255}$/', '评论内容必须在255个字符以内！', 1, 'regex'),
		array('comid', 'require', '评论出现错误！', 1),
        array('comid', '/^\d+$/', '评论错误', 0, 'regex'),
		array('gid', 'require', '评论出现错误！', 1),
        array('gid', '/^\d+$/', '评论错误', 0, 'regex'),
    
    );
    //自动完成
    protected $_auto = array (
        array('ctime', 'current_datetime', 1, 'function')
    );
	
    /**
     * 用户回复
     * @return string|boolean
     */
	public function addAnswer(){
	    // 看看是用户回复还是商家回复
	    if (session("?user")) {
	        $data['uid'] = session("user.id");
	        $data['sid'] = 0;
	    } else if (session("?shopkeeper")) {
	        $data['sid'] = session("shopkeeper.id");
	        $data['uid'] = 0;
	    } else {
	        return '403';//还没登录403没权限
	    }

	    
	    if (session('?g_com_answer_time')){
	        if (time()<session('g_com_answer_time')+5){
	            session('g_com_answer_time',time()+5);
	            return '回复过于频繁';
	        }else {
	            session('g_com_answer_time',time());
	        }
	    }else {
	        session('g_com_answer_time',time());
	    }
	    
	    if (!$this->create()) {
	        return $this->getError();
	    }
	    
	    $data['gid']    = $this->gid;
	    $data['comid']  = $this->comid;
	    $data['a_info'] = $this->c_info;
	    $data['ctime']  = date('Y-m-d H-i-s', time());
	    if (!$this->data($data)->add()) {
	        return $this->getDbError();
	    }
	    return true;
	}
	
	
	
	
	
	//获取回复
	public function answerByGidComid($gid=0,$comid=0,$curPage=1,$perPage=2,$order='desc'){
	    $count= $this->answerTall($gid,$comid); // 查询满足要求的总记录数
	    $page = $this->answerPage($curPage,$perPage,$count);
	    $alias = 'ga';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'ga.id',
	        'ga.uid',
	        'ga.sid',
	        'ga.gid',
	        'ga.comid',
	        'ga.answerid',
	        'ga.a_info',
	        'ga.ctime',
	        'u.firstname',
	        'u.lastname',
	        'u.avatar',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = ga.uid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = ga.sid',
	    );
	    $where ='ga.gid='.$gid;
        $order='ga.ctime '.$order;
	    $rel = $this    -> alias($alias)
                        -> join($join)
                        -> where($where)
                        -> order($order)
                        -> limit($page['pageOffset'],$page['perPage'])
                        -> field($array)
	                    -> select();
	    if (!$rel){
	        return false;
	    }
	    foreach ($rel as $key=>$value){
	        $rel[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];
	        $rel[$key]['ctime']=transDate($value['ctime']);
	    }
        $res['answer']  = $rel;
        $res['pageAll'] = $page['pageAll'];
	    return $res;
	}
	

	/**
	 * 当条组团信息的评论数 $comid
	 * @param number $gid
	 * @return unknown
	 */
	public function answerTall($gid=0,$comid=0){
	    if ($comid!=0){
	        $rel = $this->where("gid=%d and comid=%d",$gid,$comid)->count();
	    }else{
	        $rel = $this->where("gid=%d",$gid)->count();
	    }
	    
	    return $rel;
	}
	
	
	
	/**
	 * 当条组团评论 分页数据
	 * 返回分页信息
	 * @param int $curPage
	 * @param int $perPage
	 * @param int $count
	 * @return array $page
	 */
	public function answerPage($curPage=1,$perPage=5,$count=0){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return  $pageArray;
	}
	
	
	
	
	
	
	
	
	/**
	 * 获取某用户的评论某条约课的最新一条记录
	 * @param number $uid
	 * @param number $gid
	 * @param number $sid
	 * @param number $comid
	 * @param string $order
	 * @return boolean|string
	 */
	public function oneAnswer($uid=0,$sid=0,$gid=0,$comid=0,$order='desc'){
	    $alias = 'ga';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'ga.id',
	        'ga.uid',
	        'ga.sid',
	        'ga.gid',
	        'ga.comid',
	        'ga.a_info',
	        'ga.ctime',
	        'u.firstname',
	        'u.lastname',
	        'u.avatar',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = ga.uid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = ga.sid',
	    );
	    if ($uid!=0){
	        $where ='ga.gid='.$gid.' AND ga.comid='.$comid.' AND ga.uid='.$uid.' AND ga.sid=0';
	    }elseif ($sid!=0){
	        $where ='ga.gid='.$gid.' AND ga.comid='.$comid.' AND ga.sid='.$sid.' AND ga.uid=0';
	    }
	    $order='ga.ctime '.$order;
	    $rel = $this    -> alias($alias)
                	    -> join($join)
                	    -> where($where)
                	    -> order($order)
                	    -> field($array)
                	    -> find();
	    if (!$rel){
	        return false;
	    }
	    $rel['skavatar']='/Public/Uploads/'.$rel['skavatar'];
	    $rel['ctime']=transDate($rel['ctime']);
	    $res['answer']=$rel;
	    return $res;
	}
	
	
	
	
	
	
	
	
	
	
//===============================================================================================================	
	/**
	 * 当条组团信息的评论数
	 * @param number $id
	 * @return unknown
	 */
	public function Gcommentall($id=0){
		$rel = $this->where("gid='%d'",$id)->count();
		return $rel;
	}
	
	
}