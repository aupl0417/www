(function($) {  
     $.fn.areaSelect = function(options) { 
     var defaults = {     
          url:'', 
		  ids:null,
     };      
     var opts = $.extend(defaults, options);
	 if(opts.url == '')  {
		alert('请传入url');
		return false;
	 };
	 $this = $(this);
	 $provinceob = $(this).find('#province');
	 $cityob = $(this).find('#city');
	 $areaob = $(this).find('#area');
	 if(opts.ids && (opts.ids instanceof Array)){
		addDataToSelect(opts.ids[0].id,0,opts.ids[0].selectVal); 
		addDataToSelect(opts.ids[1].id,1,opts.ids[1].selectVal);
		addDataToSelect(opts.ids[2].id,2,opts.ids[2].selectVal); 
	 }else{
		addDataToSelect(opts.ids,0); 
	 }
	 $provinceob.change(function(){
           addDataToSelect($(this).val(),1);
		   $areaob.find("option:gt(0)").remove();
     });
	 $cityob.change(function(){
           addDataToSelect($(this).val(),2);
     });
	 function addDataToSelect(id,level,selectv) {
		 $.ajax({
 				url: opts.url,
 				data: {
 					id: id
 				},
				async: false,
 				dataType: "json",
 				success: function(data) {
					var arr = ['选择省','选择市','选择区/县'];
 					var html = '<option value="">'+arr[level]+'</option>';
 					$.each(data, function(index, val) {
 						html += '<option value="'+val.a_code+'" '+(selectv && selectv == val.a_code ? 'selected="selected"' : '')+' >'+val.a_name+'</option>';
 					});
					if(level == 0) {
						$provinceob.html(html);	
					}else if(level == 1) {
						$cityob.html(html);	
					}else if(level == 2) {
						$areaob.html(html);
					}
 					
 				}
 			}); 
	 }
  };
  
})(jQuery);        