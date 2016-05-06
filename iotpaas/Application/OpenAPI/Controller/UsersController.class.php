<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace OpenAPI\Controller;

use User\Api\UserApi;
use User\Model\UcenterMemberModel;
/**
 * 用户相关的开放接口
 */

class UsersController extends JsonAPIController {
	
	/**
	 * 用户名登录
	 * 方法：POST
	 * POST参数: username
	 * POST参数: password 
	 */
	public function login(){
		$result = array();
		$redis = getRedis();
		\Think\Log::write('enter login 1 ');
		
		try {
			// 处理入参
			$input =  $this->getJsonPostContent();
			$username = $input['username'];
	// 		$password = decript_password($input['password']); // 密码参数是混淆加密过的，需要解密。
			$password = $input['password'];
			if (!$password ) {
				return $this->returnError('PASSWORD_ERR_ENCRIPT');
			}
			\Think\Log::write('enter login 2 $username='.$username.'  $password='.$password);
			
			// 检查用户是否存在，并判断该用户是否被锁定, 检查密码是否正确
			$userApi = new UserApi();
			$uid = $userApi->login($username, $password);
			\Think\Log::write('enter login 3 ');
			if ($uid < 0) {
				return $this->returnError('LOGIN_FAIL', $userApi->getErrorMsg($uid));
			}
					
			// 创建登陆Token，以及登陆日志
			$userToken = D('user_token')->where('uid=' . $uid)->find();
			\Think\Log::write('enter login 4');
			if ($userToken == null) {
				$token = build_auth_key();
				$data['token'] = $token;
				$data['time'] = time();
				$data['uid'] = $uid;
				D('user_token')->add($data);
			} else {
				$token = $userToken['token'];
			}
			
			\Think\Log::write('enter login 5 ');
			action_log('user_login', 'member', $uid, $uid);
			
			// 构造返回信息，并返回
			$result['token'] = $token;
			$user  = query_user(array('uid','username', 'email','mobile', 'nickname', 'avatar32', 'avatar128'), $uid);
			$result['user']=$user;
			\Think\Log::write('enter login 6 ');
			
			$redis -> set($token, $user, 'token');
			
			return $this->returnSuccess($result);
		} finally {
			\Think\Log::write('enter login 7');
			releaseRedis($redis);
		}
	}
	
	/** 通过第三方账号进行登录 */
	public function login3rd() {
		$result = array();
		$redis = getRedis();
		\Think\Log::write('enter login 1 ');
		
		try {
			// 处理入参
			$input =  $this->getJsonPostContent();
			$unionId = $input['union_id'];
			$nickName = $input['nick_name'];
			$loginType = $input['login_type'];
			$image = $input['avatar_url'];
			
			\Think\Log::write('enter login3rd 1 $unionId='.$unionId.' $nickname='.$nickName);
			
			// 根据原来 登录名为$unionId的用户是否存在， 
// 			D('member')->where(array('uid'=>$uc_user[0]))->setField('nickname',$uc_user[1]);
			$umemberModel = new UcenterMemberModel();
			$user = $umemberModel->where(array('username'=>$unionId))-> find();
			
			if (null == $user) {
				// 如果不存在， 创建该用户
				\Think\Log::write('login3rd create user');
				$uid = $umemberModel->register($unionId, $nickName, $unionId, '', '', $loginType);
				if ($uid <0) {
					return $this->returnError('REGISTRY_ERROR', $userApi->getErrorMsg($result));
				}
				
			} else {
				\Think\Log::write('login3rd user already exist');
				$uid = $user['id'];
			}
			
			//  根据用户的ID来创建登录token
			$userToken = D('user_token')->where('uid=' . $uid)->find();
			\Think\Log::write('enter login 4');
			if ($userToken == null) {
				$token = build_auth_key();
				$data['token'] = $token;
				$data['time'] = time();
				$data['uid'] = $uid;
				D('user_token')->add($data);
			} else {
				$token = $userToken['token'];
			}
			
			\Think\Log::write('enter login 5 ');
			action_log('user_login', 'member', $uid, $uid);
				
			// 构造返回信息，并返回
			$result['token'] = $token;
			$user  = query_user(array('uid','username', 'email','mobile', 'nickname', 'avatar32', 'avatar128'), $uid);
			$result['user']=$user;
			\Think\Log::write('enter login 6 ');
				
			$redis -> set($token, $user, 'token');
				
			return $this->returnSuccess($result);
		} finally {
			\Think\Log::write('enter login 7');
			releaseRedis($redis);
		}
	}
	
	
	/**
	 * 获取用户信息
	 * 方法：GET
	 */
	public function profile_get($uid=0) {
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
 		
		// 参数校验
		if ($uid == 0) {
			$uid = $this->currentUid;
		}
		
		// 获取相关用户信息
		$user  = query_user(array('uid','username', 'email','mobile', 'nickname', 'avatar32', 'avatar128'), $uid);
		
		// 构造返回数据
		return $this->returnSuccess($user);
	}

	
	/**
	 * 判断用户名是否被使用
	 * 方法：GET
	 */
	public function checkUserName($username) {
		$userApi = new UserApi();
		$result = $userApi->checkUsername($username);
		if ($result < 0) {
			return $this->returnError('USERNAME_ERROR', $userApi->getErrorMsg($result));
		}
		
		return $this->returnSuccess($username);
	}
	
