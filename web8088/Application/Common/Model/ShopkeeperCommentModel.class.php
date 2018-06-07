<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家的评论Model
 * @author  user
 *
 */
class ShopkeeperCommentModel extends CommonModel {
	/**
	 * 获取商家的评论内容
	 * @return array
	 */
	public function getBusinesscomment($curPage=0,$perPage=3,$sid=0,$depth=0,$order='desc'){
	    if (session('?user')){
	        $login_user = session('user.id');
	    }else {
	        return 403;
	    }
	    $count= $this->shopkeeperCommenTall($sid); // 查询满足要求的总记录数
	    $page = $this->shopkeeperCommentpage($curPage,$perPage,$count);
	    $alias = 'com';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
			'com.id',
            'com.sid',
			'com.content',
			'com.uid',
            'com.parent_id',
	        'com.depth',
			'com.ctime',
			'ue.firstname',
			'ue.lastname',
			'ue.avatar',
	    );
	    $join  = array(
				'left join __USER__ ue on ue.id = com.uid',
	    );
	    $where ='com.sid='.$sid;
	    $order ='com.ctime '.$order;
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
	        $rel[$key]['login_user']= $login_user;
	        $rel[$key]['ctime']   = $this->gettimes($value['ctime']);
	        if ($value['depth']!=0){
	            $result = $this->depthUserInfo($value['parent_id']);
	            $rel[$key]['answer_u_name']= $result['firstname'].$result['lastname'];
	            $rel[$key]['answer_u_id']  = $result['uid'];
	        }
	    }
	    $res['shopkeepercomment'] = $rel;
	    $res['pageAll'] = $page['pageAll'];
	    return $res;
	    
	}
	


	public function depthUserInfo($commid=0){
	    $alias = 'com';//定义当前数据表的别名
	    //要查询的字段
	    $array = array(
	        'com.id',
	        'com.uid',
	        'com.sid',
	        'com.ctime',
	        'u.firstname',
	        'u.lastname',
	    );
	    $join  = array(
	        'left join __USER__ u on u.id = com.uid',
	    );
	    $where ='com.id='.$commid;
	    $rel = $this    -> alias($alias)
                	    -> join($join)
                	    -> where($where)
                	    -> field($array)
                	    -> find();
	    if (!$rel){
	        return false;
	    }
	    return $rel;
	}
	
	
	public function gettimes($time){
		//结束的时间（目前的时间）
		$now=time();
		
		$startdate=date("Y-m-d H:i:s",$time);
		$enddate=date("Y-m-d H:i:s",$now);
		$date=floor((strtotime($enddate)-strtotime($startdate))/86400);
		$hour=floor((strtotime($enddate)-strtotime($startdate))%86400/3600);
		$minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
		$second=floor((strtotime($enddate)-strtotime($startdate))%86400%60);
		$ss=date("H:i",$time);
		$st=date("m-d",$time);	
	 	if($date==0){
			if($hour<1){
				if(($minute==1) || ($minute==0)){
					return "刚刚";
				}else{
					return $minute ." 分钟前";
				}
			}else{
				return $hour. "小时前";
			}
		}elseif($date==1){
			return "昨天  " .$ss;
		}elseif($date==2){
			return "前天  " .$ss;
		}else{
			return $st ." ". $ss;
		} 	
	}
	
	public function insert($sid,$content){
	    if (session("?user")) {
    	    $data['uid'] = session("user.id");
    	    $comCounts  = $this->countShopkeeperCommentToday($data['uid']);
	    }else {
	        return '请先登录';
	    }

	    //父ID，深度
	    if ($_POST['parent_id']){
	        $_POST['depth']=intval($_POST['depth'])+1;
	    }else {
	        $_POST['depth']=0;
	    }
	    //限制频繁回复
	    if (session('?shop_com_time')){
	        if (time()<session('shop_com_time')+5){
	            session('shop_com_time',time()+5);
	            return '回复过于频繁';
	        }else {
	            session('shop_com_time',time());
	        }
	    }else {
	        session('shop_com_time',time());
	    }
	    
	    
	    //判断一天回复条数是否超过限制
	    if (is_int($comCounts)&&$comCounts>=100){
	        return '回复超过100条，当天禁止回复';
	    }
	    
	    //评分
	    $user_num=D('User')->userScore();
	    if ($user_num<40){
	        return '请先完善个人资料';
	    }
	    if (!$this->create()) {
	        return $this->getError();
	    }
		$data['sid']=$sid;
		$data['content']  = $content;
		$data['parent_id']= $this->parent_id;
		$data['depth']    = $this->depth;
		$data['ctime']    = time();
		$res=$this->data($data)->add();
		if(!$res){
			return $this->getDbError();
		}
		return true;
	}
	


	/**
	 * 根据gid，删除此gid下的评论
	 * @param number $gid
	 * @return Ambigous <\Think\mixed, boolean, unknown>
	 */
	public function delCommByGid($comid=0,$sid=0,$user_id=0){
	    if($sid!=0&&$user_id!=0){//删除某条评论中的某人的某条评论
	        $delComm = $this->where('sid=%d and uid=%d and id=%d',$sid,$user_id,$comid)->delete();
	    }
	    if ($delComm===false){
	        return $this->getDbError();
	    }
	    return true;
	}
	
	public function countShopkeeperCommentToday($uid=0){
	    // 获取一天的时间始终
	    list($beginTime, $lastTime) = get_day_time_scope();
	    $count     = $this->where('uid = %d and ctime >= %d and ctime < %d', $uid, $beginTime, $lastTime)->count();
	     
	    if ($count === false) {
	        return $this->getDbError();
	    }
	    return intval($count);
	}
	
	public function shopkeeperCommentpage($curPage=1,$perPage=5,$count=0){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return $pageArray;
	}
	
	
	//=================================================================
	/**
	 * @param number $uid
	 * @return unknown
	 */
	 public function shopkeeperCommenTall($sid=0){
	     $counts = $this->where('sid=%d',$sid)->count();
	     return $counts;
	}
	
}
