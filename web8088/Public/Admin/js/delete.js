
		$().ready(function(){
		$('.id').bind('click',function(){
			//获取当前要删除的分类名称
			var $catename=$(this).attr('title');
			var conf=confirm('你确定删除  '+$catename+'分类   嘛?');
			//判断为真删除时
			if(conf==true){
				//获取当前要删除的分类id
				var $id=$(this).attr('id');
				//验证合法性
				if($id==0){
					alert('没有要删除的数据!');
					return false;
				}
				//把当前添加的信息发送PHP处理								
				var data={
				id:$id
			}
			
			$.post(deleteURL,data,function(msg){
				if(msg==true){
					alert('删除分类成功！');
					window.location.href=listURL;
				}
				if(msg==false){
					alert('删除分类失败！');
				}
				if(msg==-1){
					alert('不能删除，原因是当前不是末级分类！');
				}
			},'text');
			}
			//判断为假
			if(conf==false){
				return false;
			}
		});
		
		});		