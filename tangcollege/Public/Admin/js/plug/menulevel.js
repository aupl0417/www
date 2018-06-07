(function($) {  
     $.fn.menulevel = function(options) {     
     var defaults = {     
          intervalLen:10, 
     };       
     var opts = $.extend(defaults, options);
	 if(opts.data.length == 0)  {
		alert('请传递数据');
		return false;
	 };
	 var $this = $(this);
	 var optionsel = $(this).find('.items');
	 var selectel = $this.find('.selected');
	 data = opts.data;
	 setOptionsPostion();
	 html = createHtml('',data,0);
	 optionsel.append(html);
	 function createHtml(html,data,level) {
	     for(var i in data) {
			 if(level == 0) {
				 html += '<li value="'+data[i].id+'">'+data[i].name+'</li>';
			 }else{
				 html += '<li value="'+data[i].id+'" style="padding-left:'+level*20+'px;"><span>'+data[i].name+'</span></li>';
			 }
			 if(typeof(data[i].list)!= "undefined" && data[i].list.length != 0) {
				html = createHtml(html,data[i].list,level+1); 
			 }	 
			 
		 }
	     return html;
    }
	function setOptionsPostion() {
		pos = selectel.offset();
		newPos=new Object();
        newPos.left=pos.left + $(document).scrollLeft();
        newPos.top=pos.top + 4 + $(document).scrollTop();
        optionsel.offset(newPos);
	}
	
	$(document).click(function(e){
		e = window.event || e;
        obj = $(e.srcElement || e.target);
        if (!$(obj).is(".menu-level .selected")) { 
           optionsel.hide();
        } 
    });
	
    optionsel.find('li').click(function(){
		var v= $(this).attr('value');
		var t = $(this).children('span').length > 0 ? $(this).children('span').html() : $(this).html();
		selectel.html(t);
		$this.find('#menu-level-value').val(v);
		if(typeof(opts.callBack) == 'function') {
			opts.callBack(v,t);
		}
        toggle();
    });
	selectel.click(function(){
        toggle();
    });

	function toggle() {
		if(optionsel.is(':visible')) {
			optionsel.hide();
		}else{
			optionsel.show();
		}
		
	}
  };
  
})(jQuery);        