<?php 
class Tree { 
   
	/** 
	* 从数据库查询出的所有分类信息 
	* @var array 
	*/
	var $arr; 
	/** 
	* 如下格式 
	*  var $arr = array( 
	1 => array('id'=>'1', 'parentid'=>0, 'name' => '一级栏目一'), 
	2 => array('id'=>'2', 'parentid'=>0, 'name' => '一级栏目二'), 
	3 => array('id'=>'3', 'parentid'=>1, 'name' => '二级栏目一'), 
	);*/
 
	/** 
	* 输出结构 
	* @var array 
	*/
	var $tree = array(); 
	/** 
	* 树形递归的深度 
	* @var int 
	*/
	var $deep = 1;
	
	/** 
	* 生成树形的修饰符号 
	* @var array 
	*/
	var $icon = array('│','├','└');
	
	/** 
	* 生成指定id的下级树形结构 
	* @param int $rootid 要获取树形结构的id 
	* @param string $add 递归中使用的前缀 
	* @param bool $parent_end 标识上级分类是否是最后一个 
	*/
	function getTree($rootid = 0,$add = ”,$parent_end =true){ 
		$is_top = 1; 
		$child_arr = $this->getChild($rootid);
		if(is_array($child_arr)){
			$cnt = count($child_arr);
			foreach($child_arr as $key => $child){
				$cid = $child['id'];
				$child_child = $this->getChild($cid);
				if($this->deep >1){
					if($is_top == 1 && $this->deep > 1){
						$space = $this->icon[1];
						if(!$parent_end)
							$add .=  $this->icon[0];
						else
							$add .= '&nbsp;&nbsp;';
					}
					
					if($is_top == $cnt){
						$space = $this->icon[2];
						$parent_end = true;
					}else {
						$space = $this->icon[1];
						$parent_end = false;
					}
				}
				$this->tree[] = array('spacer'=>$add.$k.$space, 'name'=>$child['name'], 'id'=>$cid);
				$is_top++;
				
				$this->deep++;
				if($this->getChild($cid))
					$this->getTree($cid,$add,$parent_end);
				$this->deep –-;
			}
		}
		
		return $this->tree; 
	}
	
	/** 
	* 获取下级分类数组 
	* @param int $root 
	*/
	function getChild($root = 0){
		$a = $child = array();
		foreach($this->arr as $id=>$a){
			if($a['parentid'] == $root){
				$child[$a['id']] = $a;
			}
		}
		
		return $child?$child:false;
	}
	
	/** 
	* 设置源数组 
	* @param $arr 
	*/
	function setArr($arr = array()){
		$this->arr = $arr;
	}
}