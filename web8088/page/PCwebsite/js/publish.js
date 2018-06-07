(function($){

$('button').click(function(e){
	e.preventDefault();
});
$('.webuploader-pick').html('本地上传');
//初始化检查
var Storage={
  'course_type':getStorageItem('course_type'),
  'course_type_name':getStorageItem('course_type_name'),
  'ctype':getStorageItem('course_type'),
  'course_price':getStorageItem('course_price'),
  'course_cheap':getStorageItem('course_cheap'),
  'course_time':getStorageItem('course_time'),
  'course_special':getStorageItem('course_special'),
  'special_menu':getStorageItem('special_menu'),
  'day':getStorageItem('day'),
  'weekday':getStorageItem('weekday'),
  'teac_year':getStorageItem('teac_year'),
  'teac_exper':getStorageItem('teac_exper'),
  'teac_info':getStorageItem('teac_info'),
  'tead_spec':getStorageItem('tead_spec'),
  'title':getStorageItem('title'),
  'content':getStorageItem('content'),
  'address':getStorageItem('address'),
  'phone':getStorageItem('phone')
}
console.log(Storage);
initCheck();
//事件绑定
var publish={
	"typeButton":"#ctype",
	"editButton":"#edit",
	"spMenu":"#special-menu",
	"typeMenu":".dropdown-menu-big",
	"timeMenu":"time-select-menu",
	"typeSubMenu":'.dropdown-menu-small>li'
};

//这里存放所有input 类型对象
var inputs = {
'course_price':$('#course_price'),
'course_cheap':$('#course_cheap'),
'teac_year':$('#teac_year'),
'teac_exper':$('#teac_exper'),
'tead_spec':$('#tead_spec'),
'teac_info':$('#teac_info'),
'title':$('#title'),
'address':$('#address'),
'phone':$('#phone'),
'content':$('#content')
};

var typeMenu = $(publish.typeMenu);
var spMenu = $(publish.spMenu);

$(publish.typeButton).click({param:typeMenu},HideAndShow);
$(publish.editButton).click({param:spMenu},HideAndShow);
$(publish.typeSubMenu).bind('click',getType);

for(as in inputs){
	inputs[as].blur({param:as},getValue);
}
//隐藏显示
function HideAndShow(e){
	 e.data.param.toggleClass('hidden');
	/*$(e.currentTarget).next().toggleClass('hidden');*/
}

//存储
function getStorageItem(key){
 var item = localStorage.getItem(key);
 return item;
}

//取值
function setStorageItem(key,value){
	localStorage.setItem(key,value);
}

function getType(e){
  var index = $(e.currentTarget).find('a').eq(0).attr('index');
  var name = $(e.currentTarget).find('a').eq(0).html();
  setStorageItem('course_type',index);
  setStorageItem('course_type_name',name);
}

function getValue(e){
	var a = $(e.currentTarget);
	setStorageItem(e.data.param,a.val());
}

function initCheck(){
	for(as in Storage){
		if(Storage[as]!=null){
	$('#'+as).val(Storage[as]);
	}
	}
	if(Storage.course_type!=null){
	$('#course_type').val(Storage.course_type_name);
	}
}

function clearAll(){
	for(as in Storage){
	   localStorage.removeItem(as);
}
}

})(jQuery);


