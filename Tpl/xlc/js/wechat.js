/**
 * Created by admin on 16/1/19.
 */
var weChatUtils = {
    //获取微信登录二维码
    getWechatLoginQr:function(){
        var redirect_uri = encodeURIComponent("http://www.xinlechou.com/wxpay_web/wxUtil.php?act=getWechatAuthLogin");
        if(typeof WxLogin !== 'undefined'){
            var obj = new WxLogin({
                id:"qr_container",//二维码容器id
                appid: "wx12b1f2142f7e8e7a",
                scope: "snsapi_login",
                redirect_uri: redirect_uri,
                state: (new Date()).getTime(),
                style: "black",
                href: ""
            });
        }
    },
    getWechatUserInfo:function(){
        //临时地址
        var url = 'http://'+window.location.host+'/htdocs/wxpay_web/wxUtil.php?act=getWechatAuthBase';
        window.location.href = url;
    }
}

