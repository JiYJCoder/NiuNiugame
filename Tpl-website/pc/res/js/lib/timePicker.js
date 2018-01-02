var timepiece = {};
timepiece.render = function($input) {
    var hourHtml = "";
    var minuteHTML = "";
    for (var i = 0; i < 24; i++) {
        if (i <= 9) {
            i = "0" + i;
        }
        hourHtml += '<button class="hourBtn">' + i + '</button>'
    }
    for (var i = 0; i < 60; i++) {
        if (i <= 9) {
            i = "0" + i;
        }
        minuteHTML += '<button class="minuteBtn">' + i + '</button>'
    }
    var html = '<div class="v_timePicker"><div class="jedateblue clearfix"><div class="jedatehmstitle">时间选择<div class="jedatehmsclose">×</div></div><div class="jedateprop"><div class="jedateproptext">小时</div><div class="jedateproptext">分钟</div></div><div class="jedatehmsconbox"><div class="jedatehmscon vedatehour">' + hourHtml + '</div><div class="jedatehmscon vdateminute">' + minuteHTML + '</div></div></div></div>'
    var $wrapper = $input.parent();
    $wrapper.append(html)

    var left = $input[0].offsetLeft;
    var top = $input[0].offsetTop;
    var height = $input[0].offsetHeight;

    var $timePicker = $wrapper.find(".v_timePicker")
    $timePicker.css({
        'top': top + height + 5 + "px",
        'left': left
    })
    $timePicker.find(".hourBtn").eq(0).addClass('active')
    $timePicker.find(".minuteBtn").eq(0).addClass('active')
}

timepiece.bind = function($input) {
    var thisTimePickBox = $input.parent();
    var hourBtn = thisTimePickBox.find(".hourBtn")
    var minuteBtn = thisTimePickBox.find(".minuteBtn")
    $input.on("click", function() {
        // thisTimePickBox = $(this).parent()
        $(".v_timePicker").hide();
        thisTimePickBox.find(".v_timePicker").fadeIn(200);
    })

    hourBtn.on("click", function() {
        $(this).addClass('active').siblings().removeClass("active")
        timepiece.fullTime(thisTimePickBox, $input)
    })
    minuteBtn.on("click", function() {
        $(this).addClass('active').siblings().removeClass("active")
        timepiece.fullTime(thisTimePickBox, $input)
        thisTimePickBox.find(".v_timePicker").slideUp(200);
    })
    $(".jedatehmsclose").on("click", function() {
        timepiece.fullTime(thisTimePickBox, $input)
        thisTimePickBox.find(".v_timePicker").slideUp(200);
    })
}


timepiece.fullTime = function(parentBox, $input) {
    // console.log(parentBox)
    var hour = parentBox.find(".hourBtn.active").html();
    var minute = parentBox.find(".minuteBtn.active").html();
    var time = hour + ":" + minute
    $input.val(time)
}


timepiece.init = function(input) {
    var hour;
    var minute;
    var $input = $(input)
    timepiece.render($input)
    timepiece.bind($input)
}
define(function() {
    return timepiece;
})
