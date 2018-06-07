<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 
 * @author yuan---后期修改由user
 *
 */

class AdvertModel extends CommonModel {
	
	//定义字段
	protected $fields = array('id', 'adname', 'advert','url','ctime','otime','sort','status');
	protected $pk     = 'id';
	
	//验证
	protected $_validate=array(
			array('adname','require',' 广告位名称不能为空'),
	);
	
	/**
	 * 广告位的显示
	 * @return array 成功返回一个调用递归函数的数组
	 */
	public function getAdvert($pageOffset=0,$perPage=3){
	    $data=$this->limit($pageOffset,$perPage)->select();

	    return $data;
	}
	/**
	 * 广告位的添加
	 * @return boolean 添加成功返回true，添加失败返回false
	 */
	public function checkInsert() {
		 if (!IS_POST) {
			return false;
		}
		C('TOKEN_ON', false);
		$upload = new \Think\UploadFile();// 实例化上传类		
			$upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  './Public/Uploads/cate/';// 设置附件上传目录
            $upload->autoSub=true;
            $upload->subType='date';
            $upload->dateFormat='Ymd';
            if(!$upload->upload()) {// 上传错误提示错误信息
                return $upload->getErrorMsg();
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
            }
            $_POST['advert']=$info[0]['savename'];
     
		//自动完成
		if (!$this->create()){
		// 如果失败 输出错误提示信息
			return $this->getError();
		}
		//数据插入数据库
			 if($this->add()){
			     
				return true;
			}
		}

		/**
		 * 广告位的修改方法
		 * @return boolean 修改成功返回true，添加失败返回false
		 */
		public function checkUpdate() {
			if (!IS_POST) {
				return false;
			}
			C('TOKEN_ON', false);
			$upload = new \Think\UploadFile();// 实例化上传类
			
			$upload->maxSize  = 3145728 ;// 设置附件上传大小
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->savePath =  './Public/Uploads/cate/';// 设置附件上传目录
			$upload->autoSub=true;
			$upload->subType='date';
			$upload->dateFormat='Ymd';
			$advert=$_POST['advert'];
			if($advert!=null){
			if(!$upload->upload()) {// 上传错误提示错误信息
				return $upload->getErrorMsg();
			}else{// 上传成功 获取上传文件信息
				$info =  $upload->getUploadFileInfo();
			}
			$_POST['advert']=$info[0]['savename'];
			//自动完成
			}
			if (!$this->create()){
				// 如果失败 输出错误提示信息
				return $this->getError();
			}
			//数据插入数据库
			if($this->save()){
			     $adimg=S('adimg');
			    unlink ( './Public/Uploads/cate/' . $adimg );
				//删除成功 
				return  true ;
				}else{
					//删除失败
				return  false;
			}
		}
		
		
		/*
		 * 删除分类菜单
		* @param int $id 要删除的分类的ID
		* @return mixed 成功返回true ,失败返回false
		*/
		public function adDelete($id){
			$row = $this->field('advert')->find ($id);
			if($this->delete($id)){			
					//删除成功
				unlink ( './Public/Uploads/cate/' . $row ['advert'] );
				return  true ;		
			}else{
				//删除失败
				return  false ;						
		}	
			
		}
		
		/*获取设置位首页的广告位  */
		public function getIndexAdvert(){
		    $time=time();
		   $row = $this
		   ->field('advert,url,adname')
		   ->where("otime>'$time'")
		   ->order('sort desc')
		   ->limit(1)
		   ->cache(true,600)
		   ->find ();
		   return $row;
		}
		
		
		/*设置sort为最大排序 */
		public function updateSort($id,$sort){
	
		    $res=$this-> where('id=%d',$id)->setField('sort',$sort);
	       if($res){
	           return true;
	       }else{
	           return false;
	       }
		}
		
		/*获取最大排序 */
	public function getMaxSort(){
		    $row = $this
		  ->max('sort');
		    return $row;
		} 
		
		
		/**
		 * 返回总的广告位数
		 * @return $res
		 */
		public function advertcount(){
		    $res = $this->order('id')->count();
		    return  $res;
		}
		
		
		/**
		 * 用户分页数据
		 * 返回分页信息
		 * @param number $curPage
		 * @param number $perPage
		 * @return array $page
		 */
		public function advertpage($curPage=1,$perPage=5){
		    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		    $count= $this->advertcount(); // 查询总记录数
		    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		    $pageArray=$Page->getCounts();
		    return  $pageArray;
		}
		
		
		
		
//---------------------------------------电脑端---广告展示------------------------------------------------------------------------	
		
		
	/**
	 * position表示展示的位置，501表示网站首页
	 * @param unknown $position
	 * @return boolean|unknown
	 */
	public function getBarInfo( $position ){
	    $where['position'] = array('EQ',$position);
	    $where['status'] = array('EQ',1);
	    $data = $this  -> where($where)
	                   -> select();
	    if (!$data){
	        return false;
	    }
	    return $data;
	}
		
		
		
		
		
		
}