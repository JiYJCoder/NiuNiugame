define(["swiper"], function(swiper) {
    var loadTip = function() {
    	var recordList = $(".recorded-course-tabs1 .record-list");
        recordList.each(function(index, el) {
            var smallCourse = $(this).find(".small-course");
            console.log(smallCourse)
            new Swiper(smallCourse, {
                slidesPerView: 'auto',
                spaceBetween: 15
            })
        })
    }
    return loadTip;
})