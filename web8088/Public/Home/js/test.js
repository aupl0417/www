/**
 * Created by Administrator on 2015/4/14.
 */
(function() {

    $(".release_preview").click(function (e) {
        e.preventDefault();
        $('#release_box_bg').removeClass("bounceInDown");
        $('#release_box_bg').addClass("bounceInDown");
        $("#release_box_bg").removeClass("hidden");
    });
    $(".release_box_bg").click(function(e){
            e.preventDefault();
            if($(e.target).hasClass('release_box_bg')){
                $("#release_box_bg").addClass("hidden");
            }
        }
    );
}
)();