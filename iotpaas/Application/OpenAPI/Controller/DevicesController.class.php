<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace OpenAPI\Controller;
use  Device\Service\DeviceService;


class DevicesController extends JsonAPIController {
	
	protected $deviceService ;
	
	public function __construct() {
		parent::__construct();
		$this->deviceService = new DeviceService();
	}
	
	/**
	 * 注册一个设备， 该接口目前尚未启用
	 */
	public function registryDevice() {
		\Think\Log::record('enter registryDevice');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$mac = $postData['device_mac'];
		$productCode = $postData['product_code'];
		
		// howard add:post user id
		$uid =  $this->currentUid;
		\Think\Log::record('print mac = '.$mac);
		\Think\Log::record('print productcode = '.$productCode);
		\Think\Log::record('print uid = '.$uid);
		
		// 调用Service registry接口
		// update by howard: add param uid
		$result = $this->deviceService->registry($mac, $productCode,$uid);
		
		// TODO error exception
		
		return $this->returnSuccess($result);
	}
	
	/**
	 *  手机端发起设备注销接口请求
	 */
	public function cancellation() {
		\Think\Log::record('enter cancellation Device');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$uid =  $this->currentUid;
		
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		
		
		// 校验设备与操作用户是否拥有者关系
		\Think\Log::record('check device user owner permission');
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'], 
				'person_id'=>$uid, 'relation_type'=>2))->find();  // 2:设备所有者
		// 2 : 设备所有者
		if (empty($deviceUser)) {
			return $this->returnErrorNoAcl('no ownship on device');
		}
		
		// 向设备发送注销请求
		// TODO 需根据 接入接口调整
		$cancelContent = array('cmd'=>'cancellation');
		$this->deviceService->excuteCmd2Device($device['device_mac'], $this->currentUid, $cancelContent, '2');
		
		// 修改设备的状态至注销状态
		\Think\Log::record('update device to initiate status');
		$device['device_status']=1;  // 1:初始化
		$deviceModel->save($device);
		
		// 清空设备的拥有者，使用者信息， 及其相关信息
		\Think\Log::record('remove all user relationships with this device');
		$deviceUserList = $deviceUserModel->where(array('device_id'=>$device['id']))->select();
		foreach ($deviceUserList as $tmpDeviceUser) {
			if ($tmpDeviceUser['relation_type']=2 || $tmpDeviceUser['relation_type']=3) {
				$deviceUserModel->delete($tmpDeviceUser['id']);
			}
		}
		
		return $this -> returnSuccess($result);
	}
	
	/**
	 * 校验Barcode是否合格，并返回其对应的设备的状态
	 */
	public function barcodeCheck() {
		\Think\Log::record('enter barcode check');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$redis = getRedis();
		$result = array();
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$barcode = $postData['barcode'];
		$uid =  $this->currentUid;
		$result['barcode']=$barcode;
		
		// 解析Barcode是否合理，并从Barcode中取得Device_sn
		// TODO  
		$deviceSn = $barcode;
		$result['device_sn']=$deviceSn;
		
		\Think\Log::record('check device mac exist or not device_sn:'.$deviceSn);
		$devicemacModel = D('Device/DeviceMac');
		$devicemacData = $devicemacModel->where(array('device_sn'=>$deviceSn))->find();
		\Think\Log::record('check mac exist sql:'. $devicemacModel->getLastSql());
		
		if (empty($devicemacData)) {
			return $this->returnErrorNotExist($result);
		}
		
		\Think\Log::record('check device data exist or not device_sn:'.$deviceSn);
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {		
			$result['device_status']=1; // 1:初始化
			$result['online_status']=0; // 0:不在线
			return $this->returnSuccess($result);
		} else {
			$result['device']=$device;
		}

		\Think\Log::record('check device user relation device_sn:'.$deviceSn);
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],'person_id'=>$uid))->find();
		if (empty($deviceUser)) {
			$result['user_relation'] = 4; // 4:没有关系
		} else {
			$result['user_relation'] = $deviceUser['relation_type']; 
		}
		
		// 从redis 获取online status
		/*
		$online_status = $redis->hget($device['device_mac'], 'online_status');
		if (!empty($online_status)) {
			$result['online_status']=$online_status;			
		} else {
			$result['online_status']=0;  // 0:不在线
		}
		*/
		
		// 检查该设备的当前状态
		return $this -> returnSuccess($result);
	}
	
	/**
	 * 获取当前用户设备列表
	 */
	public function listDevices() {
		\Think\Log::record('enter listDevices');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$redis = getRedis();
		$deviceUserModel = D('Device/DeviceUser');
		$deviceModel = D('Device/Device');
		$productModel = D('Product/Product');
		$pictureModel = D('Admin/Picture');
		
		// 检验当前用户是否某产品的创造者，如果是创造者，则返回所有该产品的设备
		
// 		$productModel->find();
		
		// 根据用户获取其有权限访问的Device列表
		$condition['person_id'] = $this->currentUid;
		$condition['relation_type'] = array('IN', array(1, 2, 3));
		$deviceUserList = $deviceUserModel->where($condition)->order('reg_time asc')->select();
		
		foreach ($deviceUserList as &$deviceUser) {
			$deviceData = $deviceModel->find($deviceUser['device_id']);
			$productData = $productModel->find($deviceData['product_id']);
			$pictureData = $pictureModel->find($productData['logo_img']);
			$deviceUser['device']=$deviceData;
			$deviceUser['product']=$productData;
			$deviceUser['picture']=$pictureData;
			
			$deviceStatus = array();
			$deviceStatus['online_status']=$redis->hget($deviceData['device_mac'], 'online_status');
			$deviceUser['status']=$deviceStatus;
		}
		
		return $this->returnSuccess($deviceUserList);
	}
	
	/**
	 * 获取设备详细信息， 包含设备对应的产品详细信息
	 */
	public function getDevice($deviceSn) {
		\Think\Log::record('enter getDevice');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 取得设备信息。
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($id);
		}
		
		// 取得设备的产品信息
		$productModel = D('Product/Product');
		\Think\Log::record('product_id in getDevice = '.$device['product_id']);
		
		$product = $productModel->find($device['product_id']);
		
		// 判断当前用户是否是产品的Owner
		$deviceUserModel = D('Device/DeviceUser');
		$condition = array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid
		);
		$relationData = $deviceUserModel->where($condition)->find();
		
		$result = array();
		$result['device']=$device;
		$result['product']=$product;
		$result['relation_type']=$relationData['relation_type'];
		
		return $this->returnSuccess($result);
	}
	
	/**
	 * 获取产品的详细定义信息 
	 */
	public function getProductDetail($productId){
		\Think\Log::record('enter listDevices');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$redis = getRedis();
		$result = array();
		
		$productModel = D('Product/Product');
// 		$metadataModel=D();
		$mdEnumModel=D('Proudct/ProductEnum');
		
		
		$product = $productModel->find($productId);
		if (empty($product)) {
			return $this->returnErrorNotExist(array('product_id'=>$productId));
		}
		$result['product']=$product;
		$mdDatas=$productModel->getMetatDataList($product['id']);
		
		foreach ($mdDatas as &$mdData) {
			$whereMap = array('product_id'=>$product['id'], 'metadata_id'=>$mdData['metadata_id']);
			$enumDetails = $mdEnumModel->where($whereMap)->order('enum_key asc')->select();
			
			if (!empty($enumDetails)) {
				$mdData['enum_details'] = $enumDetails;
			}
		}
		
		$result['metadatas']=$mdDatas;
		
		return $this->returnSuccess($result);
		
	}
	
	/**
	 * 修改设备的名称，只有Owner可以操作
	 */
	public function updateDeviceName() {
		\Think\Log::record('enter updateDeviceName');
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$postData = $this->getJsonPostContent();
		
		$deviceSn = $postData['device_sn'];
		$deviceName = $postData['device_name'];
		
		// 校验设备是否有效
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
		
		// 检验当前用户是否是设备的拥有者
		\Think\Log::record('check current user is device owner or not');
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid, 'relation_type'=>2))->find();  // 2:设备所有者
		if (empty($deviceUser)) {
			return $this->returnErrorNoAcl('no ownship on device');
		}
		
		// 设定新的名字
		$device['device_name'] = $deviceName;
		$deviceModel->save($device);
		
		return $this->returnSuccess();
	}
	
	
	/**
	 * 将设备绑定到当前用户名下
	 */
	public function bindDeviceOwner() {
		\Think\Log::record('enter bindDeviceOwner');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$deviceSn = strtoupper($deviceSn);
		$uid =  $this->currentUid;
		
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		
		\Think\Log::record('check device status');
		// 检验当前的STATUS是否为激活状态
		if ($device['device_status'] != 2) {
			return $this->returnError("NO_AVAILABLE_STATUS");
		}
		
		// 发送消息去设备申明绑定状态
		// TODO 需根据 接入接口调整
		// $result = $this->deviceService->excuteCmd2Device($deviceSn, '', '2');
		
		// 校验设备与操作用户是否拥有者关系
		$deviceUserModel = D('Device/DeviceUser');
		$condition = array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid
		);
		$deviceUserModel->where($condition)->delete();
		
		$data = array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid,
				'relation_type'=>2 // 2:设备所有者
		);
		$result = $deviceUserModel->add($data);
		
		$device['device_status']=3; // 3:绑定状态
		$deviceModel->save($device);
		
		return $this->returnSuccess($result);
	}
	
	/**
	 * 将设备绑定到当前用户名下
	 */
	public function unbindDeviceOwner() {
		\Think\Log::record('enter unbindDeviceOwner');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$uid =  $this->currentUid;
		
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		// 检验当前的STATUS是否为绑定状态
		if ($device['device_status'] != 3) {
			return $this->returnError("NO_AVAILABLE_STATUS");
		}

		// 校验设备与操作用户是否拥有者关系
		$deviceUserModel = D('Device/DeviceUser');
		$condition = array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid
		);
		$deviceUserList = $deviceUserModel->where($condition)->select();
		foreach ($deviceUserList as $deviceuser) {
			if ($deviceuser['relation_type'] == 2) { // 2:设备所有者
				// 发送消息去设备申明绑定状态
				// TODO 需根据 接入接口调整
				// $result = $this->deviceService->excuteCmd2Device($deviceSn, '', '2');
				$device['device_status']=2; // 2: 激活
				$deviceModel->save($device);
				
			} else if ($deviceuser['relation_type'] == 1) { // 3:设备使用者
				// donothing here
			}
		}
		$result = $deviceUserModel->where($condition)->delete();
		
		return $this->returnSuccess();
	}
	
	/**
	 * 获取设备的使用者信息
	 * @param unknown $id
	 */
	public function getDeviceUsers($deviceSn) {
		\Think\Log::record('enter getDeviceUsers');
	
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
	
		\Think\Log::record('check device exist or not');
		$device = D('Device/Device')->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
	
		// 获取列表信息
		$deviceUserModel = D('Device/DeviceUser');
		$condition['device_id'] = $device['id'];
		$condition['relation_type'] = array('IN', array(2,3)); //2:设备所有者  3:设备使用者
		
		$deviceUsers = $deviceUserModel->where($condition)->order('reg_time asc')->select();
		\Think\Log::record('search deviceuser sql='.$deviceUserModel->getLastSql());
		
		// 丰富用户的相关的信息
		$userModel = D('Common/Member');		
		foreach ($deviceUsers as &$deviceUser) {
			$deviceUser['person']=$userModel->find($deviceUser['person_id']);
		}
	
		return $this->returnSuccess($deviceUsers);
	}
	
	/**
	 * 申请将自己添加为设备添加使用者
	 */
	public function applyDeviceUser() {
		\Think\Log::record('enter applyDeviceUser');
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
	
		$postData = $this->getJsonPostContent();
	
		$deviceSn = $postData['device_sn'];
	
		// 校验设备是否有效
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
		if ($device['device_status'] != 3) {
			return $this->returnError('NO_AVAILABLE_STATUS', $deviceSn);
		}
	
		// 添加当前用户为设备的使用者
		\Think\Log::record('add device user data');
		$deviceUserModel = D('Device/DeviceUser');
		
		$isOwner = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid, 'relation_type'=>2))->find();
		if (!empty($isOwner)) {
			return $this->returnSuccess('ALERDY_IS_OWNER');
		}
		
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid, 'relation_type'=>3))->find();
		if (empty($deviceUser)) {
			$data = array('device_id'=>$device['id'],
					'person_id'=>$this->currentUid,
					'relation_type'=>3 // 3:设备使用者
			);
				
			$result = $deviceUserModel->add($data);
			return $this->returnSuccess($result);
		} else {
			return $this->returnSuccess('ALERDY_IS_USER');
		}
	}
	
	
	/**
	 * 为设备添加使用者，只有设备的Owner才可以做这个操作
	 */
	public function authDeviceUser() {
		\Think\Log::record('enter authDeviceUser');
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$postData = $this->getJsonPostContent();
		
		$deviceSn = $postData['device_sn'];
		$deviceSn = strtoupper($deviceSn);
		$uid = $postData['uid'];
		
		// 校验设备是否有效
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
		if ($device['device_status'] != 3) {
			return $this->returnError('NO_AVAILABLE_STATUS', $deviceSn);
		}
		
		// 检验当前用户是否是设备的拥有者
		\Think\Log::record('check current user is device owner or not');
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid, 'relation_type'=>2))->find();  // 2:设备所有者
		if (empty($deviceUser)) {
			return $this->returnErrorNoAcl('no ownship on device');
		}
		
		// 添加指定用户为设备的使用者
		\Think\Log::record('add device user data');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$uid, 'relation_type'=>3))->find();
		if (empty($deviceUser)) {
			$data = array('device_id'=>$device['id'],
					'person_id'=>$uid,
					'relation_type'=>3 // 3:设备使用者
			);
			
			$result = $deviceUserModel->add($data);
			return $this->returnSuccess($result);
		} else {
			return $this->returnSuccess();
		}
		
		
	}
	
	/**
	 * 对设备取消授权
	 */
	public function unAuthDeviceUser() {
		\Think\Log::record('enter unauthDeviceUser');
		
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$postData = $this->getJsonPostContent();		
		$deviceSn = $postData['device_sn'];
		$uid = $postData['uid'];
		
		// 校验设备是否有效
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
		if ($device['device_status'] != 3) {
			return $this->returnError('NO_AVAILABLE_STATUS', $deviceSn);
		}
		// 检验当前用户是否是设备的拥有者
		\Think\Log::record('check device ownership uid='.$uid);
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUser = $deviceUserModel->where(array('device_id'=>$device['id'],
				'person_id'=>$this->currentUid, 'relation_type'=>2))->find();  // 2:设备所有者
		if (empty($deviceUser)) {
			return $this->returnErrorNoAcl('no ownship on device');
		}
		
		// 添加指定用户为设备的使用者
		\Think\Log::record('delete device user');
		$condition = array('device_id'=>$device['id'],'person_id'=>$uid);		
		$deviceUserModel->where($condition)->delete();
		\Think\Log::record('delete device user sql='.$deviceUserModel->getLastSql());
		
		return $this->returnSuccess();
	}
	
	/**
	 * 获取设备当前的所有的运行状态信息
	 */
	public function getDeviceStatus($deviceSn) {
		\Think\Log::record('enter getDeviceStatus - '.$deviceSn);
		
		$result = array();
		$redis = getRedis();
		try {
			$mac = $deviceSn; // TODO need changed
			
			$alldata = $redis->hgetall($mac);			
			// 解析所有的数据		
			return $this->returnSuccess($alldata);
		} finally {
			releaseRedis($redis);
		}
	}
	
	/**
	 * 执行设备指令(异步执行）
	 */
	public function asynCommand() {
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
	
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$mdCode =  $postData['md_code'];
		$mdValue = $postData['md_value'];
	
		\Think\Log::write('$mdCode='.$mdCode);
	
	
		// 判断设备是否存在
		$deviceData = D('Device/Device')->where(array('device_sn'=>$deviceSn))->find();
		if (empty($deviceData)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
	
		// 调用 Service
		$result = $this->deviceService->asynCommand($deviceData['device_mac'], $this->currentUid, $mdCode, $mdValue);
		if (!$result) {
			return $this->returnError('COMMAND_EXCUTE_ERROR');
		} else {
			return $this->returnSuccess($result);
		}
	}
	
	/**
	 * 执行设备指令(同步执行）
	 */
	public function synCommand() {
		// 处理入参
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
	
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$mdCode =  $postData['md_code'];
		$mdValue = $postData['md_value'];
	
		// 判断设备是否存在
		$deviceData = D('Device/Device')->where(array('device_sn'=>$deviceSn))->find();
		if (empty($deviceData)) {
			return $this->returnErrorNotExist(array('device_sn'=>$deviceSn));
		}
	
		// 调用 Service
		$result = $this->deviceService->synCommand(device_sn, $mdCode, $mdValue);
	
		if (!$result) {
			return $this->returnError('COMMAND_EXCUTE_ERROR');
		} else {
			return $this->returnSuccess($result);
		}
	}
	
	/**
	 * 发起OTA 升级指令
	 */
	public function requestOTA() {
		\Think\Log::record('enter requestOTA');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$deviceSn = $postData['device_sn'];
		$uid =  $this->currentUid;
		
		// 前提判断检验
		\Think\Log::record('Device check');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		// 检验当前的STATUS是否为未激活状态
		if ($device['device_status'] == 1) {  // 1:初始化  
			return $this->returnError("NO_AVAILABLE_STATUS", $deviceSn);
		}
		
		// 发送OTA升级指令到设备
		// TODO 需根据 接入接口调整
		\Think\Log::record('Send ota command to device');
		$result = $this->deviceService->excuteCmd2Device($deviceSn, 'OTA', '2');
		
		// 初始化本地设备OTA记录
		\Think\Log::record('Log to OTA log');
		$otaLogData = array('device_id'=>$device['id'],
				'ota_status'=>1,  // 1:开始下载
				'request_uid'=>$this->currentUid
		);
		$otaLogModel = D('Device/OtaLog');
		$otaLogModel->add($otaLogData);
		
		return $this->returnSuccess();
	}
	
	
	/**
	 * OTA 状态查询
	 */
	public function getOTAStatus($deviceSn) {
		\Think\Log::record('enter getOTAStatus');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 前提判断检验
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		
		// 查询OTA记录的状态
		$otaLogModel = D('Device/OtaLog');
		$condition['device_id'] = $device['id'];
		
		$otaLogData = $otaLogModel->where($condition)->order('update_time desc')->find();
		if (empty($otaLogData)) {
			return $this->returnSuccess('');
		} else {
			return $this->returnSuccess($otaLogData['ota_status']);
		}
		
		
	}
	
	/**
	 * 获取设备历史的故障信息
	 * @param unknown $id
	 */
	public function getDeviceHistoryErrs($id) {
		// TODO
	}
	
	/**
	 * 获取设备的错误详细信息
	 * @param unknown $id
	 */
	public function getMessageDetail($id) {
		// TODO
	}
	
	/**
	 * 检查最新的APP版本
	 * TODO
	 */
	public function checkAppVersion() {
	}
	
	
}

