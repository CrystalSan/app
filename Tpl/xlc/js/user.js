/**
 * Created by libeibei on 15/11/27.
 */
$(function(){
    user.init(urls);

    /*var dropObj = $('.drop_content');
    //选择登录方式
    $('.pay_sucsss>ul>li>a').each(function(){
        //alert(_this.id)
        var _this = $(this)
        _this.live(EVENT_TYPE,function(e){
            e.preventDefault();
            if(_this.attr('id') == 'wechatLoginBtn'&&isWeiXin()){
            //if(_this.attr('id') == 'wechatLoginBtn'){
                //微信浏览器直接授权登录
                weChatUtils.getWechatUserInfo();
                return;
            }
            var thisDropObj =  $(this).next();

            dropObj.slideUp();
            if(thisDropObj.css('display')=="none"){thisDropObj.slideDown();}
        })
    });*/
});

var user = {
    timmer: null,
    leftTime: 0,
    formData: {
        form: $("#user_getpwd_by_mobile"),
        phone: $("#settings_mobile"),
        password: $("#user_pwd"),
        rePassword: $("#confirm_user_pwd"),
        sendMsgBtn: $("#J_send_sms_verify"),
        verifyCode:$("#verify_coder"),
        submitBtn:$("#submit_btn")
    },
    //EVENT_TYPE:"click",
    init: function (urlList) {
        this.sendSMSUrl = typeof urlList.sendSMSUrl!=="undefined"?urlList.sendSMSUrl:"";
        this.checkVerifyCodeUrl = typeof urlList.checkVerifyCodeUrl!=="undefined"?urlList.checkVerifyCodeUrl:"";
        this.submitUrl = typeof urlList.submitUrl!=="undefined"?urlList.submitUrl:"";
        //按钮绑定事件，暂时使用click，更新jQuery版本后更换事件
        user.formData.sendMsgBtn.click(function(){
            user.sendMobileVerifyMsg();//获取验证码按钮
        });
        var sb = user.formData.submitBtn;//提交表单按钮
        switch (sb.attr("data-submit-type")){
            case "forgetPwd":
                sb.bind(EVENT_TYPE,function(){user.forgetPwd()});
                break;
            case "modifyPwd":
                sb.bind(EVENT_TYPE,function(){user.modifyPwd()});
                break;
            case "doLogin":
                user.formData.form = $("#user_login_form");
                sb.bind(EVENT_TYPE,function(){user.doLogin()});
                break;
            case "doRegister":
                user.formData.form = $("#user_register_form");
                sb.bind(EVENT_TYPE,function(){user.doRegister()});
        };
        user.formData.verifyCode.bind("blur",function(){//焦点离开时校验验证码
            user.checkRegisterVerifyCoder();
        })
    },
    //找回密码
    forgetPwd: function () {
        /*
         *  steps:
         * 1.check phone
         * 2.compare pwd & repwd
         * 3.validate pwd
         * 4.checkRegisterVerifyCoder
         * 5.send Form
         *
         * Q: 尚未校验验证码
         * */
        if(this.checkPhone() && this.checkPassword()){
            var url = user.submitUrl,
                p = user.formData.form.serialize();
            user.sendAjax(url,p,function(json){
                if(json.status){
                    $.showSuccess(json.info,function(){
                        location.href=APP_ROOT+"/index.php?ctl=user&act=login";//APP_ROOT+"/index.php?ctl=user&act=login";
                    });
                }else{
                    $.showErr(json.info);
                }
            },function(){$.showErr("系统繁忙，稍后请重试!");})
        }
    },
    //修改个人密码
    modifyPwd: function () {
        /*
         * Q：是否校验旧密码？
         * */
        if(this.checkPhone() && this.checkPassword()){
            var url = user.submitUrl,
                p = user.formData.form.serialize();
            user.sendAjax(url,p,function(json){
                /* alert(json.status);
                 alert(json.info)
                 if(json.info!=null){
                 $.showSuccess(json.info);
                 }else{*/
                if(json.status==1){
                    $.showSuccess("保存成功!",function(){
                        window.location.href = APP_ROOT+"/index.php?ctl=settings";
                    });
                }
                if(json.status==0){
                    $.showSuccess(json.info);
                }
                //}
            },function(){$.showErr("系统繁忙，稍后请重试!");})
        }
    },
    //login
    doLogin:function(){
        if($("#user_pwd").val()!==""&&user.formData.form.find('input[name="mobile"]').val()!=="") {
            var url = user.submitUrl,
                p = user.formData.form.serialize();
            user.sendAjax(url, p, function (json) {
                if (json.status == 1) {
                    location.href = json.jump;
                } else {
                    $.showErr(json.info);
                }
            }, function () {
                $.showErr("系统繁忙，稍后请重试!");
            })
        }else{
            $.showErr("用户名或密码不能为空！");
            return false;
        }
    },
    //register
    doRegister:function(){
        if(user.formData.form.find('input[name="user_name"]').val()==""){
            user.formData.form.find('input[name="user_name"]').val(user.formData.phone.val());
            //return false;
        };
        if(this.checkPhone() && this.checkPassword()) {
            var url = user.submitUrl,
                p = user.formData.form.serialize();

            user.sendAjax(url, p, function (json) {
                if (json.status == 1) {
                    location.href = json.jump;
                } else {
                    $.showErr(json.info);
                }
            }, function () {
                $.showErr("系统繁忙，稍后请重试!");
            })
        }
    },
    checkPhone: function () {
        var obj = user.formData.phone,p = obj.val();
        if (!$.checkMobilePhone(p)) {
            $.showErr("手机号码格式错误");
            return false;
        }
        /*if (!$.maxLength(p, 11, true)) {
            $.showErr("长度不能超过11位");
            return false;
        }*/
        if ($.trim(p).length == 0) {
            $.showErr("手机号码不能为空");
            return false;
        }
        if ($.trim(p).length !== 11 ) {
            $.showErr("请填写正确的手机");
            return false;
        }
        return true;
    },
    checkPassword:function(){
        var p = $.trim(user.formData.password.val()),rep = $.trim(user.formData.rePassword.val());
        if(p=="") {
            $.showErr("请输入密码");
            return false;
        }
        if(p.length < 4){
            $.showErr("密码不少于4个字符");
            return false;
        }
        if(rep==""){
            $.showErr("请输入确认密码");
            return false;
        }
        if(p !== rep){
            $.showErr("密码不一致");
            return false;
        }
        return true;
    },
    sendSMSUrl:"",//发送验证码的请求url
    checkVerifyCodeUrl:"",//检查验证码的请求url
    submitUrl:"",//提交表单时请求url
    //发送手机验证码
    sendMobileVerifyMsg: function () {
        if(user.checkPhone()){
            var url = user.sendSMSUrl, param = {mobile: $.trim(user.formData.phone.val())};
            user.sendAjax(url, param, this.sendingMsgFun);
        };
    },
    //发送验证码后回调
    sendingMsgFun: function (json) {
        if (json.status == 1) {
            //sucsses   启动计时器
            user.leftTime = 60;
            user.codeLeftTimeFun();
            $.showSuccess(json.info);
        } else {
            $.showErr(json.info);
        }
    },
    //计时器
    codeLeftTimeFun: function () {
        clearTimeout(user.timmer);
        var oBtn = user.formData.sendMsgBtn;
        oBtn.html(user.leftTime + "秒后重新发送").addClass("disabled");
        oBtn.attr('disabled', "true");//禁用按钮
        user.leftTime--;
        if (user.leftTime > 0) {
            user.timmer = setTimeout(user.codeLeftTimeFun, 1000);
        } else {
            user.leftTime = 60;
            oBtn.removeAttr("disabled").removeClass("disabled").html("发送验证码");//启用按钮
        }
    },resultTmp:{},
    //检查验证码
    checkRegisterVerifyCoder: function () {
        var code = user.formData.verifyCode;
        if($.trim(code.val())==""){
            $.showSuccess("请输入验证码");
            code.removeClass("correct");
            return false;
        }else{
            var mobile = $.trim(user.formData.phone.val()),code = $.trim(code.val()),url = user.checkVerifyCodeUrl;
            if(mobile!=""||code!=""){
                var q = {mobile:mobile,code:code};
                user.sendAjax(url, q,
                    function(json){
                        if(json.status == 0){/*$.showSuccess("对不起，验证码错误");*/user.formData.verifyCode.removeClass("correct").addClass('incorrect');return false;}
                        if(json.status == 1){/*$.showSuccess("验证码正确！");*/user.formData.verifyCode.addClass("correct");}
                    }
                );
            }else{
                $.showSuccess("对不起，手机号不能为空");return false;
            }

        }
    },
    sendAjax: function (url, param, successFun, errorFun,type) {
        $.ajax({
            url: url,
            data: param,
            type: type?type:"POST",
            dataType: "json",
            success: function (json) {
                successFun && successFun(json);
            },
            error: function (e) {
                errorFun && errorFun(e);
            }
        });
    }
}
