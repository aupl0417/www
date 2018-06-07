<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author yuan
 *
 */

class CategoryModel extends CommonModel {

	//定义字段
	protected $fields = array('id', 'catename', 'byname','parent_id','desc','logo','thumb_logo','depth');
	protected $pk     = 'id';

	//验证
	protected $_validate=array(
			array('catename','require','分类菜单名称不能为空'),
			array('parent_id','require','选择一个父级栏目'),
			//array('catename','','分类菜单名称已经存在！',0,'unique',1), // 在新增的时候验证catename字段是否唯一
			array('desc', '2,50', '描述信息长度必须在2-50位之间！', 2, 'length', 3)
	);


	/*
	 * 获取所有分类信息的数据
	* @param  int $stop_id，需要终止查询的分类的ID
	* @return array 成功返回一个调用递归函数的数组
	*/
	public function getCategories($stop_id=0){
	    //要查询的字段
	    $array = array(
	        'id',
	        'catename',
	        'byname',
	        'thumb_logo',
	        'depth'
	    );
		$data=$this
		->field($array)
		//->order('sort desc')
		->select();

		//返回数据，调用无限极分类的方法
		return $this ->getTree($data,0,$stop_id);

	}

	// 无限级分类,生成树状结构
	public function getTree($data, $cid = 0,$stop_id=0) {
		static $tree = array ();
		foreach ( $data as $key => $value ) {
			if ($value ['parent_id'] == $cid) {
				//当查询到的ID不等于当前的d（查询的是同一个ID）
				if($value['id']!=$stop_id){
				$tree [] = $value;
				$this->getTree ( $data, $value ['id'],$stop_id);
				}
			}
		}
		return $tree;
	}

	/**
	 * 分类菜单的添加
	 * @return boolean 添加成功返回true，添加失败返回false
	 */
	public function checkInsert() {
		 if (!IS_POST) {
			return false;
		}
		C('TOKEN_ON', false);
		$upload = new \Think\UploadFile();// 实例化上传类
			$upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  './Public/Uploads/cate/';// 设置附件上传目录
            $upload->thumb=true;//表示生成缩略图
            $upload->thumbMaxWidth='100,230';//指定缩略图 的宽度
            $upload->thumbMaxHeight='100,230';//指定缩略图的高度
            $upload->thumbPrefix="thumb_,img_";
            $upload->autoSub=true;
            $upload->subType='date';
            $upload->dateFormat='Ymd';
            $logo=$_POST['logo'];
            if($logo!=null){
            if(!$upload->upload()) {// 上传错误提示错误信息
            return $upload->getErrorMsg();
            }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            }
            $_POST['logo']=$info[0]['savename'];
            //把保存的文件名字分割为数组
            $arr=explode('/', $info[0]['savename']);
            //拼凑缩略图的前缀
            $str='thumb_'.$arr[1];
            $str1=$arr[0].'/'.$str;
            //存放缩略图的保存地址到数据库
            $_POST['thumb_logo']=$str1;
            }
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
		 * 分类菜单的修改方法
		 * @return boolean 修改成功返回true，添加失败返回false
		 */
		public function checkUpdate() {
			if (!IS_POST) {
				return false;
			}
			$id=$_POST['id'];
			C('TOKEN_ON', false);
			$upload = new \Think\UploadFile();// 实例化上传类

			$upload->maxSize  = 3145728 ;// 设置附件上传大小
			$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->savePath =  './Public/Uploads/cate/';// 设置附件上传目录
			$upload->thumb=true;//表示生成缩略图
			$upload->thumbMaxWidth='100';//指定缩略图 的宽度
			$upload->thumbMaxHeight='100';//指定缩略图的高度
			$upload->thumbPrefix="thumb_";
			$upload->autoSub=true;
			$upload->subType='date';
			$upload->dateFormat='Ymd';
			$logo=$_POST['logo'];
			if($logo!=null){
			    if(!$upload->upload()) {// 上传错误提示错误信息
			        return $upload->getErrorMsg();
			    }else{// 上传成功 获取上传文件信息
			        $info =  $upload->getUploadFileInfo();
			    }
			    $_POST['logo']=$info[0]['savename'];
			    //把保存的文件名字分割为数组
			    $arr=explode('/', $info[0]['savename']);
			    //拼凑缩略图的前缀
			    $str='thumb_'.$arr[1];
			    $str1=$arr[0].'/'.$str;
			    //存放缩略图的保存地址到数据库
			    $_POST['thumb_logo']=$str1;
			}

			//自动完成
			if (!$this->create()){
				// 如果失败 输出错误提示信息
				return $this->getError();
			}
			//数据插入数据库
			if($this->save()){
				//删除成功
				return  true ;
				}else{
					//删除失败
				return  false;
			}
		}

