<?php
/**
PHP SDK v1.1.5
 */
//设置默认时区
date_default_timezone_set('Asia/Shanghai');

//检测API路径
if(!defined('DETUYUN_API_PATH'))
define('DETUYUN_API_PATH', dirname(__FILE__));


//加载conf.inc.php文件
require_once DETUYUN_API_PATH.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'Exception.php';

//定义软件名称，版本号等信息
define('DETUYUN_NAME','detuyu-sdk-php');
define('DETUYUN_VERSION','1.1.5');
define('DETUYUN_BUILD','201310121010245');
define('DETUYUN_AUTHOR', 'gwb@ouwei.cn');


define("DEFAULT_DETUYUN_HOST",'s.detuyun.com');

define("CONTENT_TYPE",'Content-Type');
define("CONTENT_MD5",'Content-MD5');

    // 缩略图
define("X_GMKERL_THUMBNAIL", 'x-gmkerl-thumbnail');
define("X_GMKERL_TYPE", 'x-gmkerl-type');
define("X_GMKERL_VALUE", 'x-gmkerl-value');
define("X_GMKERL_QUALITY", 'x­gmkerl-quality');



class DetuYun {

    private $_bucket_name;
    private $_username;
    private $_password;
    private $_timeout = 30;

    /**
     * @deprecated
     */
    private $_content_md5 = NULL;

    /**
     * @deprecated
     */
    private $_file_secret = NULL;

    /**
     * @deprecated
     */
    private $_file_infos= NULL;

    protected $endpoint;

	/**
	* 初始化 DetuYun 存储接口
	* @param $bucketname 空间名称
	* @param $username 操作员名称
	* @param $password 密码
    *
	* @return object
	*/
	public function __construct($bucketname, $username, $password, $endpoint = NULL, $timeout = 30) {
		$this->_bucketname = $bucketname;
		$this->_username = $username;
		$this->_password = md5($password);
        $this->_timeout = $timeout;

        $this->endpoint = is_null($endpoint) ? DEFAULT_DETUYUN_HOST : $endpoint;
	}

  
  	 public function makeBucket($path, $auto_mkdir = false) {
        $headers = array('Folder' => 'true');
        $this->_bucket_name = $path;
        return $this->_do_request('PUT', "", $headers);
    }

    /** 
     * 创建目录
     * @param $path 路径
     * @param $auto_mkdir 是否自动创建父级目录，最多10层次
     *
     * @return void
     */
    public function makeDir($path, $auto_mkdir = false) {
        $headers = array('Folder' => 'true');
        if ($auto_mkdir) $headers['Mkdir'] = 'true';
        return $this->_do_request('PUT', $path, $headers);
    }

    /**
     * 删除目录和文件
     * @param string $path 路径
     *
     * @return boolean
     */
    public function delete($path) {
        return $this->_do_request('DELETE', $path);
    }


    /**
     * 上传文件
     * @param string $path 存储路径
     * @param mixed $file 需要上传的文件，可以是文件流或者文件内容
     * @param boolean $auto_mkdir 自动创建目录
     * @param array $opts 可选参数
     */
    public function writeFile($path, $file, $opts = NULL) {
        if (is_null($opts)) $opts = array();
        if (!is_null($this->_content_md5) || !is_null($this->_file_secret)) {
            if (!is_null($this->_content_md5)) $opts[CONTENT_MD5] = $this->_content_md5;
        }

        $this->_file_infos = $this->_do_request('PUT', $path, $opts, $file);

        return $this->_file_infos;
    }

    /**
     * 下载文件
     * @param string $path 文件路径
     * @param mixed $file_handle
     *
     * @return mixed
     */
    public function readFile($path, $file_handle = NULL) {
        return $this->_do_request('GET', $path, NULL, NULL, $file_handle);
    }

    /**
     * 获取目录文件列表
     *
     * @param string $path 查询路径
     *
     * @return mixed
     */
    public function getList($path = '/') {
        $rsp = $this->_do_request('GET', $path);

        $list = array();
        if ($rsp) {
            $rsp = explode("\n", $rsp);
            foreach($rsp as $item) {
	
                @list($name, $type, $size, $time,$filetype) = explode("\t", trim($item));
				if($name == "")
				{
					continue;
				}
                if (!empty($time)) {
                    $type = $type == 'N' ? 'file' : 'folder';
                }

                $item = array(
                    'name' => $name,
                    'type' => $type,
                    'size' => intval($size),
                    'time' => intval($time),
					'filetype'=>$filetype
                );
                array_push($list, $item);
            }
        }

        return $list;
    }

    /**
     * 获取目录空间使用情况
     *
     * @param string $path 目录路径
     *
     * @return mixed
     */
    public function getFolderUsage($path) {
        $rsp = $this->_do_request('GET', $path . '?usage');
        return floatval($rsp);
    }

    /**
     * 获取文件、目录信息
     *
     * @param string $path 路径
     *
     * @return mixed
     */
    public function getFileInfo($path) {
        $rsp = $this->_do_request('HEAD', $path);

        return $rsp;
    }
	/**
     *得到其签名地址
     *
     * @param string $path 路径
     *
     * @return mixed
     */
    public function getFileurl($path,$expires) {
		$headers = array('Signurl' => 'true');
		$headers["Expires"]=$expires;
        $rsp = $this->_do_request('GET', $path,$headers);

        return $rsp;
    }

