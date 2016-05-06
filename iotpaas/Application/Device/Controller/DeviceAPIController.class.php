<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Device\Controller;

use Think\Controller\RestController;
use Device\Service\DeviceService;

class DeviceAPIController extends RestController {
	
	protected $deviceService;
	
	public function __construct() {
		parent::__construct();
		$this->deviceService = new DeviceService();
	}
	
	public function urlroot($key) {
		\Think\Log::write('Enter urlroot '.$key);
		$root = C('DEMO_HOST');
		if (empty($root)) {
			$root = "120.27.4.46";
		}
		$result = array('ip'=>$root);
		
		$this->response($this->_successData($result), 'json');
	}
	
	/**
	 * 提供给接入层当设备首次接入时进行注册（WIFI设备一般在配网成功后发起)
	 * device_mac
	 * product_code
	 * access_key
	 */
	public function activation() {
		\Think\Log::write('Enter registDevice');
		$post = $GLOBALS['HTTP_RAW_POST_DATA'];
		\Think\Log::write('Enter registDevice '.$post);
		
		// 参数解析
		$postData = json_decode($post, true);
		if (empty($postData)) {
			return $this->response($this->_errorData(), 'json');
		}
		
		$mac = $postData['device_mac'];
		$mac = strtoupper($mac);
		// TODO 需要解析
		
		\Think\Log::write('Mac='.$mac);
		
		// 1. 检查MAC是否有效
		$deviceMacModel = D('Device/DeviceMac');
		$where = array('device_mac'=>$mac);
		$deviceMacData = $deviceMacModel->where($where)->find();
		if (empty($deviceMacData)) {
			return $this->response($this->_errorData('Your mac address is not permitted to access!'), 'json');
		}
		
		// 2. 检测产品是否存在
		$productData = D('Product/Product')->find($deviceMacData['product_id']);
		\Think\Log::write('$productData='.json_encode($productData));
		if (empty($productData)) {
			return $this->response($this->_errorData('Your mac address is not permitted to access!'), 'json');
		}
		
		// 3. 分配AK
		// TODO
		$device_key=C('DEFAULT_DEVICE_KEY');
		
		// 4. 分配接入资源
		$host = C('DEFAULT_INBOUND_HOST');
		$port = C('DEFAULT_INBOUND_PORT');
		
		$deviceModel = D('Device/Device');
		$deviceData = $deviceModel->where(array('device_mac'=>$mac))->find();
		if (!empty($deviceData)) {
			\Think\Log::write('device exist, update status!');
			// 5. 添加设备数据，如果设备存在，更改设备的状态至激活状态
			if ($deviceData['device_status']==1) {
				$deviceData['device_status']=2;  // 2:激活
				$deviceData['device_firmware_updatetime']=new \DateTime();
				$deviceData['active_time']=new \DateTime();
				
				$deviceModel->save($deviceData);	
			}
		} else {
			\Think\Log::write('device not exist, create new data!');
			$nowdata = new \DateTime();
			// 6. 如果设备记录存在，清空设备的拥有者，使用者信息
			$deviceData = array('product_id'=>$productData['id'],
					'device_sn'=>$mac,
					'device_mac'=>$mac,
					'device_name'=>$mac,
					'device_status'=>2,  // 2:激活
					'online_status'=>0,
					'device_firmware_updatetime'=>$nowdata
			);
			$deviceModel->add($deviceData);
		}
		
		// 返回结果
		$result = array('host'=>$host, 'port'=>$port, 'device_ak'=>$device_key);
		$this->response($this->_successData($result), 'json');
	}
	
	
	/**
	 * 设备注销
	 */
	public function cancellation() {
		$post = $GLOBALS['HTTP_RAW_POST_DATA'];
		\Think\Log::write('Enter cancellation '.$post);
		$postData = json_decode($post, true);
		if (empty($postData)) {
			return $this->response($this->_errorData(), 'json');
		}
		
		// 获取INPUT信息
		$deviceMac = $postData['device_mac'];
		$deviceMac = strtoupper($deviceMac);
		
		\Think\Log::record('Device exist check1 '. $deviceMac);
		$deviceModel = D('Device/Device');
		\Think\Log::record('Device exist check2');
		$deviceData = $deviceModel->where(array('device_mac'=>$deviceMac))->find();
		\Think\Log::record('Device exist check3');
		if (empty($deviceData)) {
			return $this->response($this->_errorData(), 'json');
		}
		
		// 修改设备的状态至注销状态
		\Think\Log::record('Device change status');
		$deviceData['device_status']='1';  // 1:初始化
		$deviceModel->save($deviceData);
		
		// 清空设备的拥有者，使用者信息， 及其相关信息
		\Think\Log::record('Device clean users');
		$deviceUserModel = D('Device/DeviceUser');
		$deviceUserList = $deviceUserModel->where(array('device_id'=>$deviceData['id']))->select();
		foreach ($deviceUserList as $tmpDeviceUser) {
			if ($tmpDeviceUser['relation_type']=2 || $tmpDeviceUser['relation_type']=3) {
				$deviceUserModel->delete($tmpDeviceUser['id']);
			}
		}
		
		\Think\Log::record('Device return success');
		return $this->response($this->_successData(), 'json');
	}
	
	
	/**
	 * 提供给接入层,每次设备连接时进行有效性认证
	 * device_mac
	 * access_key
	 */
	public function validateDevice() {
		// 参数解析
		$postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		if (empty($postData)) {
			return $this->response($this->_errorData(), 'json');
		}
		$deviceMac = $postData['device_mac'];
		$deviceMac = strtoupper($deviceMac);
		
		// 1. 检查MAC是否有效
		$deviceMacModel = D('Device/DeviceMac');
		$deviceMacData = $deviceMacModel->where(array('device_mac'=>$deviceMac))->find();
		if (empty($deviceMacData)) {
			return $this->response($this->_errorData('Your mac address is not permitted to access!'), 'json');
		}
		
		// 2. 分配AK
		// TODO
		$device_key=C('DEFAULT_DEVICE_KEY');
		
		// 3. 分配接入资源
		$host = C('DEFAULT_INBOUND_HOST');
		$port = C('DEFAULT_INBOUND_PORT');
		
		// 返回结果
		$result = array('host'=>$host, 'port'=>$port, 'device_key'=>$device_key);
		
		$this->response($this->_successData($result), 'json');
	}
	