		/*
		 * 验证删除分类菜单有效性
		* @param int $id 要删除的分类的ID
		* @return mixed 成功返回true ,失败返回false
		*/
		public function isDelete($id){
			$row=$this->field("id,parent_id")
			->where('parent_id=%d',$id)
			->limit(1)
			->find();
			if($row){
				//当前不是末级分类（不能删除）
				return  false;
			}else{
				//没有子分类
				return  true;
			}
		}

		/*
		 * 删除分类菜单
		* @param int $id 要删除的分类的ID
		* @return mixed 成功返回true ,失败返回false
		*/
		public function cateDelete($id){
			$row = $this->field('logo,thumb_logo')->find ($id);
			if($this->delete($id)){
					//删除成功
				unlink ( './Public/Uploads/cate/' . $row ['logo'] );
				unlink ( './Public/Uploads/cate/' . $row ['thumb_logo'] );
				return  true ;
			}else{
				//删除失败
				return  false ;
		      }

		}

//--------------------@author user----------获取该分类下的3级以内的所有分类id
        public function getCateListByCateid($cateid=0,$catelist=array()){
            $catelist = $this   -> field('id,parent_id,depth')
                                -> select();
            $listarray=array();
            foreach ($catelist as $ckey=>$cvalue){
                if ($cvalue['parent_id']==$cateid){
                        $listarray[]=$cvalue['id'];
                }
                if ($cvalue['id']==$cateid){
                    $nowCatePid=$cvalue['parent_id'];
                }
            }
            $listarrayAll=array();
            foreach ($catelist as $cckey=>$ccvalue){
                foreach ($listarray as $twokey=>$twovalue){
                    if ($ccvalue['parent_id']==$twovalue){
                        $listarrayAll[]=$ccvalue['id'];
                    }
                }
            }

            $listarrayAllo=array();
            foreach ($catelist as $ccckey=>$cccvalue){
                foreach ($listarrayAll as $threekey=>$threevalue){
                    if ($cccvalue['parent_id']==$threevalue){
                        $listarrayAllo[]=$cccvalue['id'];
                    }
                }
            }
            
//             $listarrayAlloo = array();
//             foreach ($catelist as $cccckey=>$ccccvalue){
//                 foreach ($listarrayAllo as $fourkey=>$fourvalue){
//                     if ($ccccvalue['parent_id']==$fourvalue){
//                         $listarrayAlloo[]=$ccccvalue['id'];
//                     }
//                 }
//             }
            
//             $listarrayAllooo = array();
//             foreach ($catelist as $ccccckey=>$cccccvalue){
//                 foreach ($listarrayAlloo as $fivekey=>$fivevalue){
//                     if ($cccccvalue['parent_id']==$fivevalue){
//                         $listarrayAllooo[]=$cccccvalue['id'];
//                     }
//                 }
//             }
            
            
            $allListCate=array();
            $allListCates=array();
            $allListCate=array_merge($listarray,$listarrayAll);
            $allListCates=array_merge($allListCate,$listarrayAllo);

//             $allListCatess=array_merge($allListCates,$listarrayAlloo);
//             $allListCatesss=array_merge($allListCatess,$listarrayAllooo);
            
            $allListCates[]=$cateid;
            $allListCates[]=$nowCatePid;
            return $allListCates;


        }


//=======================================
		/*
		 * 后台分类下的数据
		 */
		public function getCateData($pageOffset,$perPage,$sort='desc'){
		    $alias = 'cate';//定义当前数据表的别名
		    //要查询的字段
		    $array = array(
		        'cate.id',//用户id
		        'cate.catename',
		        'cate.thumb_logo',
		    );
		    $order='id '.$sort;
		    $res=$this  -> alias($alias)
		    -> order($order)
		    -> limit($pageOffset,$perPage)
		    -> field($array)
		    -> select();
		    $ShopInfo=D('ShopInfo');
		    $ShopkeeperDetail=D('ShopkeeperDetail');
		    foreach ($res as $key=>$value){
		        $cateid=intval($value['id']);
		        $res[$key]['bmcount'] = $ShopInfo->where("cateid='$cateid'")->sum('number');
		        $res[$key]['sjcount'] = $ShopkeeperDetail->where("cateid='$cateid'")->count('cateid');
		    }
		    return $res;
		}

