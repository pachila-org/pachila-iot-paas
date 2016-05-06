<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

/**
 * 对第3方传入的密码参数进行解析，密码在传输过程中按照规则加密，防止传输中被截获并破解
 *  
 * @param unknown $encriptPWD
 * @return string
 */
function decript_password($encriptPWD) {
	if (empty($encriptPWD)) {
		return false;
	}
	
	// 解码 BASE64 压缩
	$result = base64_decode($data);
	if ($result === false) {
		return false;
	} 
	// 去除混淆字（第2位，第3位，第6位）
// 	if (strlen($result) <6) {
// 		return false;
// 	}
	$array = str_split($result);
	if (count($array) <6) {
		return false;
	}
	array_splice($array,5,1);
	array_splice($array,2,1);
	array_splice($array,1,1);
	
	return join('', $pieces);
}

function getErrorMsg($code) {
	\Think\Log::write('code = '.$code);
	if (empty($code)) {
		return $code;
	}
	$list = C('API_ERROR_MSG');
	foreach ($list as $key=>$value ){
		\Think\Log::write('key = '.$key .' value='.$value);
		if ($key === $code) {
			return $value;
		}
	}
	return $code;
}




