<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class GroupcommentModel extends CommonModel {
	
	//自动验证
	protected $_validate=array(
			array('c_info', '/^.{1,255}$/', '评论内容必须在255个字符以内！', 1, 'regex'),
			array('gid', 'require', '评论出现错误！', 1),
	        array('gid', '/^\d+$/', '评论错误', 1, 'regex'),
	        array('pid', '/^\d+$/', '回复错误', 1, 'regex'),
	        array('depth', '/^\d+$/', '回复错误', 0, 'regex'),
	);
	//自动完成
	protected $_auto = array (
			array('addtime', 'current_datetime', 1, 'function')
	);

	/**
	 * 发表组团课程的评论
	 * @return string|boolean
	 */
	public function addComment() {
	    // 看看是用户评论还是商家评论
	    if (session("?user")) {
	        $data['cid'] = session("user.id");
	        $comCounts  = $this->countGroupcommentToday($data['cid']);
	        	
	
	        //评分
	        $user_num=D('User')->userScore();
	        if ($user_num<40){
	            return '410';
	        }
	        $user_avatar_true=D('User')->userAvatars($data['cid']);
	        if ($user_avatar_true!==true){
	            return '请先上传头像';
	        }
	        	
	    } else if (session("?shopkeeper")) {
	        $data['sid']= session("shopkeeper.id");
	        $comCounts  = $this->countGroupcommentToday(0,$data['sid']);
	    } else {
	        return '401';//还没登录401
	        
	    }
	    //父ID，深度
	    if ($_POST['pid']){
	        $_POST['depth']=intval($_POST['depth'])+1;
	    }else {
	        $_POST['depth']=0;
	    }
	    //限制频繁回复
	    if (session('?g_com_answer_time')){
	        if (time()<session('g_com_answer_time')+5){
	            session('g_com_answer_time',time()+5);
	            return '408';
	        }else {
	            session('g_com_answer_time',time());
	        }
	    }else {
	        session('g_com_answer_time',time());
	    }
	
	
	    //判断一天回复条数是否超过限制
	    if (is_int($comCounts)&&$comCounts>=100){
	        return '回复超过100条，当天禁止回复';
	    }
	
	
	
	
	    if (!$this->create()) {
	        return $this->getError();
	    }
	
	    $data['gid']    = $this->gid;
	    $data['c_info'] = $this->c_info;
	    $data['pid']    = $this->pid;
	    $data['depth']  = $this->depth;
	    $data['addtime']= date('Y-m-d H-i-s', time());
	    if (!$this->data($data)->add()) {
	        return $this->getDbError();
	    }
	    D('GroupInfo')->addCommOne($data['gid']);//增加一条评论数
	    return true;
	}
//------------------------------------------------------------------------------------游客处理
	/**
	 * 发表组团课程的评论----游客处理
	 * @return string|boolean
	 */
	public function addCommentByVisitor() {
	    $visitor_id = session('visitor.id');
	    $gid        = I('post.gid')?I('post.gid'):0;
	    $comCounts  = $this->countGroupcommentTodayByVisitor($gid,$visitor_id);
	    
	    //父ID，深度
	    if ($_POST['pid']){
	        $_POST['depth']=intval($_POST['depth'])+1;
	    }else {
	        $_POST['depth']=0;
	    }
	    //限制频繁回复
	    if (session('?g_com_answer_time')){
	        if (time()<session('g_com_answer_time')+5){
	            session('g_com_answer_time',time()+5);
	            return '评论过于频繁';
	        }else {
	            session('g_com_answer_time',time());
	        }
	    }else {
	        session('g_com_answer_time',time());
	    }
	
	
	    //判断一天回复条数是否超过限制
	    if (is_int($comCounts)&&$comCounts>=200){
	        return '回复评论超过200条，该约课禁止回复评论';
	    }
	
	    if (!$this->create()) {
	        return $this->getError();
	    }
	
	    $data['gid']    = $this->gid;
	    $data['c_info'] = $this->c_info;
	    $data['pid']    = $this->pid;
	    $data['depth']  = $this->depth;
	    $data['visitor_id']  = $visitor_id;
	    $data['addtime']= date('Y-m-d H-i-s', time());
	    if (!$this->data($data)->add()) {
	        return $this->getDbError();
	    }
	    D('GroupInfo')->addCommOne($data['gid']);//增加一条评论数
	    return true;
	}
	/**
	 * 更新两个字段值，用户的id跟游客的id关联
	 * @param number $uid
	 * @param number $visitorid
	 * @param number $gid
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function updataVisitorToCid($uid=0,$visitorid=0){

	    if ($uid==0||$visitorid==0){
	        return false;
	    }
        $visitComGid=$this->field('gid')->where('visitor_id=%d',$visitorid)->select();
        if (!$visitComGid){
            return false;
        }
        $visitGidList=array_column($visitComGid,'gid');
        $comPode=implode(',',$visitGidList);
        
        $data=array(
            'cid'     =>$uid,
            'visitor_id'=>0,
            'visitor_check_id'=>$visitorid,
        );
        $where['gid'] = array('IN',$comPode);
        $map['cid'] = array('EQ',0);
        $where['visitor_id'] = array('EQ',$visitorid);
        $saveVisit=$this->where($where)->setField($data);
	    
	    if (!$saveVisit){
	        return $saveVisit;
	    }
	    return true;
	}
	
	/**
	 * 获取某用户的评论某条约课的最新一条记录
	 * @param number $uid
	 * @param number $gid
	 * @param string $order
	 * @return boolean|string
	 */
	public function oneCommentByVisitor($uid=0,$gid=0,$sid=0,$order='desc',$visitor=0){
	
	    $login_user = 0;
	    $login_sk   = 0;
	    
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gc.id',
// 	        'gc.cid as uid',
// 	        'gc.sid',
	        'gc.c_info',
	        'gc.pid',
	        'gc.depth',
	        'gc.visitor_id',
	        'gc.addtime',
// 	        'u.avatar',
// 	        'u.firstname',
// 	        'u.lastname',
// 	        'skd.avatar as skavatar',
// 	        'skd.nickname',
            'vi.name',
	    );
	    $join  = array(
	        'left join __VISITOR__ vi on vi.id = gc.visitor_id',
// 	        'left join __USER__ u on u.id = gc.cid',
// 	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
	    $where='gc.gid='.$gid.' AND gc.visitor_id='.$visitor;
	    $order='gc.addtime '.$order;
	    $rel = $this    -> alias($alias)
                	    -> join($join)
                	    -> where($where)
                	    -> order($order)
                	    -> field($array)
                	    -> find();
	    if (!$rel){
	        return false;
	    }
	    $rel['login_user']= $login_user;
	    $rel['login_sk']  = $login_sk;
	    $rel['login_visitor']  = $visitor; //游客处理
	    if ($rel['depth']!=0){
	        $result = $this->depthUserInfo($rel['pid']);
	        $rel['answer_u_name']= $result['firstname'].$result['lastname'];
	        $rel['answer_u_id']  = $result['uid'];
	        $rel['answer_s_name']= $result['nickname'];
	        $rel['answer_s_id']  = $result['sid'];
	        
	        $rel['answer_vi_name']= $result['name']; //游客处理
	        $rel['answer_vi_id']  = $result['visitor_id']; //游客处理
	    }

	    $rel['addtime']=transDate($rel['addtime']); 
	    $visitor_avatar=C('visitor_config')['avatar'];
	    $rel['visitor_avatar']=$visitor_avatar; //游客处理
	    $res['comment']=$rel;
	    return $res;
	}
	
//------------------------------------------------------------------------------------游客处理

	/**
	 * 根据gid，删除此gid下的评论
	 * @param number $comid评论的ID
	 * @param number $gid约课的ID
	 * @param number $user_id用户的ID
	 * @return Ambigous <\Think\mixed, boolean, unknown>
	 */
	public function delCommByGid($gid=0,$comid=0,$user_id=0,$visitor_id=0){
	    if ($gid!=0&&$comid==0&&$user_id==0){//删除该约课的全部评论
	        $delassist = $this->where('gid=%d',$gid)->delete();
	    }elseif ($gid!=0&&$user_id!=0&&$comid!=0){//删除某条约课评论中的某人的某条评论
	        $delassist = $this->where('gid=%d and cid=%d and id=%d',$gid,$user_id,$comid)->delete();
	        if ($delassist){
	            $groupcommdel=D('GroupInfo')->delCommOne($gid);//减少一条评论数
	        }
	    }elseif ($gid!=0&&$comid!=0&&$user_id==0&&$visitor_id!=0){ //----------- 游客评论处理
	        $delassist = $this->where('gid=%d and cid=%d and id=%d and visitor_id=%d',$gid,$user_id,$comid,$visitor_id)->delete();
	    }else {
	        return '错误!';
	    }
	    if ($delassist===false){
	        return $this->getDbError();
	    }
	    return true;
	}
	
	
	/**
	 * 获取某用户的评论某条约课的最新一条记录
	 * @param number $uid
	 * @param number $gid
	 * @param string $order
	 * @return boolean|string
	 */
	public function oneComment($uid=0,$gid=0,$sid=0,$order='desc'){

	    if (session('?user')){
	        $login_user = session('user.id');
	        $login_sk   = 0;
	    }elseif (session('?shopkeeper')){
	        $login_user = 0;
	        $login_sk   = session('shopkeeper.id');
	    }else {
	        $login_user = 0;
	        $login_sk   = 0;
	    }
	    

	    
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gc.id',
	        'gc.cid as uid',
	        'gc.sid',
	        'gc.c_info',
	        'gc.pid',
	        'gc.depth',
	        'gc.visitor_id',
	        'gc.addtime',
	        'u.avatar',
	        'u.firstname',
	        'u.lastname',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = gc.cid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
	    if ($uid!=0){
	        $where ='gc.gid='.$gid.' AND gc.cid='.$uid.' AND gc.sid=0';
	    }elseif ($sid!=0){
	        $where ='gc.gid='.$gid.' AND gc.sid='.$sid.' AND gc.cid=0';
	    }
	    $order='gc.addtime '.$order;
	    $rel = $this   -> alias($alias)
	                   -> join($join)
	                   -> where($where)
	                   -> order($order)
	                   -> field($array)
	                   -> find();
	    if (!$rel){
	        return false;
	    }
        $rel['login_user']= $login_user;
        $rel['login_sk']  = $login_sk;
        if ($rel['depth']!=0){
            $result = $this->depthUserInfo($rel['pid']);
            $rel['answer_u_name']= $result['firstname'].$result['lastname'];
            $rel['answer_u_id']  = $result['uid'];
            $rel['answer_s_name']= $result['nickname'];
            $rel['answer_s_id']  = $result['sid'];
        }
        
	    $rel['skavatar']='/Public/Uploads/'.$rel['skavatar'];
	    $rel['addtime']=transDate($rel['addtime']);
	    $res['comment']=$rel;
	    return $res;
	}
	


	/**
	 * 获取某条组团的评论 关于深度
	 * @return Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
	 */
	public function commentByGid($gid=0,$curPage=1,$perPage=2,$depth=0,$order='desc'){
        if (session('?user')){
            $login_user = session('user.id');
            $login_sk   = 0;
        }elseif (session('?shopkeeper')){
            $login_user = 0;
            $login_sk   = session('shopkeeper.id');
        }else {
            $login_user = 0;
            $login_sk   = 0;
        }
	    
	    $count= $this->Gcommentall($gid,$depth); // 查询满足要求的总记录数
	    $page = $this->commentpage($curPage,$perPage,$count);
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gc.id',
	        'gc.cid as uid',
	        'gc.sid',
	        'gc.gid',
	        'gc.c_info',
	        'gc.pid',
	        'gc.depth',
	        'gc.visitor_id', // 游客
	        'gc.addtime',
	        'u.avatar',
	        'u.firstname',
	        'u.lastname',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	        'vi.name', //游客
	    );
	    $join  = array(
	        'left join __VISITOR__ vi on vi.id = gc.visitor_id',//游客
	        'left join __USER__ u on u.id = gc.cid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
// 	    $where ='gc.gid='.$gid.' AND gc.depth='.$depth;
	    $where ='gc.gid='.$gid;
        $order ='gc.addtime '.$order;
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
//-----------------------------游客---------------------------------
        $visitor_avatar=C('visitor_config')['avatar'];
//-----------------------------游客---------------------------------
	    foreach ($rel as $key=>$value){
	        $rel[$key]['login_user']= $login_user;
	        $rel[$key]['login_sk']  = $login_sk;
	        $rel[$key]['skavatar']  = '/Public/Uploads/'.$value['skavatar'];
	        $rel[$key]['addtime']   = transDate($value['addtime']);
	        if ($value['depth']!=0){
	            $result = $this->depthUserInfo($value['pid']);
	            $rel[$key]['answer_u_name']= $result['firstname'].$result['lastname'];
	            $rel[$key]['answer_u_id']  = $result['uid'];
	            $rel[$key]['answer_s_name']= $result['nickname'];
	            $rel[$key]['answer_s_id']  = $result['sid'];
	            

	            $rel[$key]['answer_vi_name']= $result['name']; //游客
	            $rel[$key]['answer_vi_id']  = $result['visitor_id']; //游客
	        }
//-----------------------------游客---------------------------------
	        if ($value['visitor_id']!=0){
	            $rel[$key]['visitor_avatar']=$visitor_avatar;
	            if (session('?visitor.id')){
	                $rel[$key]['login_visitor']  = session('visitor.id');
	            }else {
	                $rel[$key]['login_visitor']  = 0;
	            }
	        }
//-------------------------------游客---------------------------------
	    }
        $res['comment'] = $rel;
        $res['pageAll'] = $page['pageAll'];
        $res['page'] = $page;
	    return $res;
	}
	
//===================	


// 	/**
// 	 * 获取某条组团的评论 关于深度
// 	 * @return Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
// 	 */
// 	public function commentByGid($gid=0,$curPage=1,$perPage=2,$order='desc'){
// 	    $page = $this->commentpage($gid,$curPage,$perPage);
// 	    $alias = 'gc';//定义当前数据表的别名
// 	    //要查询的字段
// 	    $array = array(
// 	        'gc.id',
// 	        'gc.cid as uid',
// 	        'gc.sid',
// 	        'gc.c_info',
// 	        'gc.addtime',
// 	        'u.avatar',
// 	        'u.firstname',
// 	        'u.lastname',
// 	        'skd.avatar as skavatar',
// 	        'skd.nickname',
// 	    );
// 	    $join  = array(
// 	        'left join __USER__ u on u.id = gc.cid',
// 	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
// 	    );
// 	    $where ='gc.gid='.$gid;
// 	    $order='gc.addtime '.$order;
// 	    $rel = $this    -> alias($alias)
//                 	    -> join($join)
//                 	    -> where($where)
//                 	    -> order($order)
//                 	    -> limit($page['pageOffset'],$page['perPage'])
//                 	    -> field($array)
//                 	    -> select();
// 	    if (!$rel){
// 	        return false;
// 	    }
// 	    $GroupAnswer=D('GroupAnswer');
// 	    foreach ($rel as $key=>$value){
// 	        $rel[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];
// 	        $rel[$key]['addtime']=transDate($value['addtime']);
// 	        $rel[$key]['answer']=$GroupAnswer->answerByGidComid($gid=0,$comid=0,$curPage=1,$perPage=2,$order='desc');
// 	    }
// 	    $res['comment'] = $rel;
// 	    $res['pageAll'] = $page['pageAll'];
// 	    return $res;
// 	}
	
	
	public function depthUserInfo($commid=0){
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array( //游客
	        'gc.id',
	        'gc.cid as uid',
	        'gc.sid',
	        'gc.visitor_id', //游客
	        'gc.addtime',
	        'u.firstname',
	        'u.lastname',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	        'vi.name', //游客
	    );
	    $join  = array(
	        'left join __VISITOR__ vi on vi.id = gc.visitor_id', //游客
	        'left join __USER__ u on u.id = gc.cid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
	    $where ='gc.id='.$commid;
	    $rel = $this    -> alias($alias)
                	    -> join($join)
                	    -> where($where)
                	    -> field($array)
                	    -> find();
	    if (!$rel){
	        return false;
	    }
        $rel['skavatar']='/Public/Uploads/'.$rel['skavatar'];
	    return $rel;
	}
	
	
	
	/**
	 * 当条组团信息的评论数 深度为0
	 * @param number $gid
	 * @return unknown
	 */
	public function Gcommentall($gid=0,$depth=0){
// 		$rel = $this->where("gid=".$gid,"depth=".$depth)->count();
		$rel = $this->where("gid=".$gid)->count();
		return $rel;
	}
	


	/**
	 * 当条组团评论 分页数据
	 * 返回分页信息
	 * @param int $curPage
	 * @param int $perPage
	 * @return array $page
	 */
	public function commentpage($curPage=1,$perPage=5,$count=0){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return  $pageArray;
	}
// 	/**
// 	 * 当条组团评论 分页数据
// 	 * 返回分页信息
// 	 * @param int $curPage
// 	 * @param int $perPage
// 	 * @return array $page
// 	 */
// 	public function commentpage($gid=0,$curPage=1,$perPage=5,$depth=0){
// 		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
// 		$count= $this->Gcommentall($gid,$depth); // 查询满足要求的总记录数
// 		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
// 		$pageArray=$Page->getCounts();
// 		return  $pageArray;
// 	}
//===消息==============================================================================================================
	
	//消息 根据$uid 约课评论
	public function groupComments($uid=0,$curPage=1, $perPage=2,$order='DESC'){
	    $groupid = D('GroupInfo');
	    $group = $groupid->groupidByUser($uid);//$group获取该用户发布过的课程信息id集合
	    if (!$group){
	        return $group;
	    }
	    foreach ($group as $kg=>$vg){
	        $gidArray[$kg] = $vg['id'];
	    }
	    $gidnum = implode(",", $gidArray);
	    $page = $this->comPage($curPage, $perPage, $gidnum);
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(   
	        'gc.id as commentid',
	        'gc.cid as uid',
	        'gc.sid',
	        'gc.gid as commsid',
	        'gc.c_info',
	        'gc.pid',
	        'gc.depth',
	        'gc.visitor_id', // 游客
	        'gc.addtime as commentctime',
	        'gi.ctime',
	        'gi.title',
	        'gi.environ as gienviron',
	        'gi.sid as groupsid',
	        'u.avatar as comavatar',
	        'u.firstname',
	        'u.lastname',
	        'skd.environ',
	        'skd.nickname',
	        'skd.avatar as skavatar',
	        'vi.name as vitorname', //游客
	    );
	    $join  = array(
	        'left join __VISITOR__ vi on vi.id = gc.visitor_id',//游客
    		'__GROUP_INFO__ gi on gi.id = gc.gid',
    		'left join __USER__ u on u.id = gc.cid',
    		'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
	    $map['gc.gid']  = array('in',$gidnum);
	    $order='gc.addtime '.$order;
	    $res=$this -> alias($alias)
	               -> join($join)
	               -> where($map)
	               -> order($order)
	               -> limit($page['pageOffset'],$page['perPage'])
	               -> field($array)
	               -> select();    

	   $userNews=D('UserNews');
	   $addnews=$userNews->addCheckTime($uid,2);//更新读取消息的时间
	   if (!$res){
	       return $res;
	   }
	   
//-----------------------------游客---------------------------------
	   $visitor_avatar=C('visitor_config')['avatar'];
//-----------------------------游客---------------------------------

// 	   $sidArray=array();
// 	   foreach ($res as $kskey=>$ks){
// 	   		if ($ks['sid']!=0) {
// 	   			$sidArray[$kskey] = $ks['sid'];
// 	   		}
// 	   }
// 	   if (!empty($sidArray)){
// 	       $sidnum = implode(",", $sidArray);
// 	       $skd=M('ShopkeeperDetail');
// 	       $mskd['sid']=array('IN',$sidnum);
// 	       $sidavatar=$skd -> where($mskd)
//                 	       -> field('sid,nickname,avatar')
//                 	       -> select();
	   
// 	   }
	   foreach ($res as $key=>$value){

// 	       if (!empty($sidArray)){
//     	   	   foreach ($sidavatar as $sidkey=>$sidvalues){
//     		   	   	if ($value['sid']==$sidvalues['sid']) {
//     		   	   		$res[$key]['skavatar'] = '/Public/Uploads/'.$sidvalues['avatar'];//商家的头像
//     		   	   		$res[$key]['sknickname'] = $sidvalues['nickname'];
//     		   	   	}
//     	   	   }
// 	       }

// 	       if ($value['skavatar']){
// 	           $res[$key]['skavatar'] = '/Public/Uploads/'.$value['skavatar'];
// 	       }
	       
	       
	       if (!empty($value['gienviron'])){
	           $res[$key]['environ'] = '/Public/Uploads/'.$value['gienviron'];
	       }else {
	           $res[$key]['environ'] = '/Public/Uploads/'.$value['environ'];
	       }
	       
	       
	       $res[$key]['commentctime'] = transDate($value['commentctime']); 
	       $res[$key]['name'] = $value['firstname'].$value['lastname']; 
	       
	       

	       if ($value['depth']!=0){
	           $result = $this->depthUserInfo($value['pid']);
	           $res[$key]['answer_u_name']= $result['firstname'].$result['lastname'];
	           $res[$key]['answer_u_id']  = $result['uid'];
	           $res[$key]['answer_s_name']= $result['nickname'];
	           $res[$key]['answer_s_id']  = $result['sid'];
	           
	           $res[$key]['answer_vi_name']= $result['name']; //游客
	           $res[$key]['answer_vi_id']  = $result['visitor_id']; //游客
	       }
//-----------------------------游客---------------------------------
	       if ($value['visitor_id']!=0){
	           $res[$key]['visitor_avatar']=$visitor_avatar;
	           if (session('?visitor.id')){
	               $res[$key]['login_visitor']  = session('visitor.id');
	           }else {
	               $res[$key]['login_visitor']  = 0;
	           }
	       }
//-------------------------------游客---------------------------------
	       
	       
	   } 

	   $data = array(
	       'info'=>$res,
	       'page'=>$page,
	   );
	    return $data;
	}
	
	
	
	
	/**
	 * 对该用户的 约课评论数 进行 约课分页
	 * @param number $curPage
	 * @param number $perPage
	 * @param unknown $gid
	 * @return Ambigous <\Common\Util\multitype:number, multitype:number >
	 */
	public function comPage($curPage=1,$perPage=2,$gid=''){
	   import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	   $count= $this->comCount($gid); // 查询满足要求的总记录数
	   $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	   $pageArray=$Page->getCounts();
	   return  $pageArray;
	}
	
	/**
	 * 根据传入的gid一位 集合 获取有评论过in在此数组范围内的  评论数
	 * @param string $gid
	 * @return unknown
	 */
	public function comCount($gid=''){
	    $map['gid']  = array('in',$gid);
	    $counts = $this->where($map)->count();
	    return $counts;
	}

	
	/**
	 * 返回该uid的还没读的数据记录数
	 * @param unknown $uid
	 */
	public function newsNumComment($uid){
	    $userNews=D('UserNews');
	    $newsOldTime=$userNews->checktime($uid,2);
	    $newsNum=$this->dayutime($uid,$newsOldTime['checktime']);
	    return $newsNum;
	}
	
	
	
	/**
	 * 返回新的插入的数据的记录数，根据uid
	 * @param string $oldtime
	 * @return unknown
	 */
	public function dayutime($uid=0,$oldtime=''){
	    $groupid = D('GroupInfo');
	    $group = $groupid->groupidByUser($uid);//$group获取该用户发布过的课程信息id集合
        if (!$group) {
        	return 0;
        }
	    foreach ($group as $kg=>$vg){
	        $gidArray[$kg] = $vg['id'];
	    }
	    $gidnum = implode(",", $gidArray);
	    $map['addtime']  = array('GT',$oldtime);
	    $map['gid']= array('IN',$gidnum);
	    $map['cid']= array('NEQ',$uid);
	    $nums=$this->where($map)->count();
	    return $nums;
	}
	

//------------------------------------------------------------------------------------游客处理
	/**
	 * 统计某条约课被某个游客 总共评论了多少次 
	 */
	public function countGroupcommentTodayByVisitor($gid=0,$visitor=0) {
	    $count = $this->where('gid=%d and visitor_id=%d', $gid,$visitor)->count();
	    if ($count === false) {
	        return $this->getDbError();
	    }
	    return intval($count);
	}
//------------------------------------------------------------------------------------游客处理	
	/**
	 * 统计某个评论一天之内发送了多少的评论
	 */
	public function countGroupcommentToday($uid=0,$sid=0) {
	    // 获取一天的时间始终
	    list($beginTime, $lastTime) = get_day_time_scope();
	    $beginTime = date('Y-m-d H:i:s', $beginTime);
	    $lastTime = date('Y-m-d H:i:s', $lastTime);
	    if ($uid==0&&$sid!=0){
	        $count = $this->where('sid = %d and addtime >= "%s" and addtime < "%s"', $sid, $beginTime, $lastTime)->count();
	    }elseif ($uid!=0&&$sid==0){
	        $count = $this->where('cid = %d and addtime >= "%s" and addtime < "%s"', $uid, $beginTime, $lastTime)->count();
	    }else {
	        return false;
	    }
	    
	
	    if ($count === false) {
	        return $this->getDbError();
	    }
	    return intval($count);
	}
//========================================================================================================
	/**
	 * 根据约课ID  查询该约课的总评论数
	 * @param number $gid
	 * @return unknown
	 */
	public function commCount($gid=0){
        $comnum    = $this->where('gid=%d',$gid)->count();
        return $comnum;
	}
	
	
	
	
//==================================  admin  =========================================	
	public function getComByGid($gid=0,$curPage=1,$perPage=2,$depth=0,$order='desc'){
	    
	    $count= $this->Gcommentall($gid,$depth); // 查询满足要求的总记录数
	    $page = $this->commentpage($curPage,$perPage,$count);
	    $alias = 'gc';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gc.id',
	        'gc.cid as uid',
	        'gc.sid',
	        'gc.gid',
	        'gc.c_info',
	        'gc.pid',
	        'gc.depth',
	        'gc.addtime',
	        'u.avatar',
	        'u.firstname',
	        'u.lastname',
	        'u.phone',
	        'u.email',
	        'sk.company_name',
	        'sk.login_phone',
	        'sk.login_email',
	        'sk.tel',
	        'skd.avatar as skavatar',
	        'skd.nickname',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = gc.cid',
	        'left join __SHOPKEEPER__ sk on sk.id = gc.sid',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gc.sid',
	    );
	    $where ='gc.gid='.$gid;
	    $order ='gc.addtime '.$order;
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
	    $res['comment'] = $rel;
	    $res['pageAll'] = $page['pageAll'];
	    $res['page'] = $page;
	    return $res;
	    
	    
	}
//==================================  admin  =========================================	
	
	
}