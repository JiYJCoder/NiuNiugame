define(["verify", 'ajax'], function( verify, ajax) {
    var loadTip = function() {
        new Swiper('.s-sx-banner .swiper-container', {
            slidesPerView: '1',
            autoplay: 3000, //可选选项，自动滑动
            pagination: '.swiper-pagination',
            paginationClickable: true
        })
        new Swiper('.foreshow-right-box .swiper-container', {
            slidesPerView: 'auto',
            spaceBetween: 40,
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev'
        })

        $(".h-login-btn").on("click",function(){
            $(".s-sx-login").addClass('md-show');
        })
        
       

        /*功能区tabs切换*/
        var $oLi1 = $(".course-wapper1 .tab-header li")
        var $tabContain1 = $(".course-wapper1 .tab-contain")
        $oLi1.on("click",function(){
            var index = $(this).index();
            $(this).addClass('active').siblings().removeClass('active');
            $tabContain1.eq(index).show().siblings().hide();
        })

        var $oLi2 = $(".course-wapper2 .tab-header li")
        var $tabContain2 = $(".course-wapper2 .tab-contain")
        $oLi2.on("click",function(){
            var index = $(this).index();
            $(this).addClass('active').siblings().removeClass('active');
            $tabContain2.eq(index).show().siblings().hide();
        })
    }
    return loadTip;
})