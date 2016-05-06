<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace OpenAPI\Controller;
use \Think\Controller\RestController;

class JsonAPIController extends RestController {
	const API_RETURN_SUCCESS = '100';
	const API_RETURN_ERROR = '200';
	const API_ACCESS_ERROR = '210';
	
	protected $allowMethod = array('get', 'post', 'put');
	protected $allowType = array('json');
	
	protected $token = '';
	protected $currentUid = '';
	
	
	protected function returnSuccess($data) {
		$result = array('code'=>self::API_RETURN_SUCCESS, 'msg'=>'', 'detail_code'=>'' );
		if (!empty($data)) {
			$result['result'] = $data;
		}
		
		return $this->response($result, 'json');
	}
	
	protected function returnError($detailCode, $msg='', $data='') {
		if (empty($msg)) {
			$msg = getErrorMsg($detailCode);
		}
		
		$result = array('code'=>self::API_RETURN_ERROR, 'msg'=>$msg, 'detail_code'=>$detailCode );
		if (empty($data)) {
			$result['result'] = $data;
		}
	
		return $this->response($result, 'json');
	}
	
	protected function returnErrorToken($data='') {
		$this ->returnError('TOKEN_EMPIRED', '令牌已经失效', $data);		
	}
	
	protected function returnErrorNotExist($data='') {
		$this ->returnError('DATA_NOT_EXIST', '该数据不存在', $data);
	}
	
	protected function returnErrorNoAcl($data='') {
		$this ->returnError('USER_NO_AUTHORITY', '当前用户没有权限进行该操作', $data);
	}
	
	protected function getJsonPostContent() {
		return json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
	}
	
	protected function tokenValidation() {
		
		// get uid from current token
		$this->token = $GLOBALS['HTTP_HEAD_AUTH_TOKEN'];
		\Think\Log::write('token = '.$this->token);
		if (empty($this->token)) {
			return false;
		}
		
		$model = D('user_token');
		$userToken = $model->where(array('token'=>$this->token))->find();
		\Think\Log::write('Last sql = '.$model->getLastSql());
		\Think\Log::write('user token = '.$userToken);
// 		\Think\Log::write('user id = '.$userToken['uid']);
		if (empty($userToken)) {
			\Think\Log::write('no user token');
			return false;
		}
		$this->currentUid = $userToken['uid'];
		return true;
	}
	
}