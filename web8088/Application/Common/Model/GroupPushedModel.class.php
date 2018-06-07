<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class GroupPushedModel extends CommonModel {
	
    
    //验证
    protected $_validate=array(
        array('sinfoid', '/^\d+$/', '商家课程ID不是数字！', 1, 'regex'),
        array('gid', '/^\d+$/', '约课ID不是数字！', 1, 'regex'),
    );
    
    
    protected $_auto = array (
        array('ctime', 'current_datetime', 1, 'function')
    );
    
    /**
     * 添加一条推送信息
     * @param number $sinfoid
     * @param number $gid
     * @return string|boolean
    */
    public function addPushByShop($sinfoid=0,$gid=0){
        $_POST['sinfoid']=$sinfoid;
        $_POST['gid']=$gid;
        if (!$this->create()){
            return $this->getDbError();
        }
        $checkNow=$this->checkShopBygid($sinfoid,$gid);//有木有推送过该课程
        if ($checkNow){
            return '502';//已经送过该课程
        }
        $rel = $this->add();
        if(!$rel){
            return $this->getDbError();
        }
        $addone=D('GroupInfo')->addPushedOne($gid);
        $resultPushed=$this->sendSmsPushedUser($sinfoid,$gid);
        return true;
    }
    

    /**
     * 根据gid，删除此gid下的推送
     * @param number $gid
     * @return Ambigous <\Think\mixed, boolean, unknown>
     */
    public function delPushByGid($gid=0){
        $delPushed = $this->where('gid=%d',$gid)->delete();
        if ($delPushed===false){
            return $this->getDbError();
        }
        return true;
    }

    /**
     * 获取某用户的评论某条约课的最新一条记录
     * @param number $infoid
     * @param number $gid
     * @param string $order
     * @return boolean|string
     */
    public function onePushed($infoid=0,$gid=0,$order='desc'){
        $alias = 'gp';//定义当前数据表的别名
        //要查询的字段
        $array = array(
            'gp.id',
	        'gp.sinfoid',
	        'gp.ctime',
	        'si.title',
	        'si.phone_tel',
	        'si.content',
	        'si.environ',
	        'skd.avatar',
        );
        $join  = array(
	        '__SHOP_INFO__ si on si.id = gp.sinfoid',
	        '__SHOPKEEPER_DETAIL__ skd on skd.sid = si.sid',
        );
	    $where='gp.gid='.$gid.' AND gp.sinfoid='.$infoid;
	    $order='gp.ctime '.$order;
        $res = $this    -> alias($alias)
                        -> join($join)
                        -> where($where)
                        -> order($order)
                        -> field($array)
                        -> find();
	    if (!$res){
	        return false;
	    }
	    $res['environ']='/Public/Uploads/'.$res['environ'];//课程头像
	    $res['avatar']='/Public/Uploads/'.$res['avatar'];//课程头像
	    $res['ctime']=transDate($res['ctime']);
	    $rel['pusheds']=$res;
	    return $rel;
    }

    /**
     * 检查 该商家 是否已经 推送 过 该课程 
     * @param number $uid
     * @param number $gid
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkShopBygid($sinfoid=0,$gid=0){
        $check = $this->where('sinfoid=%d and gid=%d',$sinfoid,$gid)->find();
        return $check;
    }
    
    
    
    
	/**
	 * 约课中推送的商家数量===约课详情也用到
	 * @param number $gid
	 * @return unknown
	 */
	public function coursenum($gid=0){
		$rel = $this->where('gid=%d',$gid)->count();
		return $rel;
	}
	
	
	/**
	 * 根据gid获取推送的信息
	 * @param number $gid
	 * @param number $curPage
	 * @param number $perPage
	 * @param string $order
	 * @return boolean|string
	 */
	public function pushedByGid($gid=0,$curPage=1,$perPage=2,$order='desc'){
	    $page=$this->pageByGid($curPage,$perPage,$gid);
	    $alias = 'gp';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gp.id',
	        'gp.gid',
	        'gp.sinfoid',
	        'gp.ctime',
	        'si.title',
	        'si.phone_tel',
	        'si.content',
	        'si.environ',
	        'sk.company_name',
	        'sk.tel',
	        'sk.login_phone',
	        'sk.login_email',
	        'skd.avatar',
	        'skd.nickname',
	    );
	    $join  = array(
	        '__SHOP_INFO__ si on si.id = gp.sinfoid',
	        '__SHOPKEEPER__ sk on sk.id = si.sid',
	        '__SHOPKEEPER_DETAIL__ skd on skd.sid = si.sid',
	    );
	    $where='gp.gid='.$gid;
	    $order='gp.ctime '.$order;
	    $res=$this-> alias($alias)
            	    -> join($join)
            	    -> where($where)
            	    -> order($order)
            	    -> limit($page['pageOffset'],$page['perPage'])
            	    -> field($array)
            	    -> select();
	    if (!$res){
	        return false;
	    }
	    foreach ($res as $key=>$value){
	        $res[$key]['environ'] = '/Public/Uploads/'.$value['environ'];//课程头像
	        $res[$key]['avatar'] = '/Public/Uploads/'.$value['avatar'];//商家头像
	        $res[$key]['ctime'] = transDate($value['ctime']);
	        $res[$key]['content'] = clean_br_content($value['content']);//把多个/r/n变成<br/>
	    }
	    $rel['pusheds'] = $res;
	    $rel['pageAll'] = $page['pageAll'];
	    $rel['page'] = $page;
	    return $rel;
	}
	
	
	/**
	 * 约课中推送商家课程，根据gid进行分页
	 * @param number $curPage
	 * @param number $perPage
	 * @param number $gid
	 * @return Ambigous <\Common\Util\multitype:number, multitype:number >
	 */
	public function pageByGid($curPage=1,$perPage=2,$gid=0){
	   import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	   $count= $this->countByGid($gid); // 查询满足要求的总记录数
	   $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	   $pageArray=$Page->getCounts();
	   return  $pageArray;
	}
	
	/**
	 * 当gid！=0时统计gid数量，当$sinfoid！=0时统计sinfoid的数量,当都为0时统计全部id
	 * @param number $gid
	 * @param number $sid待用
	 */
	public function countByGid($gid=0,$sinfoid=0){
	    if ($gid!=0){
	        $counts=$this->where('gid=%d',$gid)->count();
	    }elseif ($sinfoid!=0){
	        $counts=$this->where('sinfoid=%d',$sinfoid)->count();
	    }else {
	        $counts=$this->count("id");
	    }
	    return $counts;
	}
	
	