	/**
	* 连接签名方法
	* @param $method 请求方式 {GET, POST, PUT, DELETE}
	* return 签名字符串
	*/
	private function sign($method, $uri, $date, $length){
        //$uri = urlencode($uri);
		//$sign = "{$method}&{$uri}&{$date}&{$length}&{$this->_password}";
		//return 'DetuYun '.$this->_username.':'.md5($sign);
		
		$sign = "{$uri}&{$date}&{$this->_password}";

		return 'DetuYun '.$this->_username.':'.md5($sign);
	}

    /**
     * HTTP REQUEST 封装
     * @param string $method HTTP REQUEST方法，包括PUT、POST、GET、OPTIONS、DELETE
     * @param string $path 除Bucketname之外的请求路径，包括get参数
     * @param array $headers 请求需要的特殊HTTP HEADERS
     * @param array $body 需要POST发送的数据
     *
     * @return mixed
     */
    protected function _do_request($method, $path, $headers = NULL, $body= NULL, $file_handle= NULL) {
        $uri = "/{$this->_bucketname}{$path}";
        $ch = curl_init("http://{$this->endpoint}{$uri}");
        $_headers = array('Expect:');
        if (!is_null($headers) && is_array($headers)){
            foreach($headers as $k => $v) {
                array_push($_headers, "{$k}: {$v}");
            }
        }

        $length = 0;
		$date = gmdate('D, d M Y H:i:s \G\M\T');

        if (!is_null($body)) {
            if(is_resource($body)){
                fseek($body, 0, SEEK_END);
                $length = ftell($body);
                fseek($body, 0);

                array_push($_headers, "Content-Length: {$length}");
                curl_setopt($ch, CURLOPT_INFILE, $body);
                curl_setopt($ch, CURLOPT_INFILESIZE, $length);
            }
            else {
                $length = @strlen($body);
                array_push($_headers, "Content-Length: {$length}");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }
        else {
            array_push($_headers, "Content-Length: {$length}");
        }

        array_push($_headers, "Authorization: {$this->sign($method, $uri, $date, $length)}");
        array_push($_headers, "Date: {$date}");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_LEAST_RECENTLY_USED);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_NOSIGNAL, true);

        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method == 'PUT' || $method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
        }
        else {
			curl_setopt($ch, CURLOPT_POST, 0);
        }

        if ($method == 'GET' && is_resource($file_handle)) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FILE, $file_handle);
        }

        if ($method == 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 0) throw new DetuYunException('Connection Failed', $http_code);

        curl_close($ch);

        $header_string = '';
        $body = '';

        if ($method == 'GET' && is_resource($file_handle)) {
            $header_string = '';
            $body = $response;
        }
        else {
            list($header_string, $body) = explode("\r\n\r\n", $response, 2);
        }

        //var_dump($http_code);
        if ($http_code == 200) {
            if ($method == 'GET' && is_null($file_handle)) {
                return $body;
            }
            else {
                $data = $this->_getHeadersData($header_string);
                return count($data) > 0 ? $data : true;
            }
        }
        else {

            $message = $this->_getErrorMessage($header_string);
		
            if (is_null($message) && $method == 'GET' && is_resource($file_handle)) {
                $message = 'File Not Found';
            }
            switch($http_code) {
                case 401:
                    throw new DetuYunAuthorizationException($message);
                    break;
                case 403:
                    throw new DetuYunForbiddenException($message);
                    break;
                case 404:
                    throw new DetuYunNotFoundException($message);
                    break;
                case 406:
                    throw new DetuYunNotAcceptableException($message);
                    break;
                case 503:
                    throw new DetuYunServiceUnavailable($message);
                    break;
                default:
                    throw new DetuYunException($message, $http_code);
            }
        }
    }
	
	
    /**
     * 处理HTTP HEADERS中返回的自定义数据
     *
     * @param string $text header字符串
     *
     * @return array
     */
    private function _getHeadersData($text) {
        $headers = explode("\r\n", $text);
        $items = array();
        foreach($headers as $header) {
            $header = trim($header);
			if(strpos($header, 'x-detuyun') !== False){
				list($k, $v) = explode(':', $header);
                $items[trim($k)] = in_array(substr($k,8,5), array('width','heigh','frame')) ? intval($v) : trim($v);
			}
        }
        return $items;
    }

    /**
     * 获取返回的错误信息
     *
     * @param string $header_string
     *
     * @return mixed
     */
    private function _getErrorMessage($header_string) {
		$headers = explode("\r\n", $header_string);
		foreach($headers as $header) {
            $header = trim($header);
			if(strpos($header, 'x-detuyun-error') !== False){
				list($k, $v) = explode(':', $header);
               	return $v;
			}
        }
		return null;
    }

    /**
     * 删除目录
     * @deprecated 
     * @param $path 路径

     */
    public function rmDir($path) {
      return  $this->_do_request('DELETE', $path);
    }

    /**
     * 删除文件
     *
     * @deprecated 
     * @param string $path 要删除的文件路径
     
     */
    public function deleteFile($path) {
        $rsp = $this->_do_request('DELETE', $path);
		return $rsp;
    }

    /**
     * 获取目录文件列表
     * @deprecated
     * 
     * @param string $path 要获取列表的目录
     * 
     * @return array
     */
    public function readDir($path) {
        return $this->getList($path);
    }

    /**
     * 获取空间使用情况
     *
     * @deprecated 直接使用 getFolderUsage('')来获取
     * @return mixed
     */
    public function getBucketUsage() {
        return $this->getFolderUsage('');
    }
	
}
