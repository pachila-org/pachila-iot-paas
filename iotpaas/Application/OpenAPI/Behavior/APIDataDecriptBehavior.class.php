<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace  OpenAPI\Behavior;
use Think\Behavior;
defined('THINK_PATH') or exit();

/**
 * 再接受到每一个API请求之前作数据解密
 */
class APIDataDecriptBehavior extends Behavior {
	
	
	public function run(&$params){
		
		if (IS_POST) {
			$post = $GLOBALS['HTTP_RAW_POST_DATA'];
			\Think\Log::write('enter APIDataDecriptBehavior '.$post);
			
			// 根据配置，是否需要对数据进行解密
			if (C('API_ENCRIPT')) {
				$data = think_decrypt($post);
			}
			
			// TODO 从data中解析出Post内容，TOKEN,
			
		} else {
			\Think\Log::write('enter APIDataDecriptBehavior GET');
		}
		
		// TODO get token and api key from head
// 		$headers = getallheaders();
// 		foreach ($headers as $key=>$val) {
// 			\Think\Log::write('APIDataDecriptBehavior # '.$key.' = '.$val);
// 		}

		// check token & apiKey
		$headers = getallheaders();
		foreach ($headers as $key=>$val) {
			if ($key=='Authorization') {
				$val = trim($val);
				if (substr_startswith($val, 'Token ')) {
					$token = substr($val, 6);
					$GLOBALS['HTTP_HEAD_AUTH_TOKEN'] = $token;				
				}
				if (substr_startswith($val, 'SignKey ')) {
					$signkey = substr($val, 8);
					$GLOBALS['HTTP_HEAD_AUTH_SignKey'] = $signkey;
				}
			}
		}
		
	}
	
}