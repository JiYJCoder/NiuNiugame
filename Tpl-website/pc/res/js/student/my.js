define(["ajax"],function(ajax) {
    var loadTip = function() {


        /*个人资料页面*/
        var $myContain = $(".s-my-contain");
        var $changePic = $myContain.find(".change-head-picture input")
        var $tabHeadBtn = $myContain.find(".tabhead button")
        var $tabContain = $myContain.find(".tab")
        var base64Images;

        // tab切换
        $tabHeadBtn.on("click",function(){
            var index = $(this).index();
            $(this).addClass('active').siblings().removeClass('active')
            $tabContain.eq(index).show().siblings().hide();
        })
        
        //图片预览
        $changePic.change(function() {
            //图片预览       
            var imgUrl = window.URL.createObjectURL(this.files[0]);
            $(".preview-picture img").attr("src", imgUrl);

            var reader = new FileReader();
            reader.readAsDataURL(this.files[0]);
            reader.onload = function(e) {
                base64Images = e.target.result;
            };
        })

        //生日选择框
        var defaultYear = $(".select-year").attr("default_year");
        var defaultMonth = $(".select-moon").attr("default_month");
        var selectDay = $(".select-day").attr("defaul_day");

        if(defaultYear){
            new YMDselect('year1', 'month1', 'day1',defaultYear,defaultMonth,selectDay);
        }else{
            new YMDselect('year1', 'month1', 'day1');
        }

        var $modifyPasswordBtn = $(".modify-password-btn");
        $modifyPasswordBtn.on("click",function(){
            var oldWord = $(".old-word").val();
            var newWord1 = $(".new-word1").val();
            var newWord2 = $(".new-word2").val();
            if(newWord1.length < 6){
                layer.msg("新密码必须6位数字以上")
                return false;
            }
            if(newWord1 != newWord2){
                layer.msg("两次密码不一样")
                return false;
            }
            ajax.post({
                url:"/index.php/student/change_password",
                data:{
                    password:oldWord,
                    new_password:newWord2
                },
                type:3,
                success:function(data){
                    setTimeout(function(){
                        location.reload();
                    },1000)
                }
            })
        });

        /*资料信息*/

        $.ajax({
            url:"/index.php/Global/getGrowSchool",
            async: false,
            dataType : 'json',
            type : "GET",
            success:function(data){
                var html="";
                data = data.data;
                $.each(data,function(index, el) {
                    html += "<button>"+el+"</button>"
                });
                $(".school-contain").html(html);
            }
        });

        var $tab1 = $(".tab-1");
        var $schoolInput =  $tab1.find(".c-school");
        var $schoolList = $tab1.find(".school-contain")
        var $schoolListInput = $tab1.find("button");
        var $save = $tab1.find(".save");


        $schoolInput.on("click",function(){
            $schoolList.show();
        })
        $schoolListInput.on("click",function(){
            $schoolList.hide();
            var schoolName = $(this).html();
            $schoolInput.val($(this).html())
            $(".write-school-input").val(schoolName);
        })

        $save.on("click",function(){
            var school = $(".write-school-input").val();
            var selectYear = $(".select-year").val();
            var selectMonth = $(".select-moon").val();
            var selectDay = $(".select-day").val();
            var sex = $(".select-sex").find("input:checked").attr("sex");
            var obj = {};
            if(school.length == 0){
                layer.msg("请填写所在学校")
                return false;
            }
            if(selectYear == 0 || selectMonth == 0 || selectDay == 0){
                layer.msg("请选择出生日期")
                return false;
            }
            obj.headimg = base64Images;
            obj.school = school;
            obj.birthday = selectYear+"-"+selectMonth+"-"+selectDay;
            obj.sex = sex;
            ajax.post({
                url:"/index.php/student/modify_personal_information",
                data:obj,
                type:2,
                beforemsg:"保存中...",
                success:function(){
                    setTimeout(function(){
                        window.location.reload();
                    },1000)
                }
            })
        })


     
    }
    return loadTip;
})