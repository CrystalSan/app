<?php
class projectModule extends BaseModule{
	//多文件上传
    function upload() 
    {
        import("ORG.Net.Image3");
        if (!empty($_FILES)) 
        {
            //获取文件名
            $tempFile         = $_FILES['Filedata']['tmp_name'];
            //保存路径
            $targetPath     = APP_PATH . 'Public/Uploads/';
            //新图片名
            $new_file_name     = new_name( $_FILES['Filedata']['name']);
            //图片网站路径
            $targetFile     = $targetPath . $new_file_name;
            
            //判断是否文件夹存在
            if(!is_dir($targetPath))
                mkdir($targetPath, 0777, true);
        
            //防止中文文件名乱码
            move_uploaded_file($tempFile,iconv('utf-8','gbk', $targetFile));
            
            //水印
            $water = '';
            $img   = new Image3();
            $img->param($targetFile)->water($targetFile,$water,9);
            //ajax返回图片名称
            echo get_relative_path($new_file_name);
        }
    }
    
    //图片模块
    public function img()
    {
        $c = D('class');
        $a = D('article');
        if($_POST['action'] == 'add')
        {
            //判断是否有上传多文件
            if($_POST['imgs'])
            {
                $_POST['img'] = '';
                foreach ($_POST['imgs'] as $v)
                {
                    $_POST['img'] .= $v . ',';
                }
            }
    
            //加载上传类
            $file = static::get_file(2097152);
            //判断是否上传文件
            $_POST['simg']    = ($file) ? $file : '' ;
            $_POST['addtime'] = exec_time();
            //dump($_POST);die();
            //过滤数据并添加
            $a->create();
            if($a->add())
                static::success('添加成功', 3000, 1, 0);
        }

        //获取所有分类信息
        $data = $c->order('nos asc')->select();
        //格式化无线分类
        $data = static::get_class($data);
        $this->assign('data', $data);
        //时间输出
        $this->assign('time', date('Y-m-d', time()));
        $this->display();
    }
	}
?>