//======消息====================================================================================

	/**
	 * 消息
	 * 根据用户的uid 获取该用户发布的约课信息中 商家推送过来的课程
	 * @param number $uid
	 * @param number $curPage
	 * @param number $perPage
	 * @param string $order
	 * @return unknown|string
	 */
	public function newsByUser($uid=0,$curPage=1,$perPage=2,$order='desc'){
	    $groupinfoid = D('GroupInfo');
	    $gidinfo = $groupinfoid->groupidByUser($uid);//$group获取该用户发布过的课程信息id集合
	    if (!$gidinfo){
	        return $gidinfo;
	    }
	    foreach ($gidinfo as $kg=>$vg){
	        $gidArray[$kg] = $vg['id'];
	    }
	    $gidnum = implode(",", $gidArray);
	    $page = $this->pageByGidjihe($curPage, $perPage, $gidnum);
	    $alias = 'gp';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gp.id as gpushedid',
	        'gp.gid',
	        'gp.sinfoid',
	        'gp.ctime as gpctime',
	        'ski.sid',
	        'ski.title',
	        'ski.content',
	        'ski.phone_tel',
	        'ski.environ',
	        'sk.company_name',
	        'sk.login_phone',
	        'skd.nickname',
	        'skd.avatar skavatar',
	    );
	    $join  = array(
	        '__GROUP_INFO__ gi on gi.id = gp.gid',
	        '__SHOP_INFO__ ski on ski.id = gp.sinfoid',
	        '__SHOPKEEPER__ sk on sk.id = ski.sid',
	        '__SHOPKEEPER_DETAIL__ skd on skd.sid = sk.id',
	    );
	    $map['gp.gid']  = array('in',$gidnum);
	    $order='gp.ctime '.$order;
	    $res=$this    -> alias($alias)
                    	    -> join($join)
                    	    -> where($map)
                    	    -> order($order)
                    	    -> limit($page['pageOffset'],$page['perPage'])
                    	    -> field($array)
                    	    -> select();

	    $userNews=D('UserNews');
	    $addnews=$userNews->addCheckTime($uid,3);//更新读取消息的时间
	    if (!$res){
	        return $res;
	    }
	    
	    
	    
	    foreach ($res as $key=>$value){
	        $res[$key]['environ'] = '/Public/Uploads/'.$value['environ'];//课程的头像
	        $res[$key]['skavatar'] = '/Public/Uploads/'.$value['skavatar'];//商家的头像
	        $res[$key]['gpctime'] = transDate($value['gpctime']);
	    }
	    $data = array(
	        'pushnews'=>$res,
	        'page'=>$page,
	    );
	    return $data;
	}
	
	

	/**
	 * 根据传入的gid一位 集合 获取有组团过in在此数组范围内的  推送数
	 * @param array $gid
	 * @return unknown
	 */
	public function countByUser($gid){
	    $map['gid']  = array('in',$gid);
	    $counts = $this->where($map)->count();
	    return $counts;
	}
	
	/**
	 * gid集合出现的总数进行分页
	 * 对该用户的 约课评论数 进行 约课分页
	 * @param number $curPage
	 * @param number $perPage
	 * @param string $gid集合
	 * @return Ambigous <\Common\Util\multitype:number, multitype:number >
	 */
	public function pageByGidjihe($curPage=1,$perPage=2,$gid=''){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $count= $this->countByUser($gid); // 查询满足要求的总记录数
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return  $pageArray;
	}
	
	



	/**
	 * 返回该uid的还没读的数据记录数
	 * @param unknown $uid
	 */
	public function newsNumPushed($uid){
	    $userNews=D('UserNews');
	    $newsOldTime=$userNews->checktime($uid,3);
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
	    $map['ctime']  = array('GT',$oldtime);
	    $map['gid']= array('IN',$gidnum);
	    $nums=$this->where($map)->count();
	    return $nums;
	}
	
	
	
	

