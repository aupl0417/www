<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class UserCollectModel extends CommonModel {
	
	
    
    

    //验证
    protected $_validate=array(
        array('uid', '/^\d+$/', '用户ID不是数字！', 1, 'regex'),
        array('gid', '/^\d+$/', '约课ID不是数字！', 0, 'regex'),
        array('shopid', '/^\d+$/', '课程ID不是数字！', 0, 'regex'),
    );
    
    
    protected $_auto = array (
        array('ctime', 'current_datetime', 1, 'function')
    );
    
    /**
     * 添加一条收藏约课信息 ,当shopid不为0 && gid为0时添加的是用户收藏商家的课程shopid，当shopid为0 && gid不为0时添加的是用户收藏商家的课程shopid，
     * @param number $uid
     * @param number $shopid
     * @param number $gid
     * @return string|boolean
    */
    public function addCollectByUser($uid=0,$shopid=0,$gid=0){
        $_POST['uid']=$uid;
        if ($gid===0){
            $_POST['shopid']=$shopid;
            $checkNow=$this->checkUserByShopid($uid,$shopid);
        }elseif ($shopid===0){
            $_POST['gid']=$gid;
            $checkNow=$this->checkUserBygid($uid,$gid);
        }
        if (!$this->create()){
            return $this->getDbError();
        }
        if ($checkNow){
            
            //=====================================================================商家取消收藏未做
        	$delnum=$this->delcollByGid($gid,$uid);//存在跟约，删除跟约信息
        	if ($delnum!==true) {
        		return $delnum;
        	}
            return '402';//已经收藏
        }
        $rel = $this->add();
        if(!$rel){
            return $this->getDbError();
        }
        return true;
    }
    
    /**
     * 根据gid，删除此gid下的收藏
     * @param number $gid,$uid
     * @return Ambigous <\Think\mixed, boolean, unknown>
     */
    public function delcollByGid($gid=0,$uid=0){
        $delcollect = $this->where('gid=%d and uid=%d',$gid,$uid)->delete();
        if ($delcollect===false){
            return $this->getDbError();
        }
        return true;
    }
    
    /**
     * 检查 该用户 是否已经 收藏 该约课====
     * @param number $uid
     * @param number $gid
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkUserBygid($uid=0,$gid=0){
        $check = $this->where('uid=%d and gid=%d',$uid,$gid)->find();
        return $check;
    }
    

    /**
     * 检查 该用户 是否已经 收藏 该课程
     * @param number $uid
     * @param number $gid
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkUserByShopid($uid=0,$shopid=0){
        $check = $this->where('uid=%d and shopid=%d',$uid,$shopid)->find();
        return $check;
    }
    
    
    
    
    
    
    
	// 收藏  ASC  desc
	/**
	 * 当前用户收藏约课信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return boolean|unknown
	 */
	public function collectGroupInfo($uid=0,$pageOffset=0,$perPage=2,$sort='desc'){
		
		$alias = 'uc';//定义当前数据表的别名
		//要查询的字段
		$array = array(
				'uc.id',
				'uc.gid',
				'uc.uid as userid',
				'gi.uid',
				'gi.cateid',
				'gi.title',
				'gi.areaid',
				'gi.ltprice',
				'gi.gtprice',
		        'gi.priceid',
		        'gi.catename as cate_name',
				'gi.mode',
		        'gi.content',
		        'gi.environ as gienviron',
				'gi.tags',
				'gi.ctime',
				'gi.overtime',
				'gi.view',
				'gi.number',
				'gi.pushed',
				'gi.gcomment',
				'u.id as userid',
				'u.firstname',
				'u.lastname',
				'u.avatar',
				'u.profession',
				'u.interest',
				'u.telstatus',
		        'uv.vtype',
		        'uv.vstatus',
				'cate.catename',
		        'cate.parent_id',
				'sk.company_name',
				'skd.avatar as skavatar',
				'skd.nickname',
				'skd.environ',
		);
		$join  = array(
				'__GROUP_INFO__ gi on gi.id=uc.gid',
				'__USER__ u on gi.uid = u.id',
				'left join __USER_V__ uv on uv.uid = u.id',
				'__CATEGORY__ cate on gi.cateid = cate.id',
				'left join __SHOPKEEPER__ sk on gi.sid = sk.id',
				'left join __SHOPKEEPER_DETAIL__ skd on sk.id = skd.sid'
		);
		//join可以使用array
		$where = 'uc.uid='.$uid.' AND gi.id=uc.gid AND gi.uid=u.id AND gi.cateid = cate.id AND gi.sid=sk.id AND sk.id = skd.sid';
		$order = 'uc.ctime '.$sort;
		$res=$this  ->alias($alias)
        			->join($join)
        			->where($where)
        			->order($order)
        			->limit($pageOffset,$perPage)
        			->field($array)
        			->select();
		$Group = D('Common/GroupInfo');
		$res=$Group->grouptags($res);
		// 实例化 Groupcomment 模块
		$Comm = D('Common/Groupcomment');
		$GroupPushed = D('Common/GroupPushed');
		$areaAllName=D('Common/Area');
		$Price=D('Price');
		$Category=D('Category');
//		$UserV = D('UserV');
		foreach ($res as $key=>$value){
			$res[$key]['comment']= $Comm->Gcommentall($value['gid']); // 当前组团内的评论数
			$res[$key]['coursemun']=$GroupPushed->coursenum($value['gid']);//商家唉推送的课程总数
			$res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
			$res[$key]['content'] = nl2br($value['content']);//内容转换 
			$res[$key]['skavatar']='/Public/Uploads/'.$value['skavatar'];//商家的头像
			$res[$key]['ctime'] = transDate($value['ctime']);   				//个人申V，1通过，false失败
			//多级分类跟优惠价格
			$res[$key]['pricearr']=$Price->getId($value['priceid']);
			$parent_id=$value['parent_id'];
			$arr=$Category->where("id='$parent_id'")->field('catename')->find();
			$res[$key]['name']=$arr['catename']."/".$value['catename'];
			//约课过期时间
			if (strtotime($value['overtime'])>time()){
			    $res[$key]['overtimes'] = 1;
			}else{
			    $res[$key]['overtimes'] = 0;
			}
			$Mode=C('mode');
			foreach ($Mode as $k => $v){
			    if($value['mode']==$k){
			        $res[$key]['mode']= $v;
			    }
			}
			
			
			if (!empty($value['gienviron'])){
			    $res[$key]['environ'] = '/Public/Uploads/'.$value['gienviron'];
			}else{
			    $res[$key]['environ']='/Public/Uploads/'.$value['environ'];//课程的场景图
			}
			
			
			
		}
		if (!$res) {
			return false;
		}
		return $res;
	}
	
	
	

	/**
	 * 返回当前用户收藏的课程总数
	 * @param number $uid
	 * @return unknown
	 */
	public function shopcCount($uid=0){
		$rel = $this->where('uid=%d and gid=%d',$uid,0)->count();
		return $rel;
	}
	
	
	
	/**
	 * 返回当前用户收藏的总约课数
	 * @param number $uid
	 * @return unknown
	 */
	public function groupCount($uid=0){
		$rel = $this->where('uid=%d and shopid=%d',$uid,0)->count();
		return $rel;
	}
	
	

	/**
	 * 
	 * 收藏分页数据
	 * 返回分页信息
	 * @param number $uid
	 * @param string $type
	 * @param number $curPage
	 * @param number $perPage
	 * @return Ambigous <\Common\Util\multitype:number, multitype:number NULL >
	 */
	public function collectPage($uid=0,$type='',$curPage=1,$perPage=3){
		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		if ($type=='group') {
			$count= $this->groupCount($uid); // 查询满足要求的总记录数
		}else {
			$count= $this->shopcCount($uid); // 查询满足要求的总记录数
		}
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}
	
	
	/**
	 * 用户收藏商家的课程
	 * @param number $uid
	 * @return boolean|Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
	 */
	public function collectShopInfo($pageOffset = 0, $perPage = 2, $uid=0,$sort = 'desc'){
		$rel = $this->where('uid=%d and gid=%d',$uid,0)->field('shopid')->limit($pageOffset,$perPage)->select();
		if (!$rel) {
			return false;
		}
		$Shop = D('Common/ShopInfo');
		foreach ($rel as $key=>$value){
			$rel[$key]=$Shop->listInfo($pageOffset, $perPage, $sort,$value['shopid']);
			$rel[$key][0]['ctime'] = transDate($rel[$key][0]['ctime']);
// 			$telStatus = $rel[$key][0]['login_phone'];
// 			if ($telStatus==''){
// 			    $rel[$key][0]['phonestatus'] = 0;
// 			}else {
// 			    $rel[$key][0]['phonestatus'] = 1;
// 			}
			
		}
		return $rel;
	}
	
	
	//===================
	/**
	 * 检查 该用户 是否已经 收藏 该约课====约课详情中也用到了
	 * @param number $uid
	 * @param number $gid
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function checkUserBygidEict($uid=0,$gid=0){
		$check = $this->where('uid=%d and gid=%d',$uid,$gid)->find();
		return $check;
	}
	
}