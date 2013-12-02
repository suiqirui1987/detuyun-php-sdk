detuyun-php-sdk
===============



此 SDK 适用于 PHP 5.1.0 及其以上版本。基于得图云存储HTTP REST API接口 构建。使用此 SDK 构建您的网络应用程序，能让您以非常便捷地方式将数据安全地存储到得图云存储上。无论您的网络应用是一个网站程序，还是包括从云端（服务端程序）到终端（手持设备应用）的架构的服务或应用，通过得图云存储及其 SDK，都能让您应用程序的终端用户高速上传和下载，同时也让您的服务端更加轻盈。




- [应用接入](#install)
	- [获取Access Key 和 Secret Key](#acc-appkey)
- [使用说明](#detuyun-api)
	- [1 初始化DetuYun](#detuyun-init)
	- [2 上传文件](#detuyun-upload)
	- [3 下载文件](#detuyun-down)
	- [4 创建目录](#detuyun-createdir)
	- [5 删除目录或者文件](#detuyun-deletedir)
	- [6 获取目录文件列表](#detuyun-getdir)
	- [7 获取文件信息](#detuyun-getfile)
	- [8 获取空间使用状况](#detuyun-getused)
- [异常处理](#detuyun-exception)




<a name="install"></a>
## 应用接入

<a name="acc-appkey"></a>

### 1. 获取Access Key 和 Secret Key

要接入得图云存储，您需要拥有一对有效的 Access Key 和 Secret Key 用来进行签名认证。可以通过如下步骤获得：

1. <a href="http://www.detuyun.com/user/accesskey" target="_blank">登录得图云开发者自助平台，查看 Access Key 和 Secret Key 。</a>

<a name=detuyun-api></a>
## 初始化DetuYun

<a name="detuyun-init"></a>
### 1.初始化DetuYun

````
require_once('detuyun.class.php');
$detuyun = new DetuYun('bucketname',  Access Key, Secret Key);
````

参数 `bucketname` 为空间名称。

示例代码如下：

	$detuyun = new DetuYun('bucketname', Access Key, Secret Key);
	
**超时时间设置**在初始化DetuYun上传时，可以选择设置上传请求超时时间（默认30s）:
```
$detuyun = new DetuYun('bucketname',  Access Key, Secret Key, 600);
```
<a name="detuyun-upload"></a>
### 2. 上传文件



	// 直接传递文件内容的形式上传
	$detuyun->writeFile('/temp/text_demo.txt', 'Hello World', True);
	
	// 数据流方式上传，可降低内存占用
	$fh = fopen('demo.png', 'r');
	$detuyun->writeFile('/temp/upload_demo.png', $fh, True);
	fclose($fh);

第三个参数为可选。True 表示自动创建相应目录，默认值为False。

本方法还有一个数组类型的可选参数，用来设置文件类型、缩略图处理等参数。


	$opts = array(
		X_GMKERL_THUMBNAIL => 'square' // 缩略图版本，仅适用于图片空间
	);
	
	$fh = fopen('demo.png', 'r');
	$detuyun->writeFile('/temp/upload_demo.png', $fh, $opts);
	fclose($fh);

该参数可以设置的值还包括：

* CONTENT_TYPE
* CONTENT_MD5
* X_GMKERL_THUMBNAIL
* X_GMKERL_TYPE
* X_GMKERL_VALUE
* X_GMKERL_QUALITY

参数的具体使用方法，请参考 <a target="_blank" href="http://www.detuyun.com/docs/page2.html">标准API上传文件</a>

文件空间上传成功后返回`True`，图片空间上传成功后一数组形式返回图片信息：


	array(
	  'x-detuyun-width' => 2000,
	  'x-detuyun-height' => 1000,
	  'x-detuyun-frames' => 1
	  'x-detuyun-type' => "JPEG"
	)

如果上传失败，则会抛出异常。

<a name=detuyun-down></a>
### 3. 下载文件


	// 直接读取文件内容
	$data = $detuyun->readFile('/temp/upload_demo.png');
	
	// 使用数据流模式下载，节省内存占用
	$fh = fopen('/tmp/demo.png', 'w');
	$detuyun->readFile('/temp/upload_demo.png', $fh);
	fclose($fh);


直接获取文件时，返回文件内容，使用数据流形式获取时，成功返回`True`。
如果获取文件失败，则抛出异常。

<a name=detuyun-createdir></a>
### 4.创建目录

	$detuyun->mkDir('/demo/');

目录路径必须以斜杠 `/` 结尾，创建成功返回 `True`，否则抛出异常。

<a name=detuyun-deletedir></a>
### 5.删除目录或者文件

	$detuyun->delete('/demo/'); // 删除目录
	$detuyun->delete('/demo/demo.png'); // 删除文件

删除成功返回True，否则抛出异常。注意删除目录时，`必须保证目录为空` ，否则也会抛出异常。


<a name=detuyun-getdir></a>
### 6.获取目录文件列表


	$list = $detuyun->getList('/demo/');
	$file = $list[0];
	echo $file['name'];	// 文件名
	echo $file['type'];	// 类型（目录: folder; 文件: file）
	echo $file['size'];	// 尺寸
	echo $file['time'];	// 创建时间
    echo $file['filetype']; //定义文件类型

获取目录文件以及子目录列表。需要获取根目录列表是，使用 `$detuyun->getList('/')` ，或直接用方法不传递参数。
目录获取失败则抛出异常。

<a name=detuyun-getfile></a>
### 7.获取文件信息


	$result = $detuyun->getFileInfo('/demo/demo.png');
	$arr = explode("\t",$result["x-detuyun-file"]);

获取文件信息时通过Tab键分隔获取相应内容，返回结果为一个数组。

<a name=detuyun-getused></a>
###8.获取空间使用状况

	
	$detuyun->getFolderUsage();	// 获取Bucket空间使用情况
	$detuyun->getFolderUsage('/demo/'); 获取目录空间使用情况
	
返回的结果为空间使用量，单位 ***kb***

<a name=detuyun-exception></a>
## 异常处理
当API请求发生错误时，SDK将抛出异常，具体错误代码请参考 <a target="_blank"  href="http://www.detuyun.com/docs/page6.html">标准API错误代码表</a>

根据返回HTTP CODE的不同，SDK将抛出以下异常：

* **DetuYunAuthorizationException** 401，授权错误
* **DetuYunForbiddenException** 403，权限错误
* **DetuYunNotFoundException** 404，文件或目录不存在
* **DetuYunNotAcceptableException** 406， 目录错误
* **DetuYunServiceUnavailable** 503，系统错误

未包含在以上异常中的错误，将统一抛出 `DetuYunException` 异常。

为了真确处理API请求中可能出现的异常，建议将API操作放在`try{...}catch(Exception $e){…}` 块中


	try{
		$detuyun->getFolderUsage('/demo/');
		...
	}
	catch(Exception $e) {
		echo $e->getCode();		// 错误代码
		echo $e->getMessage();	// 具体错误信息
	}


