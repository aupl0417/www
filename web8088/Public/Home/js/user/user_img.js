var $img = $(".user_my_info_img_wrap_img");
$img.click(showUserImage);

function showUserImage () {
    var $frame = $("#user-img-bg");
    $frame.removeClass("hidden");
    $frame.find("img").attr("src", this.src);
    $frame.click(function() {
        $(this).addClass("hidden");
    });
}