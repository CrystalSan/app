$(document).ready(function(){
	bind_user_register();	
});

function clear_tip_box(obj)
{
	$(obj).parent().find(".tip_box").html("");
}
function form_error(obj,str)
{
	$(obj).parent().find(".tip_box").html("<div class='form_error'>"+str+"</div>");
}
function form_success(obj,str)
{
	$(obj).parent().find(".tip_box").html("<div class='form_success'>"+str+"</div>");
}
function form_tip(obj,str)
{
	$(obj).parent().find(".tip_box").html("<div class='form_tip'>"+str+"</div>");
}
function form_loading(obj)
{
	$(obj).parent().find(".tip_box").html("<div class='form_loading'></div>");
}

function bind_user_register()
{
	//绑定、执行最后的注册动作
	$("#submit_form").bind("click",function(){
		do_register();
	});
	
	$("#email").bind("blur",function(){
		check_register_email();
	});
	
	$("#user_pwd").bind("blur",function(){
		check_register_user_pwd();
	});
	
	$("#confirm_user_pwd").bind("blur",function(){
		check_register_confirm_user_pwd();
	});
	
	$("#user_name").bind("blur",function(){
		check_register_user_name();
	});
	
	$("#mobile").bind("blur",function(){
		check_register_mobile();
	});

	$("#verify_coder").bind("blur",function(){
		check_register_verifyCoder();
	});
	
	$("#user_register_form").bind("submit",function(){
		return false;
	});

}

	function check_register_email()
	{
		if($.trim($("#email").val())=="")
		{
			form_tip($("#email"),"请输入邮箱");		
		}
		else
		{
			check_field($("#email"),"email",$("#email").val());
		}
	}

	function check_register_user_name()
	{
		if($.trim($("#user_name").val())=="")
		{
			form_tip($("#user_name"),"请输入会员帐号");
		}
		else
		{
			check_field($("#user_name"),"user_name",$("#user_name").val());
		}
	}

	function check_register_user_pwd()
	{
		if($.trim($("#user_pwd").val())=="")
		{
			form_tip($("#user_pwd"),"请输入会员密码");
		}
		else if($.trim($("#user_pwd").val()).length<8)
		{
			form_error($("#user_pwd"),"密码不得小于八位");
		}
		else
		{
			form_success($("#user_pwd"),"");
		}
	}

	function check_register_confirm_user_pwd()
	{
		if($.trim($("#confirm_user_pwd").val())!=$.trim($("#user_pwd").val()))
		{
			form_error($("#confirm_user_pwd"),"确认密码失败");
		}
		else
		{
			form_success($("#confirm_user_pwd"),"");
		}
	}

	function check_register_mobile()
	{
		if($.trim($("#mobile").val())=="")
		{
			form_error($("#mobile"),"请输入手机号码");		
		}
		else
		{
			check_field($("#mobile"),"mobile",$("#mobile").val());
		}
	}
	//检查验证码
	function check_register_verifyCoder(){
		if($.trim($("#verify_coder").val())=="")
		{
			form_tip($("#verify_coder"),"请输入验证码");		
		}
		else
		{
			
			var mobile = $.trim($("#mobile").val());
		
			var code = $.trim($("#verify_coder").val());
			if(mobile!=""||code!=""){
				var ajaxurl = APP_ROOT+"/index.php?ctl=user&act=check_verify_code";
				var query = new Object();
				query.mobile = mobile;
				query.code = code;
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					data:query,
					type: "POST",
					success:function(ajaxobj){
						if(ajaxobj.status==1)
						{
							form_success($("#verify_coder"),"验证码正确");
						}
						if(ajaxobj.status==0)
						{
							form_error($("#verify_coder"),"验证码不正确");
						}
					}
				});
			}
		}
	}