	/**
	 * 提供给接入层数据上传得接口
	 * device_mac
	 * device_data
	 */
	public function uploadData() {
		\Think\Log::write('Enter uploadData');
	
		// 参数解析
		$postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		if (empty($postData)) {
			return $this->response($this->_errorData('post data json format err'), 'json');
		}
	
		// 数据解析
		$mac = $postData['device_mac'];
		$data = $postData['data'];
		$isJson = $postData['is_json'];
	
		\Think\Log::write('Enter uploadData mac='.$mac.' data='.$data);
		if (empty($isJson)) {
			$result = $this->deviceService->uploadDeviceData($mac, $data);
		} else {
			if (strtolower($isJson) == 'true') {
				if (is_array($data)){
					\Think\Log::write('data='.$data . ' is array.');
					$result = $this->deviceService->uploadJsonData($mac, $data);
				} else {
					// it's json format string
					\Think\Log::write('data='.$data . ' is json format string.');
					$data = json_decode($data,true);
					if (empty($data)) {
						return $this->response($this->_errorData('data string json format err'), 'json');
					} else {
						$result = $this->deviceService->uploadJsonData($mac, $data);
					}
				}
				
			} else {
				$result = $this->deviceService->uploadDeviceData($mac, $data);
			}
		}
	
		if (!$result) {
			$this->response($this->_errorData($result), 'json');
		}
	
		// 返回结果
		$this->response($this->_successData($result), 'json');
	}
	
	
	
	/**
	 * 当WIFI设备离线时，主动告知服务层
	 */
	public function notifyOffline() {
		\Think\Log::write('notifyOffline='.$GLOBALS['HTTP_RAW_POST_DATA']);
		$postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		if (empty($postData)) {
			return $this->response($this->_errorData('Error Json Format'), 'json');
		}
		
		$mac = $postData['device_mac'];
		
		$redis = getRedis();		
		$redis -> hset($mac, 'online_status', 'false');		
		releaseRedis($redis);
		$this->response($this->_successData(), 'json');
	}
	
	/**
	 * 设备端将OTA执行的过程状态通知到服务器
	 */
	public function notifyOTAStatus() {
		\Think\Log::write('notifyOTAStatus='.$GLOBALS['HTTP_RAW_POST_DATA']);
		$postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
		if (empty($postData)) {
			return $this->response($this->_errorData('Error Json Format'), 'json');
		}
		
		$mac = $postData['device_mac'];
		$otaStatus = $postData['ota_status'];
		
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_mac'=>$mac))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		
		$otaLogModel = D('Device/OtaLog');
		$condition['device_id'] = $device['id'];
		$condition['ota_status'] = array('NEQ', 4);
		
		$otaLogData = $otaLogModel->where($condition)->order('update_time desc')->find();  
		
		$otaLogModel->save($otaLogData);
	}
	
	private function _successData($data = null) {
		if (empty($data)) {
			return array('code'=>0);
		} else {
			return array('code'=>0, 'data'=>$data);
		}
	}
	
	private function _errorData($data = null) {
		if (empty($data)) {
			return array('code'=>-1);
		} else {
			return array('code'=>-1, 'data'=>$data);
		}
	}
	
}
