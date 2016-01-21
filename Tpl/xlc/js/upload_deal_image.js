
function upd_file(obj,file_id,imgid)
{	

	$("input[name='"+file_id+"']").bind("change",function(){			
		//$(obj).hide();
		//$(obj).parent().find(".fileuploading").removeClass("hide");
		//$(obj).parent().find(".fileuploading").removeClass("show");
		//$(obj).parent().find(".fileuploading").addClass("show");
		  $.ajaxFileUpload
		   (
			   {
				    url:APP_ROOT+'/upload.php',
				    secureuri:false,
				    fileElementId:file_id,
				    dataType: 'json',
				    success: function (data, status)
				    {
				   		//$(obj).show();
				   		//$(obj).parent().find(".fileuploading").removeClass("hide");
						//$(obj).parent().find(".fileuploading").removeClass("show");
						//$(obj).parent().find(".fileuploading").addClass("hide");
				   		if(data.status==1)
				   		{
				   			var newImgThumbUrl = data.thumb_url+"?r="+Math.random();
				   			$(obj).css("background-image","url("+newImgThumbUrl+")"); 
				   			$("input[name='"+imgid+"']").val(data.url);
				   			$("input[name='"+imgid+"_thumb']").val(data.thumb_url);
				   		}
				   		else
				   		{
				   			$.showErr(data.msg);
				   		}
				   		
				    },
				    error: function (data, status, e)
				    {
						$.showErr(data.responseText);;
				    	//$(obj).show();
				    	//$(obj).parent().find(".fileuploading").removeClass("hide");
						//$(obj).parent().find(".fileuploading").removeClass("show");
						//$(obj).parent().find(".fileuploading").addClass("hide");
				    }
			   }
		   );
		  $("input[name='"+file_id+"']").unbind("change");
	});	
}