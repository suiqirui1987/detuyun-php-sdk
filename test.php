<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<?php
require_once('detuyun.class.php');

try
{
	$bucketname = "test";
	$detuyun = new DetuYun($bucketname, '10001', '123456');


	echo "=====writeFile====直接上传文件\r\n";
    $fh = fopen('7.png', 'rb');
	$content = fread($fh,filesize("7.png"));
    fclose($fh);
    $rsp = $detuyun->writeFile('/demo/7.png', $content);   // 上传图片，自动创建目录
	
	
	
    echo "=====writeFile====直接上传文件\r\n";
    $fh = fopen('demo.jpg', 'rb');
    $rsp = $detuyun->writeFile('/demo/demo_normal.jpg', $fh);   // 上传图片，自动创建目录
    fclose($fh);
	echo "\n\r\n=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	

   
	echo "\n\r\n=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	echo "=====writeFile====直接上传文件\r\n";
    $fh = fopen('qq_face_33.gif', 'rb');
    $rsp = $detuyun->writeFile('/demo/qq_face_33.gif', $fh);   // 上传图片，自动创建目录
    fclose($fh);
	echo "\n\r\n=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	
	$rsp = $detuyun->getFileInfo("/demo/demo_normal.jpg",true);
	echo "\n\r\ngetFileInfo=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	$rsp = $detuyun->getFileurl("/demo/demo_normal.jpg",time()+3600);
	echo "\n\r\n getFileurl=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	
	$rsp = $detuyun->getBucketUsage();
	
	echo "\n\r\n=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	$rsp = $detuyun->getList("/",true);
	echo "\n\r\ngetList=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	
	
	$rsp = $detuyun->getList("/demo",true);
	echo "\n\r\ngetList=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	

	
	
	$rsp = $detuyun->makeDir("/demo/2",true);
	echo "\n\r\n==makeDir=====\n\r\n";
    var_dump($rsp);
    echo "===makeDir====DONE\n\r\n";
	
	
	$rsp =$detuyun->rmDir('/demo/2');
	echo "\n\r\n=rmDir======\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	$rsp =$detuyun->rmDir('/demo');
	echo "\n\r\n=rmDir======\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	

	
    $rsp = $detuyun->getFileInfo("/demo/demo_normal.jpg",true);
	echo "\n\r\ngetFileInfo=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	
	
 $rsp =$detuyun->delete('/demo/demo_normal.jpg');
	echo "\n\r\n=delete======\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";

	
	
	$rsp = $detuyun->getList("/demo",true);
	echo "\n\r\ngetList=========\n\r\n";
    var_dump($rsp);
    echo "=========DONE\n\r\n";
	


	

	
/*
*/
}
catch(Exception $e)
{
echo $e->getMessage();	
}