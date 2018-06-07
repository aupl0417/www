<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */
class UsercommentModel extends CommonModel {
	
	//验证
	protected $_validate=array(

			array('info', 'require', '评论不能为空！', 1),
			array('info', '1,50','评论长度必须在25个数字以内！', 2, 'length', 3),
			
	);
	
	
	
	

	/**
	 * 获取评论的信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return string
	 */
	public function usercom($pageOffset=0,$perPage=2,$sort='asc'){
		//要查询的字段
		$array = array(
				'uc.id',
				'uc.cid',
				'uc.gid',
				'uc.info',
				'uc.pid',
				'uc.ctime',
				'u.firstname',
				'u.lastname',
				'gi.title',
		);
		$alias = 'uc';//定义当前数据表的别名
		$join  = array('__USER__ u on uc.cid = u.id','__GROUP_INFO__ gi on uc.gid = gi.id');//join可以使用array
		$where = 'uc.cid=u.id AND uc.gid=gi.id';
		$order = 'uc.id '.$sort;
		$res=$this  ->alias($alias)
					->join($join)
					->where($where)
					->order($order)
					->limit($pageOffset,$perPage)
					->field($array)
					->select();
		return $res;
	}
	

	/**
	 * 返回总评论信息数
	 * @return int $res
	 */
	public function usercomcount(){
		$res = $this->order('id')->count();
		return  $res;
	}

	/**
	 * 评论分页数据
	 * 返回分页信息
	 * @param int $curPage
	 * @param int $perPage
	 * @return array $page
	 */
	public function usercompage($curPage=1,$perPage=5){
	
		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		$count= $this->usercomcount(); // 查询满足要求的总记录数
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}
	
	
}