var is_submiting = false;
function do_register()
{
	if($("#tk_ipt").val()==0){
		return false;
	}
	if(!is_submiting)
	{
		is_submiting = true;
		var email = $.trim($("#email").val());
		
		var user_pwd = $.trim($("#user_pwd").val());
		var user_type = $.trim($("#select_box").val());
		var confirm_user_pwd = $.trim($("#confirm_user_pwd").val());
		var user_name = $.trim($("#user_name").val());
		var user_level= $.trim($("#user_level").val());
		
		var mobile=$.trim($("#mobile").val());
		var verify_coder=$.trim($("#verify_coder").val());
		
		if(email!=""||user_name!="")
		{
			var ajaxurl = APP_ROOT+"/index.php?ctl=user&act=do_register";
			var query = new Object();
			query.email = email;
			query.user_name = user_name;
			query.user_pwd = user_pwd;
			query.confirm_user_pwd = confirm_user_pwd;
			query.user_type=user_type;
			query.user_level=user_level;
		
			query.mobile=mobile;
			query.verify_coder=verify_coder;
			
			$.ajax({ 
				url: ajaxurl,
				dataType: "json",
				data:query,
				type: "POST",
				success: function(ajaxobj){
					if(ajaxobj.status==1)
					{
						form_success($("#email"),"");
						form_success($("#user_name"),"");
						form_success($("#user_pwd"),"");
						form_success($("#confirm_user_pwd"),"");
						form_success($("#user_type"),"");
						var integrate = $("<span id='integrate'>"+ajaxobj.data+"</span>");
						$("body").append(integrate);
						location.href = ajaxobj.jump;
							
					}
					else
					{
						is_submiting = false;
						if(ajaxobj.info!="")
						{
							$.showErr(ajaxobj.info,function(){
								location.href = APP_ROOT+"/";
							});
						}
						for(var i=0;i<ajaxobj.data.length;i++)
						{
							 if(ajaxobj.data[i].type=="form_success")
							 {
								 form_success($("#"+ajaxobj.data[i].field+""),"");
							 }
							 if(ajaxobj.data[i].type=="form_error")
							 {
								 form_error($("#"+ajaxobj.data[i].field+""),ajaxobj.data[i].info);
							 }
							 if(ajaxobj.data[i].type=="form_tip")
							 {
								 form_tip($("#"+ajaxobj.data[i].field+""),ajaxobj.data[i].info);
							 }						
						}
					}
				},
				error:function(ajaxobj)
				{
					is_submiting = false;
					if(email!="")
					{
						clear_tip_box($("#email"));
					}
					if(user_name!="")
					{
						clear_tip_box($("#user_name"));
					}
				}
			});
		}
		else
		{
			is_submiting = false;
			form_tip($("#user_name"),"请输入会员帐号");
			form_tip($("#email"),"请输入邮箱");
			if(user_pwd=="")
			form_tip($("#user_pwd"),"请输入会员密码");	
			else if(user_pwd.length<8)
			form_error($("#user_pwd"),"密码不得小于八位");	
			else
			form_success($("#user_pwd"),"");
			
			if(mobile=="")
			form_tip($("#mobile"),"请输入手机号码");	
			else if(user_pwd.length>12)
			form_error($("#mobile"),"不得大于11位");	
			
			if(verify_coder=="")
			form_tip($("#verify_coder"),"请输入验证码");
			else
			form_success($("#verify_coder"),"");
			
			if(user_pwd==confirm_user_pwd)
			{
				form_success($("#confirm_user_pwd"),"");
			}
			else
			{
				form_error($("#confirm_user_pwd"),"确认密码失败");
			}
		}
	}
}


function check_field(o,field,value)
{
	var ajaxurl = APP_ROOT+"/index.php?ctl=user&act=register_check";
	var query = new Object();
	query.field = field;
	query.value = value;
	form_loading(o);
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==1)
			{
				form_success(o,"");			
			}
			else
			{
				form_error(o,ajaxobj.info);							
			}
		},
		error:function(ajaxobj)
		{
			clear_tip_box(o);
		}
	});
}