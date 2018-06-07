/**
 * 
 */

function imgonerror(){
	var imgs = document.images; 
	for(var i = 0;i < imgs.length;i++){ 
	  imgs[i].onerror = function(){ 
//	      this.src = "/Public/Home/img/iPhone.png"; 
	      this.src="http://17yueke.cn/Public/Uploads//shop_environ/2015/06/09/5576b998a784b.jpg";
	  } 
	} 
}
imgonerror();