//======统计，商家为用户的约课推送的课程信息，并且定时调用统计好，给用户发送推送的课程的短信====================================================================================
	public function userForShop($t){
	    
	    $oldSendTime=$this->pushedtimesend($t);
	    if ($oldSendTime===false){
	        return '时间错误';
	    }
	    $oldSendTime1=date('Y-m-d H:i:s',$oldSendTime);
// 	    $oldSendTime1='2015-03-20 15:19:44';


	    $alias = 'gp';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gp.id',
	        'gp.gid',
	        'gp.sinfoid',
	        'gp.ctime',
	        'u.id as uid',
	        'u.firstname',
	        'u.lastname',
	        'u.phone',
	        'u.email',
	        'count(u.id) `user_count`',
	        'si.title',
	        'si.phone_tel',
	    );
	    $join  = array(
	        'left join __GROUP_INFO__ gi on gi.id = gp.gid',
	        'left join __USER__ u on u.id = gi.uid',
	        'left join __SHOP_INFO__ si on si.id = gp.sinfoid',
	    );
	    $where['gp.ctime']=array('gt',$oldSendTime1);
        $pushedInfo = $this -> alias($alias)
                    	    -> join($join)
                    	    -> where($where)
                    	    ->group('u.id')
                    	    -> field($array)
                    	    -> select();
    
        
        foreach ($pushedInfo as $key=>$row) {
            if ($row['email']) { // 这个商家是使用邮箱作为联系方式
                $emails[$key]['email'] = $row['email'];
                $emails[$key]['num'] = $row['user_count'];
            } else if ($row['phone']) { // 这个商家使用手机作为联系方式
                $phones[$key]['phone'] = $row['phone'];
                $phones[$key]['num'] = $row['user_count'];
            }
        }       
       
        $phonecode=0;
        $emailcode=0;

        $subject='【17约课】';
        $body1='您好！您在17约课上发布的心愿，已经有商家为您推送';
        $body2='个相关的课程，请及时查看。登录17yueke.cn。';
        if (!empty($emails)){
            foreach ($emails as $emailkey=>$emailvalue){
                $body=$body1.$emailvalue['num'].$body2;
                $emailstatus=sendMail($emailvalue['email'],$body, $subject);
//                 $bodyemail[$emailkey]=$emailvalue['email'].$body1.$emailvalue['num'].$body2;
            }
        }elseif (!empty($phones)){
            require_once('./Api/sms/sms_send.php');
            foreach ($phones as $telkey=>$telvalue){
                $body=$body1.$telvalue['num'].$body2;
                $msg=$body.$subject;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
                $phonecode=sendnote($telvalue['phone'],urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
//                 $body=$body1.$telvalue['num'].$body2;
//                 $msgphone[$telkey]=$telvalue['phone'].$body.$subject;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
            }
        }
// print_r($bodyemail);
// print_r('<br/>');        
// print_r($msgphone);
// exit;
        $dataarray['status']=200;
        $dataarray['emails']=$emails;
        $dataarray['phones']=$phones;
        $dataarray['emailcode']=$emailcode;
        $dataarray['phonecode']=$phonecode;
        return $dataarray;
  
	}
	

	
	
	
	
	/**
	 * 判断时间段
	 * @param unknown $t
	 * @return string
	 */
	public function pushedtimesend($t){
	    switch ($t){
	        case '10':
	            $oldtime=time()-60*60*18;
	            break;
	        case '16':
	            $oldtime=time()-60*60*6;
	            break;
	        default:
				return false;
	    }
	    return $oldtime;
	}
	
