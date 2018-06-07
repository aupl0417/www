<?php
namespace Common\Model;
use Common\Model\CommonModel;


/**
 *
 * @author user
 *
 */
class UserVModel extends CommonModel {
	
	//验证
	protected $_validate=array(
	
	);
	protected $_map = array(         
			'path' =>'vpath', // 把表单中path映射到数据表的vpath字段       
	);
	
	protected $_auto = array (
			array('datatime', 'current_datetime', 1, 'function')
	);
	
	/**
	 * 上传用户个人认证图片and 社团
	 * @return string
	 */
	public function uploadFileV(){
    	$user_v=C('user_v');
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     5145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
		$upload->mimes     = array('image/jpg', 'image/jpeg', 'image/png', 'application/x-MS-bmp', 'image/nbmp', 'image/vnd.wap.wbmp');
    	$upload->rootPath  =     '.'.$user_v.'/'; // 设置附件上传目录    
    	$upload->savePath  =     '';
    	$upload->autoSub = true; 
    	$upload->subName = array('date','Y/m/d');
		$info   =   $upload->uploadOne($_FILES['file']);
		
		if(!$info) {// 上传错误提示错误信息    
			return array($upload->getError(),0);
		}else{// 上传成功 获取上传文件信息     
			$path = '/Public/Uploads/userv/'.$info['savepath'].$info['savename']; 
			return array($path,1);    
		}
	}
	
	/**
	 * 保存个人申V材料的信息,图片
	 * @return string|boolean
	 */
	public function pathFileV($uid=0,$vtype=1){
		if (!$this->create()) {
			return $this->getDbError();
		}
		$this->uid=$uid;
		$this->status=0;
		$this->vtype=$vtype;
		$checktype=$this->selFileVid($uid,$vtype);
		if ($checktype) {
		    $this->delFileV($checktype);
		}
		if (!$this->filter('strip_tags')->add()) {
			return $this->getDbError();
		}
		return true;
	}
	
	
	/**
	 * 删除申请大V的资料，数据库中的
	 * @return string|boolean
	 */
	public function delFileV($id=0){
		$rel = $this->where('id=%d',$id)->delete();
		if (!$rel) {
			return $this->getDbError();
		}
		return true;
	}
	/**
	 * 物理删除大V认证文件
	 * @param string $path
	 * @return string|boolean
	 */
	public function unlinkfile($path=''){
		$path='.'.$path;
		$result=unlink($path);
		if ($result!==true) {
			return $this->getError();
		}
		return true;
	}

	/**
	 * 获取申V的资料的信息一条 id
	 * @param number $uid
	 * @return number|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
	 */
	public function selFileVid($uid=0,$vtype=1){
	    $rel=$this->where("uid=%d and vtype=%d",$uid,$vtype)->getField('id');
	    if (!$rel) {
	        return $rel;//0没有查找到该记录
	    }
	    return $rel;
	}
	/**
	 * 获取申V的资料的信息一条 地址
	 * @param number $uid
	 * @return number|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
	 */
	public function selFileV($uid=0,$vtype=1){
		$rel=$this->where("uid=%d and vtype=%d",$uid,$vtype)->getField('vpath');
		if (!$rel) {
			return $rel;//0没有查找到该记录
		}
		return $rel;
	}
	
	/**
	 * 查询userv的信息---后台也用到
	 * @param number $uid
	 * @return Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
	 */
	public function uservinfo($uid=0){
	    $rel=$this->where("uid=%d",$uid)->find();
		if (!$rel) {
			return $rel;//0没有查找到该记录
		}
		return $rel;
	}
	

	
	/**
	 * 获取某一用户是否个人申V通过
	 * @param number $uid
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function yesnoV($uid=0){
		$rel = $this->where('uid=%d and vtype=%d',$uid,1)->getField('vstatus');
		if (!$rel) {
			return false;
		}
		return $rel;
	}
	
	
//================admin后台=========	
    /**
     * 后台查看用户申V的信息，根据申V的类型，1个人，2社团
     * @param string $vtype
     * @param number $curPage
     * @param number $perPage
     * @param string $sort
     * @param number $status其中10表示查询不分状态
     * @return unknown
     */
	public function userAuthen($vtype=0,$curPage=0,$perPage=2,$sort='asc',$status=10){
	    if ($status==2){
	        $status=0;
	    }
	    $pageV=$this->userVpage($curPage,$perPage,$vtype,$status);
	    if ($pageV['counts']==0){
	        return false;
	    }
	    $alias='uv';
	    //要查询的字段
	    $array = array(
	        'uv.id',
	        'uv.uid',
	        'uv.vpath',
	        'uv.vstatus',
	        'uv.vtype',
	        'uv.datatime',
	        'u.firstname',
	        'u.lastname',
	        'u.phone',
	        'u.email',
	    );
	    $join  = array(
	        '__USER__ u on u.id = uv.uid',
	    );
// 	    if ($status!=10&&$vtype!=0){
// // 	        $where='uv.vtype='.$vtype.' and uv.vstatus='.$status;
// 	        $where='uv.vtype=1 and uv.vstatus=0';
// 	    }elseif ($status==10){
// 	        $where='uv.vtype='.$vtype;
// 	    }else {
// 	        $where='uv.vstatus='.$status;
// 	    }
	    
	    if ($status==0){
	        $where='uv.vtype='.$vtype.' and uv.vstatus=0';
	    }elseif ($status==1){
	        $where='uv.vtype='.$vtype.' and uv.vstatus=1';
	    }elseif ($status==10){
	        $where='uv.vtype='.$vtype;
	    }
	    
	    
	    $order = 'uv.datatime '.$sort;
	    $authen=$this   -> alias($alias)
    	                -> join($join)
    	                -> where($where)
    	                -> order($order)
                	    -> limit($pageV['pageOffset'],$pageV['perPage'])
                	    -> field($array)
                	    -> select();
	    $authenarray['authen']=$authen;
	    $authenarray['page']=$pageV;
        return $authenarray;
	}
	

	/**
	 * 返回总用户V数,根据申V的类型
	 * $vtypecount=0表示空，1表示个人，2表示社团
	 * @param number $vtypecount
	 * @return $res
	 */
	public function userVcount($vtypecount=0,$status=10){
	    if ($vtypecount!=0&&$status!=10){
	        $res = $this->where('vtype=%d and vstatus=%d',$vtypecount,$status)->count();
	    }elseif ($status==10){
	        $res = $this->where('vtype=%d',$vtypecount)->count();
	    }else {
	        $res = $this->where('vstatus=%d',$status)->count();
	    }
	    return  $res;
	}
	
	/**
	 * 用户V分页数据
	 * 返回分页信息
	 * @param number $curPage
	 * @param number $perPage
	 * @param number $type
     * @param number $status其中10表示查询不分状态
	 * @return array $page
	 */
	public function userVpage($curPage=1,$perPage=5,$type=0,$status=10){
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	    $count= $this->userVcount($type,$status); // 查询总记录数
	    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	    $pageArray=$Page->getCounts();
	    return  $pageArray;
	}
	
	/**
	 * 修改用户申v的状态
	 */
	public function userPassV($uid=0,$vtype=0,$status=0){
	    $result = $this->where('uid=%d and vtype=%d',$uid,$vtype)->setField('vstatus',$status);
	    if (!$result){
	        return $this->getDbError();
	    }
	    return $result;
	}
	
	
	
}