define(["layer", "ajax"], function(layer, ajax) {
    var loadTip = function() {
        var $chaBox = $(".chat-bxo");
        var $shrink = $(".shrink")
        var $broadcastLeft = $(".broadcast-left")
        var $playerWapper = $(".player-wapper")

        $shrink.on("click", function() {
            if ($(this).hasClass('rotate')) {
                $(this).removeClass('rotate')
                $broadcastLeft.animate({ right: 0 }, 280)
                $(".online-people-count").animate({ right: 0 }, 280)
                $playerWapper.animate({ paddingRight: 505 }, 280)
            } else {
                $(this).addClass('rotate')
                $broadcastLeft.animate({ right: -400 }, 280)
                $(".online-people-count").animate({ right: -400 }, 280)
                $playerWapper.animate({ paddingRight: 105 }, 280)
            }

        })

        $(".live-send-msg").on("click", function() {
            var lessonId = $(this).attr("lesson_id");
            var smsText = $(".sms-content").val();
            ajax.post({
                url: "/index.php/Live/talk",
                data: { lesson_id: lessonId, talk: smsText },
                success: function(data) {
                    $(".chat-bxo").html("");
                    $(".chat-bxo").append(data)
                    $chaBox[0].scrollTop = $chaBox[0].scrollHeight;
                }
            })
            $(".sms-content").val("") //清空输入框内存
        })
        $(".vod-send-msg").on("click", function() {
            var lessonId = $(this).attr("lesson_id");
            var smsText = $(".sms-content").val();
            ajax.post({
                url: "/index.php/Vod/talk",
                data: { lesson_id: lessonId, talk: smsText },
                success: function(data) {
                    $(".chat-bxo").html("");
                    $(".chat-bxo").append(data)
                    $chaBox[0].scrollTop = $chaBox[0].scrollHeight;
                }
            })

            // var html = `<div class="row my-row clearfix">
            //             <div class="info">
            //                 <img class="user-header-img" src="../res/images/teacher/10.jpg" alt="">
            //                 <span class="user-name">刘老师</span>
            //             </div>
            //             <div class="chat-text">${smsText}<i class="bottomLevel"></i>
            //             </div>
            //         </div>`


            // var nDivHight = $chaBox.height(); //元素的高度
            // var nScrollHight = $chaBox[0].scrollHeight; //滚动距离总长(注意不是滚动条的长度)
            // var nScrollTop = $chaBox[0].scrollTop; //滚动到的当前位置


            // if ((nScrollTop + nDivHight >= nScrollHight - 18) || nScrollTop == 0) {
            //     $chaBox[0].scrollTop = $chaBox[0].scrollHeight;
            // }
            $(".sms-content").val("") //清空输入框内存
        })

        // $(".chat-bxo").scroll(function() {
        //     var nDivHight = $(".chat-bxo").height(); //元素的高度
        //     var nScrollHight = $(this)[0].scrollHeight; //滚动距离总长(注意不是滚动条的长度)
        //     var nScrollTop = $(this)[0].scrollTop; //滚动到的当前位置
        //     if (nScrollTop + nDivHight >= nScrollHight-18) {
        //         alert("滚动条到底部了");
        //     }
        // });


        $("body").keydown(function() {
            if (event.keyCode == "13") { //keyCode=13是回车键
                $(".send-sms-btn").trigger("click");
            }
        });


        $(".tabs-screen").on("click",function(){
        	var flag = $(this).hasClass('active');
        	var $text = $(this).find(".tabs-text");
        	var $tab1 = $(".player-wapper").find(".tab1");
        	var $tab2 = $(".player-wapper").find(".tab2");
        	if(flag){
        		$text.html("切换为视频");
        		$(this).removeClass("active")
        		$tab1.hide();
        		$tab2.show();
        	}else{
        		$text.html("切换为PPT");
        		$(this).addClass("active")
        		$tab1.show();
        		$tab2.hide();
        	}
        })
    }
    return loadTip;
})