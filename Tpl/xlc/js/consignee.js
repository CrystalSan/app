$(function() {
    $("#showAddNewAddress").live('click',addNewAddress);

    $(".isdefault_ckb label").live('click',function(){$(this).toggleClass('onchecked');});

    $('.abo_id').each(function(){
        var _this = $(this),
            id = _this.attr('data-id'),
            delBtn = _this.find('.delConsignee'),
            setDefaultBtn = _this.find('.setDefault'),
        editConsigneeBtn = _this.find('.editConsignee');
        //删除地址
        delBtn.bind('click', function () {
            $.showConfirm("确定要删除该记录吗？",function(){
                var url = APP_ROOT + "/index.php?ctl=settings&act=del_consignee&id=" + id;
                        $.ajax({
                            url: url, dataType: "json", type: "get",
                            success: function (json) {
                                $.weeboxs.close();
                                if(json.status == 1){
                                    /*$.showSuccess(json.info,function(){
                                     _this.remove();
                                     });*/
                                    showtips(json.info,function(){
                                        _this.remove();
                                    });
                                }else{
                                    showtips(json.info);
                                }
                            }
                        })
                });
            });
        editConsigneeBtn.live('click',function(){
            editConsignee(id);
        })
//设为默认地址
        setDefaultBtn.live('click',function(){
//                $("#loading").show();
            var url = APP_ROOT + "/index.php?ctl=settings&act=set_default_consignee&id=" + id;
            $.ajax({
                url: url, dataType: "json", type: "get",
                success: function (json) {
                    if(json.status == 1){
                        showtips('默认地址设置成功！');
                        //$.showSuccess('默认地址设置成功！');
                        $('.setDefault>span').each(function(){$(this).removeClass('onchecked').html('设为默认地址');})
                        setDefaultBtn.find('span').addClass('onchecked').html('当前默认地址');
                    }
                }
            })
        })
        });
});
function showtips(msg,fun){
    $('.light-tips').html(msg).fadeIn(function(){
        setTimeout(function(){$('.light-tips').fadeOut(); fun && fun();},1000)
    });

}
/*显示添加新地址弹框*/
function addNewAddress(){
    $('#title').html('添加新地址');

    $.weeboxs.open("#addNewAddress",{boxid:"addressBox",type:'box',width:400,showTitle: false,showCancel:false,clickClose:true,okBtnName:"保存",onok:function(){
        saveConsignee('addNewAddressForm',function(json){
            if(json.status==1){
                $.weeboxs.close();
                showtips('保存地址成功!',function(){
                    window.location.reload();
                });
            }else{
                showtips(json.info)
            }
        });

    }});
}
function editConsignee(id){
    addNewAddress();
    var $form = $("#addNewAddressForm");var item = list[id];
    for (var i in item){
        //alert( i + "，" + item[i] );
        //$form.find("input[name="+i+"]").val(item[i]);
        var inputVal = $form.find("input[name="+i+"]");
        if(inputVal.val() !== undefined &&item[i]!== undefined){
            inputVal.val(item[i]);
        }
        if(i=='is_default'&&item[i]==1){
            $form.find('#isdefault').attr('checked',true);
            $form.find('label').addClass('onchecked');
        }
    }
    //$form.find('select[name=city]').val(item.city)
    $('#title').html('编辑地址');
}
/* 编辑地址*/
function saveConsignee(formid,fun){
    var $form = $("#"+formid),
        str = "",p=$form.serialize();alert(p)
    if(!$.checkMobilePhone($.trim($form.find('input[name="mobile"]').val()))){
        str="请填写正确的手机号";showtips(str);return;
    }
    //后台校验
    var url = APP_ROOT+"/index.php?ctl=ajax&act=save_consignee";

    $.ajax({url:url,dataType:"json",type:"POST",data:p,
        success:function(json){
            fun && fun(json);
        }
    });

}

