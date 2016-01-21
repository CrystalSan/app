<?php
 $conn=mysql_connect('localhost','root','root');
mysql_query('set names utf8');
mysql_select_db('xinlechou'); 
//$sql=insert into ;
//$result=mysql_query();
//$num=mysql_num_rows($result);


//1)判断是否选择了文件
$nember=count($_FILES['files']['name']);
if($nember<=9){
for($i=0;$i<$nember;$i++){
	
	$tmpname.=$_FILES['files']['tmp_name'][$i];
	
	if(!$tmpname){
		
	echo "您没有选择上传文件";
	exit;
	
}

//2)判断文件大小
$max_size=10000000;  //以b为单位，此为10mb
if($_FILES['files']['size'][$i]>$max_size){
	
	echo "文件太大，请上传小于".$max_size."KB";
	exit;
}

//3)判断文件类型
$img_type=array(
		".jpg",
		".png",
		".gif",
); 

$filename=strtolower(strrchr($_FILES['files']['name'][$i],'.'));
if(!in_array($filename,$img_type)){
	echo "您上传的文件格式不正确,请上传一以下格式<br />";
	//print_r (array_values($img_type));这个输出的是数组格式
	foreach ($img_type as $value){
		echo "<li>".$value."</li>";
	}
	exit;
}

//4)重名问题
$upfilename=$i.time().$filename;//文件命名时间加格式
$root=$_SERVER['DOCUMENT_ROOT'];//获取根路径即www下
$upload_wei=$root."/duotu/";
$pathfile=$upload_wei.$upfilename;//文件存放位置

//5)完成上传
$aa=move_uploaded_file($_FILES['files']['tmp_name'][$i],$pathfile);
$shu.=",".$upload_wei.$upfilename;
}
$shu=substr($shu, 1);
$sql="insert into xlc_zzzz(files) values('".$shu."')";
$bb=mysql_query($sql);
if($aa){
	echo "<script>window.location.href='http://127.0.0.1/hehe/newfile.php'</script>";
	
}else{
	echo "上传失败";
}
}else{
	echo "同时上传图片不得超过9张";
}
?>






















