$().ready(function(){
	
		//重置
		$('#btn').bind('click',function(){			
			$('#catename').val('');
			$('#desc').val('');
			$('#parent_id').val(0);
		});
		
			
		$('[node="date"]').datepicker({
			autoclose: true,
			format: "yyyy-mm-dd"
		});
		
});		