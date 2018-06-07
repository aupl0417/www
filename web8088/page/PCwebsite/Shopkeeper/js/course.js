(function($){
var pics={},
	size=0;
 pics=$.ajax({
  url: "/test/banner.json",
  async: false
}).responseText; 

pics = JSON.parse(pics);
var defaults = {
	'banner':'#banner',
	'pics_wrap':'.banner-list',
	"pics":'.banner-list>li',
	'pic_size':1
}

//主轮播图
var banner = $(defaults.banner);
//轮廓
var pics_wrap = $(defaults.pics_wrap);

init();
function init(){
	banner.attr('src',pics.banner[0].big);
  for( tiny of pics.banner){
  	pics_wrap.append(
  		'<li><img src='+tiny.small+' alt="banner1" data='+tiny.big+' class="banner-small"></li>'
  	);
  }
}

$(defaults.pics).bind('mouseover',picChange);

//获取首页大图json
function picChange(e){
 var temp=$(e.currentTarget).children().eq(0);
 banner.attr('src',temp.attr('data'));
 banner.addClass('fade-out');
 setTimeout(function(){
banner.removeClass('fade-out');
 },500);
 
}

})(jQuery);