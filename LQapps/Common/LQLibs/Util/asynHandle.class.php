<?php
/*
 * 异步处理类
 * @author lkk/lianq.net
 * @create on 10:05 2012-7-30
 * @example:
 *	$obj	= new asynHandle();
 *	$obj->Request('http://google.com');
 *	$obj->Get('http://google.com');
 */

namespace LQLibs\Util;
class asynHandle {
	public		$url		= '';		//传入的完整请求url,包括"http://"或"https://"
	public		$cookie		= array();	//传入的cookie数组,须是键值对
	public		$post		= array();	//传入的post数组,须是键值对
	public		$timeout	= 30;		//超时秒数
	public		$result		= '';		//获取到的数据
	
	private		$gzip		= true;		//是否开启gzip压缩
	private		$fop		= NULL;		//fsockopen资源句柄
	private		$host		= '';		//主机
	private		$port		= '';		//端口
	private		$referer	= '';		//伪造来路
	private		$requestUri	= '';		//实际请求uri
	private		$header		= '';		//头信息
	
	private		$block		= 1;		//网络流状态.1为阻塞,0为非阻塞
	private		$limit		= 128;		//读取的最大字节数	
	
	//构造函数
	public function __construct(){
		ignore_user_abort(TRUE);//忽略用户中断.如果客户端断开连接,不会引起脚本abort
		//set_time_limit(0);//取消脚本执行延时上限
	}
	
	//解析URL并创建资源句柄
	private function analyzeUrl(){
		if ($this->url == ''){return false;}
		$url_array = parse_url($this->url);
		!isset($url_array['host']) && $url_array['host'] = '';     
        !isset($url_array['path']) && $url_array['path'] = '';     
        !isset($url_array['query']) && $url_array['query'] = '';     
        !isset($url_array['port']) && $url_array['port'] = 80;
		
		$this->host			= $url_array['host'];
		$this->port			= $url_array['port'];
		$this->referer		= $url_array['scheme'].'://'.$this->host.'/';
		$this->requestUri	= $url_array['path'] ? 
							$url_array['path'].($url_array['query'] ? '?'.$url_array['query'] : '') : '/';
		
		switch($url_array['scheme']){
			case 'https':
				$this->fop	= fsockopen('ssl://'.$this->host, 443, $errno, $errstr, $this->timeout);
				break;
			default:
				$this->fop	= fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
				break;
		}
		
		if(!$this->fop){
			$this->result	= "$errstr ($errno)<br />\n";
			return false;
		}
		return true;
	}//analyzeUrl end
	
	//拼装HTTP的header
	private function assHeader(){
		$method = empty($this->post) ? 'GET' : 'POST';
		$gzip = $this->gzip ? 'gzip, ' : '';
		
		//cookie数据
		if(!empty($htis->cookie)){
			$htis->cookie = http_build_cookie($htis->cookie);
        }
		
		//post数据
		if(!empty($this->post)){			
			$this->post = http_build_query($this->post);
		}
		
		$header	= "$method $this->requestUri HTTP/1.0\r\n";
		$header	.= "Accept: */*\r\n";
		$header	.= "Referer: $this->referer\r\n";
		$header	.= "Accept-Language: zh-cn\r\n";
		if(!empty($this->post)){
			$header	.= "Content-Type: application/x-www-form-urlencoded\r\n";
		}
		$header	.= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header	.= "Host: $this->host\r\n";
		if(!empty($this->post)){
			$header	.= 'Content-Length: '.strlen($this->post)."\r\n";
		}
		$header	.= "Connection: Close\r\n";
		$header	.= "Accept-Encoding: {$gzip}deflate\r\n";
		$header	.= "Cookie: $this->cookie\r\n\r\n";
		$header	.= $this->post;
		$this->header	= $header;
	}//assHeader end
	
	//返回状态检测,301、302重定向处理
	private function checkRecvHeader($header){
		if(strstr($header,' 301 ') || strstr($header,' 302 ')){//重定向处理
			preg_match("/Location:(.*?)$/im",$header,$match);
            $url = trim($match[1]);
            preg_match("/Set-Cookie:(.*?)$/im",$header,$match);
			$cookie	= (empty($match)) ? '' : $match[1];
			
			$obj			= new asynHandle();
			$result			= $obj->Get($url, $cookie, $this->post);
			$this->result	= $result;
			return $result;
		}elseif(!strstr($header,' 200 ')){
			//找不到域名或网址
			return false;
		}else return 200;
	}//checkRecvHeader end
	
	//gzip解压
	private function gzdecode($data){
		$flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16) $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2) $headerlen += 2;
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE) $unpacked = $data;
        return $unpacked;
	}//gzdecode end
	
	//请求函数,只请求,不返回
	public function Request($url, $cookie=array(), $post=array(), $timeout=3){
		$this->url		= $url;
		$this->cookie	= $cookie;
		$this->post		= $post;
		$this->timeout	= $timeout;
		
		if(!$this->analyzeUrl()){
			return $this->result;
		}
		$this->assHeader();
		
		stream_set_blocking($this->fop, 0);//非阻塞,无须等待
		fwrite($this->fop, $this->header);
		fclose($this->fop);
		return true;
	}//Request end
	
	//获取函数,请求并返回
	public function Get($url, $cookie=array(), $post=array(), $timeout=30){
		$this->url		= $url;
		$this->cookie	= $cookie;
		$this->post		= $post;
		$this->timeout	= $timeout;
		
		if(!$this->analyzeUrl()){
			return $this->result;
		}
		$this->assHeader();
		
		stream_set_blocking($this->fop, $this->block);   
        stream_set_timeout($this->fop, $this->timeout);
		fwrite($this->fop, $this->header);
		$status = stream_get_meta_data($this->fop);
		
		if(!$status['timed_out']){
			$h='';
			while(!feof($this->fop)){
				if(($header = @fgets($this->fop)) && ($header == "\r\n" ||  $header == "\n")){
					break;
				}
				$h .= $header;
			}
			$checkHttp	= $this->checkRecvHeader($h);
			if($checkHttp!=200){return $checkHttp;}
			
			$stop = false;
			$return = '';
			$this->gzip = false;
			if(strstr($h,'gzip')) $this->gzip = true;
			while(!($stop && $status['timed_out'] && feof($this->fop))){
				if($status['timed_out']) return false;
				$data = fread($this->fop, ($this->limit == 0 || $this->limit > 128 ? 128 : $this->limit));  
				if($data == ''){//有些服务器不行,须自行判断FOEF
					break;
				}
				$return	.= $data;
				if($this->limit){
					$this->limit -= strlen($data);
					$stop = $this->limit <= 0;
				}
				
			}
			@fclose($this->fop);
			$this->result	= $this->gzip ? $this->gzdecode($return) : $return;
			return $this->result;
		}else{
			return false;
		}
	}//Get end
}