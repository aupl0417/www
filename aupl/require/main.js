require.config({
    paths: {
        "jquery": 'jquery'
    }
});

require(["jquery"], function ($) {
    console.log('aa');
});

require(['jquery'],function(){
    //this thing shows 'Uncaught TypeError: undefined is not a function'
    // var jquery = $.noConflict();
    console.log('bb');
    $("#content").html("jquery am  loaded");
});