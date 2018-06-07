<?php
namespace Admin\Controller;
use Common\Controller\CommonController;

/**
 * 
 * @author yuan
 *
 */
class AdvertController extends CommonController {
	
		//加载分类菜单信息页面
		public function adlist(){
		    //实例化模型类
		    $advert=D('Advert');
		    $curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		    $perPage=20;
    		$pageArray  = $advert->advertpage($curPage,$perPage);	//获取广告的分页
    		$data=$advert-> getAdvert($pageArray['pageOffset'],$pageArray['perPage']);
    		$maxsort=$advert-> getMaxSort();
    		$this->assign('maxsort',$maxsort);
    		$this->assign('page',$pageArray); // 赋值分页输出
		     $this->assign('data',$data);
		    $this->display();
		}
		
		//分类添加页面
		public function adadd(){
				
			
			$this->display();
		}
		
		//添加广告位方法
		public function insertAd(){
		    //实例化模型类
		    $advert=D('Advert');
		    $add=$advert->checkInsert();
		    if($add===true){
		        $this->success('添加广告位成功','adlist');
		    }else{
		        $this->error($add,'adadd');
		    }
		}
		

		//编辑分类菜单页面
		public function adedit(){
			//接收当前要编辑的分类菜单id
			$id=I('get.id',0,'intval');
			//实例化模型类
			$advert=D('Advert');
			$row=$advert->find($id);
			$adImg=$row['advert'];
			S('adimg',$adImg,60);
		//分配数据到模版文件
			$this->assign('row',$row);
			$this->display();  
		}
		
		//修改分类菜单方法
		public function Update(){
				//实例化模型类
			$advert=D('Advert');
			$save=$advert->checkUpdate();
			if($save===true){
				$this->success('修改广告位数据成功','adlist');
			}else{
				$this->error($save,'adedit');
			} 
		}
		
		//删除当前分类菜单
		public function Delete(){
			//获取要删除的ID
			$id = intval(I('post.id'));	
			//实例化模型类
			$advert=D('Advert');
				if($advert->adDelete($id)){
					//删除分类成功
					echo true;			
			}else{
				//删除失败
				echo false;
				exit;
			}				
		}
		//设置广告位
		public function setSort(){
		    //获取的ID
		    $id =I('post.id',0,'intval');
		    $sort =I('post.sort');
		    //实例化模型类
		    $advert=D('Advert');
	       $res=$advert->updateSort($id,$sort);
	       echo $res;
		}
		
		
		
}