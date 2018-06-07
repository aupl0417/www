/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    $('.identify_file').click(function () {
        $(".identify_input").trigger('click');
    });

    $("#file").change(function(){
        var objUrl = getObjectURL(this.files[0]) ;
        if (objUrl) {
            $("#img0").attr("src", objUrl) ;
            $("#text").val(1);
        }
    });


    function uploadfile(){
        var imgcheck=$("#text").val();
        if(imgcheck==0){
            easyAlert('请上传认证材料');
            return false;
        }
        document.getElementById('uploadfile').submit();
        return true;
    }
    $("#shrtuanshenv").click(function(){
        uploadfile();
    });


    function getupload(){
        /*	var files = document.getElementById("file").files
         if (files.length <= 0) {
         return alert("请添加培训机构场景图")
         }
         var args = {
         "cateid":		cateid,
         "phone_tel":	phoneTel,
         "area_detail":	areaDetail,
         "price":		price,
         "mode":			mode,
         "tags":			tags,
         "title":		title,
         "content":		content,
         "areaid":		areaid,
         }
         // 这里拼接字符串
         var data = new FormData();
         for (var i in args) {
         data.append(i, args[i])
         }
         data.append("file", files[0])
         // 开始上传信息
         */$.ajax({
            url: addcourse_url,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR)
            {
                if (data.status != "200") {
                    return easyAlert(data.msg)
                }
                location.href = success_url;
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                easyAlert('ERRORS: ' + textStatus);
                // STOP LOADING SPINNER
            }
        });


    }

    if(error_img==1){
        window.onload=function(){
            easyAlert('认证资料上传成功');
            setTimeout(function () {
                window.location.href=success_upload_url;

            },2000);
        }
    }else if(error_img!=0){
        window.onload=function(){
            easyAlert(error_img);
            setTimeout(function () {
                window.location.href=success_upload_url;

            },2000);	}
    }
})();