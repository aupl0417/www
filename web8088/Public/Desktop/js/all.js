function FormValidator(){
    return{
        isPhone: function (str) {
            var pattern = /^1[3|4|5|7|8][0-9]\d{8}$/;
            return pattern.test(str);
        },
        isEmail: function (str) {
            var pattern = /^[\w-]+(\.[\w-]+)*@(\w)+(\.\w+)+$/;
            return pattern.test(str);
        },
        isPasswd: function (str) {
            var pattern = /^\S{6,12}$/;
            return pattern.test(str);
        },
        isBlank: function (str) {
            var pattern = /^\s*$/;
            return pattern.test(str);
        }
    };
}
/**
 * TabController. We must give config params before working
 *
 * menuId(ul element supposed),
 * itemsWrapperId,
 * itemClass,
 * eventName
 */
function TabController (config) {
	if (!config) return;
	this.config = config;
	this.menu = $('#' + config.menuId);
	this.menuLis = $('#' + config.menuId + ' li');
	this.items = $('#' + config.itemsWrapperId + ' .' + config.itemClass);
	this.eventName = config.eventName || 'mouseover';
	var that = this;
	function reset () {
		for (var i = 0; i < that.menuLis.length; i++) {
			$(that.menuLis[i]).removeClass('active');
		}
		for (var i = 0; i < that.items.length; i++) {
			$(that.items[i]).addClass('hidden');
		};
	}
	function fire (event) {
		var index = $.inArray(this, that.menuLis);
		reset();
		$(this).addClass('active');
		$(that.items[index]).removeClass('hidden');
	}
	this.menu.on(config.eventName, 'li', fire);
}

/* simple file upload preview */
function previewImage(file, maxwidth, maxheight, imgWrapper, img) {
    var MAXWIDTH = maxwidth;
    var MAXHEIGHT = maxheight;
    var imgWrapper = imgWrapper || 'preview';
    var img = img || 'imghead';
    var div = document.getElementById(imgWrapper);
    if (file.files && file.files[0]) {
        div.innerHTML = '<img id="'+ img  + '">';
        var imgNode = document.getElementById(img);
        imgNode.onload = function () {
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, imgNode.offsetWidth, imgNode.offsetHeight);
            imgNode.width = rect.width;
            imgNode.height = rect.height;
            //img.style.marginTop = rect.top + 'px';
        }
        var reader = new FileReader();
        reader.onload = function (evt) {
            imgNode.src = evt.target.result;
        }
        reader.readAsDataURL(file.files[0]);
    }
    else //兼容IE
    {
        var sFilter = 'filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
        file.select();
        var src = document.selection.createRange().text;
        div.innerHTML = '<img id="' + img + '">';
        var imgNode = document.getElementById(img);
        imgNode.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
        var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, imgNode.offsetWidth, imgNode.offsetHeight);
        status = ('rect:' + rect.top + ',' + rect.left + ',' + rect.width + ',' + rect.height);
        div.innerHTML = "<div id=divhead style='width:" + rect.width + "px;height:" + rect.height + "px;margin-top:" + rect.top + "px;" + sFilter + src + "\"'></div>";
    }
}

function clacImgZoomParam(maxWidth, maxHeight, width, height) {
    var param = {top: 0, left: 0, width: width, height: height};
    if (width > maxWidth || height > maxHeight) {
        rateWidth = width / maxWidth;
        rateHeight = height / maxHeight;
        if (rateWidth > rateHeight) {
            param.width = maxWidth;
            param.height = Math.round(height / rateWidth);
        } else {
            param.width = Math.round(width / rateHeight);
            param.height = maxHeight;
        }
    }
    param.left = Math.round((maxWidth - param.width) / 2);
    param.top = Math.round((maxHeight - param.height) / 2);
    return param;
}