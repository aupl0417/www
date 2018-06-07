(function($){

    /*强制修改百度uploader 里的值*/
$('.webuploader-pick').html('本地上传');

    /*存放所有localStorage里的值*/
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
//initCheck();
//事件绑定
var publish={
	"typeButton":"#ctype",
	"editButton":"#edit",
	"spMenu":"#special_menu",
	"typeMenu":".dropdown-menu-big",
	"timeMenu":"time-select-menu",
	"typeSubMenu":'.dropdown-menu-small>li',
    "major_menu":"#major_menu>li>a",
    "org_select":"#org_select",
    "org_menu":"#org_menu",
    "area_btn":"#area_btn",
    "area_one_data":"#area_one_data"
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
'content':$('#content'),
'course_model':$('#course_model'),
'course_special':$('.course-special')
};

var typeMenu = $(publish.typeMenu);
var spMenu = $(publish.spMenu);
var major_menu = $(publish.major_menu);
var org_menu = $(publish.org_menu);
    var area_one_data = $(publish.area_one_data);
$(publish.typeButton).click({param:typeMenu},HideAndShow);
$(publish.editButton).click({param:spMenu},HideAndShow);
$(publish.typeSubMenu).bind('click',getType);
 $(publish.org_select).click({param:org_menu},HideAndShow);
    $(publish.area_btn).click(function(){

    });
major_menu.click(function(e){
var item = $(e.currentTarget).text();
    $("#major").val(item);
    });

for(as in inputs){
	inputs[as].blur({param:as},getValue);
}

 /*以下为公用方法区*/

    /**
     * 隐藏显示函数
     * @param e 事件对象
     * @constructor
     */
function HideAndShow(e){
  //  console.log(e)
	 e.data.param.toggleClass('hidden');
        /*if(e.target.id=="org_select"){
            if($('#org_menu').hasClass('hidden')){
            }
        }*/
    }

    /**
     * 获取localStorage里的值
     * @param key 键
     * @returns {*} localStorage里的值
     */
function getStorageItem(key){
 var item = localStorage.getItem(key);
 return item;
}
    /**
     * 把数值存放到localStorage
     * @param key 键
     * @param value 值
     */
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

    /**
     * 清除所有localStorage里的值
     */
function clearAll(){
	for(as in Storage){
	   localStorage.removeItem(as);
}
}

})(jQuery);