// ===============================================================================================================
// ============================================= protected functions =============================================
// ===============================================================================================================
	/**
	 * 根据 被推送的课程 发送短信给，该约课的  跟约的全部用户
	 */
	public function sendSmsPushedUser($infoId=0,$gid=0) {
	    $alias = 'gp';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'gp.id',
	        'gp.gid',
	        'gp.sinfoid',
	        'gp.ctime',
	        'u.id as uid',
	        'u.firstname',
	        'u.lastname',
	        'u.phone',
	        'u.email',
	    );
	    $join  = array(
	        'left join __GROUP_INFO__ gi on gi.id = gp.gid',
	        'left join __USER__ u on u.id = gi.uid',
	    );
	    $where='gp.sinfoid='.$infoId.' and gp.gid='.$gid;
        $pushedInfo = $this -> alias($alias)
                    	    -> join($join)
                    	    -> where($where)
                    	    -> field($array)
                    	    -> find();
        
        $GroupAssist=D('GroupAssist');
        $snsPushedInfo = $GroupAssist->sendSnsPushed($gid);


        array_push($snsPushedInfo, $pushedInfo);
        $array_pushed=array();
        $i_assist_un=0;
        foreach ($snsPushedInfo as $keysns=>$valuesns){
            if ($pushedInfo['uid']!=$valuesns['uid']){
                $array_pushed[$keysns] = $valuesns;
            }else {
                $i_assist_un++;
                if ($i_assist_un===1){
                    $array_pushed[$keysns] = $valuesns;
                }
            }
        }

        
        $emails=array();
        $phones=array();
        foreach ($array_pushed as $key=>$row) {
            if ($row['phone']) { // 这个商家使用手机作为联系方式
                $phones[$key]['phone'] = $row['phone'];
            }else if ($row['email']) { // 这个商家是使用邮箱作为联系方式
                $emails[$key]['email'] = $row['email'];
            } 
        }       
        $phonecode=array();
        $emailcode=array();
        $subject='【17约课】';
        $body='亲～您在17约课跟约或发布的约课单，已经有老师为你推荐课程了哟，赶紧戳17yueke.cn/g/'.$gid.' 看看课程适不适合你吧';
        if (!empty($emails)){
            foreach ($emails as $emailkey=>$emailvalue){
                $emailstatus[]=sendMail($emailvalue['email'],$body, $subject);
            }
        }
        if (!empty($phones)){
            require_once('./Api/sms/sms_send.php');
            foreach ($phones as $telkey=>$telvalue){
                $msg=$body.$subject;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
                $phonecode[]=sendnote($telvalue['phone'],urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
            }
        }
        $dataarray['status']=200;
        $dataarray['emails']=$emails;
        $dataarray['phones']=$phones;
        $dataarray['emailcode']=$emailcode;
        $dataarray['phonecode']=$phonecode;
        return $dataarray;

// print_r($pushedInfo);
// print_r('<br/>');
// print_r($snsPushedInfo);
// print_r('<br/>');
// print_r($array_pushed);
// print_r('<br/>');
// print_r($emails);
// print_r('<br/>');
// print_r($phones);
// exit;
        
        
	
	}
	
	
}