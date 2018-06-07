<?php
namespace Admin\Controller;
use Common\Controller\CommonController;

/**
 * 
 * @author yuan
 *
 */
class CategoryController extends CommonController {
	
		//加载分类菜单信息页面
		public function cate_list(){
			
			//实例化模型类
			$cate=D('Category');
			//查找分类的数据(缓存3600秒)
			$data=$cate
			->select();
			// 生成树状结构
			$data=$cate-> getTree ( $data );
			
			$this->assign('data',$data);
			$this->display();
		}
		
		//分类添加页面
		public function cate_add(){
				
			//实例化模型类
			$cate=D('Category');	
			//查找分级ID为0的一级分类的数据
			$data=$cate->field('id,parent_id,catename,depth')->select();
			// 生成树状结构
			$data=$cate-> getTree ( $data );
			//分配数据到模版文件
			$this->assign('data',$data);
			$this->display();
		}
		
		//添加分类菜单方法
		public function insertCate(){
			//实例化模型类
			$cate=D('Category');
			$add=$cate->checkInsert();
			if($add===true){
				$this->success('添加分类菜单数据成功','cate_list');
			}else{
				$this->error($add,'cate_add');
			}							
		}
		
		//编辑分类菜单页面
		public function cate_edit(){
			//接收当前要编辑的分类菜单id
			$id=intval(I('get.id'));
			//实例化模型类
			$cate=D('Category');
			$row=$cate->find($id);
			$data=$cate->getCategories($id);

			//分配数据到模版文件
			$this->assign('row',$row);
			$this->assign('data',$data);
			$this->display();
		}
		
		//修改分类菜单方法
		public function Update(){
			//实例化模型类
			$cate=D('Category');
			$save=$cate->checkUpdate();
			if($save===true){
				$this->success('修改分类菜单数据成功','cate_list');
			}else{
				$this->error($save,'cate_edit');
			}
		}
		
		//删除当前分类菜单
		public function Delete(){
			//获取要删除的ID
			$id = intval(I('post.id'));	
			//实例化模型类
			$cate=D('Category');
			//可以删除分类的条件
			//调用方法
			$res=$cate->isDelete($id);
			//可以删除分类的条件
			if($res===true){
				if($cate->cateDelete($id)){
					//删除分类成功
					echo true;
				}else{
					//删除分类失败
					echo false;
					exit;
				}
			}else{
				//不能删除（不是末级分类）
				echo -1;
				exit;
			}		
			
		}
		
		
		
		
}