		/**
		 * 对 各个分类数据的信息进行分页
		 * @param unknown $curPage
		 * @param unknown $perPage
		 * @return Ambigous <\Common\Util\multitype:number, multitype:number >
		 */
		public function cateDataPage($curPage,$perPage){
		    $count= $this->count('id'); // 查询总记录数
		    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		    $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		    $pageArray=$Page->getCounts();
		    return  $pageArray;
		}



		/*
		 * 取出所有的分类，
		 */
		public function getAllCateInfo(){
		    $dataInfo = $this->select();
		    foreach ($dataInfo as $key1=>$value1){
		        if ( $value1['parent_id']==0){
		            $info[] = $value1;
		        }
		    }
		    foreach ($info as $key2=>$value2){
		        foreach ( $dataInfo as $key21=>$value21){
		            if ( $value21['parent_id']==$value2['id'] ){
		                $info[$key2]['catemenu'][] = $value21;
		            }
		        }
		    }
		    foreach ($info as $key3=>$value3){
		        foreach ( $value3['catemenu'] as $key31=>$value31){
		            foreach ( $dataInfo as $key32=>$value32){
    		            if ( $value32['parent_id']==$value31['id'] ){
    		                $info[$key3]['catemenu'][$key31]['catemenu'][] = $value32;
    		            }
		            }
		        }
		    }
		    return $info;
		}

        /**
         * 取出两级以内的所有分类
         */
        public function getAllTwoLevel() {
            // 要获取的字段
            $fields = [
                'id',
                'catename',
                'parent_id',
            ];

            // 获取第一级的分类
            $firstLevel = $this->field($fields)
                               ->where('parent_id = 0')
                               ->select();

            // 获取第一级分类id
            $firstLevelId = [];
            foreach ($firstLevel as $row) {
                $firstLevelId[] = $row['id'];
            }

            // 获取第二级的分类
            $secondLevel = $this->field($fields)
                                ->where('parent_id in (%s)', implode(',', $firstLevelId))
                                ->select();

            // 拼接最终数组
            $allLevel = [];
            foreach ($firstLevel as $key => $row) {
                $allLevel[$key][] = $row;
                // 取出子项
                foreach ($secondLevel as $subRow) {
                    if ($subRow['parent_id'] == $row['id']) {
                        $allLevel[$key][] = $subRow;
                    }
                }
            }

            return $allLevel;
        }

        /**
         * 获取价钱导航栏的数据
         */
        public function getCateNav($arr) {
            $newArr = array();

            // 处理一级分类
            for ($i = 0; $i < count($arr); $i++) {
                if ($arr[$i]['depth'] == 0) {
                    $newArr[] = array($arr[$i]);
                    //unset($arr[$i]);
                }
            }

            // 处理二级分类
            for ($i = 0; $i < count($newArr); $i++) {
                for ($j = 0; $j < count($arr); $j++) {
                    if ($newArr[$i][0]['id'] == $arr[$j]['parent_id']) {
                        $newArr[$i][] = array($arr[$j]);
                        //unset($arr[$j]);
                    }
                }
            }

            // 处理三级分类
            for ($i = 0; $i < count($newArr); $i++) {
                for ($j = 1; $j < count($newArr[$i]); $j++) {
                    for ($z = 0; $z < count($arr); $z++) {
                        if ($newArr[$i][$j][0]['id'] == $arr[$z]['parent_id']) {
                            $newArr[$i][$j][] = $arr[$z];
                            //unset($arr[$z]);
                        }
                    }
                }
            }

            return $newArr;
        }

        /**
         * 获取最多三级分类，根据Id
         */
        public function getThreeLevelByLastId($cateid) {
            $fields = array(
                'one.id id_0',
                'one.catename catename_0',

                'two.id id_1',
                'two.catename catename_1',

                'three.id id_2',
                'three.catename catename_2',
            );

            $resArr =  $this->field($fields)
                            ->alias('three')
                            ->join('left join __CATEGORY__ two on two.id = three.parent_id')
                            ->join('left join __CATEGORY__ one on one.id = two.parent_id')
                            ->where('three.id = %d', $cateid)
                            ->find();

            if ($resArr === false || $resArr === null) {
                return array();
            }

            $newArr = array();
            for ($i = 0; $i < 3; $i++) {
                if (!$resArr['id_'.$i]) {
                    continue;
                }
                $newArr[] = array(
                    'id'        =>  $resArr['id_'.$i],
                    'catename'  =>  $resArr['catename_'.$i],
                );
            }

            return $newArr;
        }

}
