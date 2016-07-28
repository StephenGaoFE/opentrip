//热点图切换
function slideBox(target,time){
    var sw = 0;
    $(target+" .num a").mouseover(function(){
            sw = $(target+" .num a").index(this);
            myShow(sw);
    });
    function myShow(i){
            $(target+" .num a").eq(i).addClass("curr").siblings("a").removeClass("curr");
            $(target+" ul li").eq(i).stop(true,true).fadeIn(600).siblings("li").fadeOut(600);
    }
    //滑入停止动画，滑出开始动画
    $(target).hover(function(){
            if(myTime){
               clearInterval(myTime);
            }
    },function(){
            myTime = setInterval(function(){
              myShow(sw)
              sw++;
              if(sw==3){sw=0;}
            } , time);
    });
    //自动开始
    var myTime = setInterval(function(){
       myShow(sw)
       sw++;
       if(sw==3){sw=0;}
    } , time);
};