	/**
	 * 用户注册
	 * 方法：POST
	 */
	public function registry() {
		// 获取参数并校验
		$input =  $this->getJsonPostContent();
		$username = $input['username'];
		$password = $input['password'];
		$nickname = $username;
		$mobile = '';
		$email = '';
		
		check_username($username, $email, $mobile, $type);
		$result = array();

		// 通过手机来注册,校验用户名是否存在
		$userApi = new UserApi();
		\Think\Log::record('Username check = '.$userApi->checkUsername($username));
		if (!empty($username)) {
			$checkMsg = $userApi->checkUsername($username);
			if (!$checkMsg) return $this->returnError('USERNAME_CHECK_ERROR', $checkMsg);
		}
		
		if (!empty($mobile)) {
			$checkMsg = $userApi->checkMobile($mobile);
			if (!$checkMsg) return $this->returnError('MOBILE_CHECK_ERROR', $checkMsg);
		}
		
		if (!empty($email) && ($userApi->checkEmail($email))) {
			$checkMsg = $userApi->checkEmail($email);
			if (!$checkMsg) return $this->returnError('EMAIL_ALREADY_EXIST', $checkMsg);
		}
		
		$uid = $userApi ->register($username, $nickname, $password, $email, $mobile);
		if ($uid <0) {
			return $this->returnError('REGISTRY_ERROR', $userApi->getErrorMsg($result));
		}
		
		// create token
		$token = build_auth_key();
		$data['token'] = $token;
		$data['time'] = time();
		$data['uid'] = $uid;
		D('user_token')->add($data);
		// 查询用户
		$user  = query_user(array('uid','username', 'email','mobile', 'nickname', 'avatar32', 'avatar128'), $uid);

		$result['token'] = $token;
		$result['user']=$user;
		
		// 构造返回数据
		return $this->returnSuccess($result);
	}
	
	/**
	 * 更改当前用户的密码
	 */
	public function updatePasswd($oldPwd, $newPwd){
		\Think\Log::write('enter updatePasswd ');
		
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 解析参数
		$input =  $this->getJsonPostContent();
		$oldPwd = $input['old_password'];
		$newPwd = $input['new_password'];
		// TODO 需添加密码Decript的过程，但需更APP端定义一致的加密逻辑
		
		$userApi = new UserApi();
		$userApi -> changePassword($this->currentUid, $oldPwd, $newPwd);
		$this->returnSuccess();
	}
	
	/**
	 * 更改当前用户的信息：NickName
	 * 方法：POST
	 */
	public function updateNickname(){
		\Think\Log::write('enter updateNickname ');
		
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		// 解析参数
		$input =  $this->getJsonPostContent();
		$nickName = $input['nick_name'];
		
		$memberModel = D('Common/Member');
		$updateData = $memberModel->where(array('uid'=>$this->currentUid))->find();
		$updateData['nickname'] = $nickName;
		
		$result = $memberModel->save($updateData);
		\Think\Log::write('enter updateNickname update result='.$result);
		
		if ($result) {
			$this->returnSuccess();
		} else {
			$ucmemberModel = new UcenterMemberModel();
			$this->returnError('NICK_NAME_ERR', $ucmemberModel->getErrorMessage($updateData) );
		}
	}
	
	/**
	 * 修改用户信息
	 * 方法：POST
	 */
	public function updateAvatar(){
		// 解析参数
		// 判断用户信息是否更新
		// 判断用户头像是否更新
		// TODO
		$this->returnSuccess();
	}
	
	/**
	 * 修改用户信息
	 * 方法：POST
	 */
	public function updateLocation(){
		// 解析参数
		// 判断用户信息是否更新
		// 判断用户头像是否更新
	}
	
	
	/**
	 * 注册－发送验证码
	 * 方法：POST
	 * POST参数: mobile：mobile no 
	 */
	public function sendVerifySMS() {
		// 生成Verify的随机码, 并插入Verify表
		$input =  $this->getJsonPostContent();
		$mobile = $input['mobile'];
		$smscode = '';
		
		// 调用手机发消息接口，发送消息
		// TODO
		$this->returnSuccess();
	}
	
	/**
	 * 注册－校验验证码	
	 * 方法：POST
	 * POST参数: mobile：mobile no
	 * Return: 校验码错误，返回错误
	 *         校验码正确，如果该手机对应的用户存在，返回成功并带上用户信息
	 *         校验码正确，如果该手机对应的用户不存在，只返回成功。
	 */
	public function checkVerifySMS(){
		// 校验手机号和验证码是否匹配
		$input =  $this->getJsonPostContent();
		$mobile = $input['mobile'];
		$smscode = $input['smscode'];
		
		// TODO
		
		// 查询手机号对应的用户是否存在，并构造返回值
		$this->returnSuccess();
	}
	
	/**
	 * 登出
	 * 方法：POST
	 */
	public function logout() {
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		\Think\Log::write('enter logout token:'.$this->token);
		$redis = getRedis();
		try {
			// 删除Token
			$model = D('user_token');
			$result = $model->where(array('token'=>$this->token))->delete();
			\Think\Log::write('Last logout sql = '.$model->getLastSql());
			
			$redis->rm($this->token, 'token');
			
			$this->returnSuccess();
		} finally { 
			releaseRedis($redis);
		}
	}
	

}

