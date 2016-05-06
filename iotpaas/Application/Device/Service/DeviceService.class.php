<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace Device\Service;

/**
 * 设备相关的无状态服务类
 * @author Work
 *
 */
class DeviceService  {
	
	
	/**
	 * 设备注册服务
	 */
	public function registry($mac, $productCode, $uid){
		\Think\Log::write('Enter deviceservice#registry');
		
		$deviceModel = D('Device/Device');
		$deviceUserModel = D('Device/DeviceUser');
		
		//  要构成deviceModel,addDevice的入参的两个list.
		//这里只是临时设置下sn = mac，后面要改。
		$datalist_mac['device_sn'] = $mac;
		
		$datalist_mac['device_mac'] =  $mac;
		$productData = D('Product/Product')->where(array('product_code'=>$productCode))->find();
		$datalist_mac['product_id'] =  $productData['id'];
		
		
		$dataList_uid['uid'] = $uid;
		//添加了设备表
		\Think\Log::write('Do add device');
		$res_device_add = $deviceModel->addDevice($dataList_uid,$datalist_mac);
		
		//添加设备用户表
		\Think\Log::write('Do add device user');
		$res = $deviceUserModel->addDeviceUser($res_device_add, $dataList_uid, $datalist_mac);
		
		
		$redis = getRedis();
		
		try {
			$redis -> hset($mac, 'product_code', $productData['product_code']);
			$redis -> hset($mac, 'owner_code', $productData['owner_code']);
			$redis -> hset($mac, 'online_status', 'true');
		} finally {
			releaseRedis($redis);
		}
		
		return $res;	
	}
	
	/**
	 * 设备反注册服务
	 */
	public function unRegistry($mac, $productCode) {
		
		$deviceModel = D('Device/Device');
		
		//这里只是临时设置下sn = mac，后面要改。
		
		//将对应mac地址的设备，注册flg位置为0
		//TODO：deviceuser表的相应处理是/无效还是/删除还是/不管？		
		\Think\Log::write('device mac in unRegistry = '.$mac);
		
		$where['device_mac'] = $mac;
		
		$res=$deviceModel->where($where)->save(array('device_reg_flg'=>0));
		return $res;
	}
	
	/**
	 * 对设备进行授权
	 */
	public function authDevice($mac, $uid, $auth_type) {
		
		$deviceUserModel = D('Device/DeviceUser');
		
		\Think\Log::write('Enter DeviceService#authDevice ...');
		$data['person_id'] = $uid;
		\Think\Log::write('function authDevice person id = '.$data['person_id'] );
		$data['device_id'] =$mac;
		$data['auth_level'] = $auth_type;
		
		//TODO
		//$data['user_type'] = I('post.user_type','','op_t');
		//如果不存在时add
		$res = $deviceUserModel ->add($data);
		
	}
	
	/**
	 * 对设备取消授权
	 */
	public function unAuthDevice($mac, $uid) {

		$deviceUserModel = D('Device/DeviceUser');
		
		\Think\Log::write('Enter DeviceService#authDevice ...');
		$data['person_id'] = $uid;
		\Think\Log::write('function authDevice person id = '.$data['person_id'] );
		$data['device_id'] =$mac;
		$data['auth_level'] = $auth_type;
	
		$where['device_mac'] = $mac;
		$where['person_id'] = $uid;
		
		//此处假定code 0 为取消授权。
		$res=$deviceModel->where($where)->save(array('auth_level'=>0));
		return $res;
	}
	
	
	/**
	 * 接受上传数据服务
	 */
	public function uploadDeviceData($mac, $data) {
		
		if (strlen($data)>32) {
			$data = substr($data, 32); // remove device id from data
		}
		$dataCount = subStr($data, 0, 2); //
		$dataCount = (int)ltrim($dataCount, '0');
		$mac = strtoupper($mac);
		$data = strtoupper(substr($data, 2));
		\Think\Log::write('Enter uploadDeviceData $mac='.$mac.' $data='.$data);
		
		$redis = getRedis();
		// 校验数据并解析数据		
		// 从Redis中获取该设备相关的产品信息，如果没有添加（可能内存数据丢失，从数据库同步过去)
		$productCode = $redis->hget($mac, 'product_code');
		$productOwner = $redis->hget($mac, 'owner_code');
		
		if (empty($productCode) || empty($productOwner)) {
			// 该设备相关的初始数据不存在，则从数据库初始化之
			$deviceData = D('Device/Device')->where(array('device_mac'=>$mac))->find();
// 			\Think\Log::write('$deviceData = '.json_encode($deviceData));
			$productData = D('Product/Product')->find($deviceData['product_id']);
// 			\Think\Log::write('$productData = '.json_encode($productData));
			
			$productCode = $productData['product_code'];
			$productOwner = $productData['owner_code'];
			$redis -> hset($mac, 'product_code', $productCode);
			$redis -> hset($mac, 'owner_code', $productOwner);
			$redis -> hset($mac, 'online_status', 'true');
		} else {
			$redis -> hset($mac, 'online_status', 'true');
		}
		
		// 从Redis获取该产品的主数据，并按照主数据定义来解析
		\Think\Log::write('get all\'s name = '.$productOwner.':'.$productCode);
		$productData = $redis->hgetall($productOwner.':'.$productCode);
// 		\Think\Log::write('$productData ='.json_encode($productData));
		
		$mds = array_keys($productData);
		$matchMdCode ='';
		
		// 匹配元数据
		foreach ($mds as $tmp_md_code){
//			\Think\Log::write('md_code ='.$tmp_md_code);
// 			\Think\Log::write('mdAllData ='.$productData[$tmp_md_code]);
			// 对每一个md_code 定义规则，来匹配当前数据是否是该MD_CODE
			$mdAllData = json_decode($productData[$tmp_md_code], true);
			$mdInfo = $mdAllData['md_info'];
			$parseInfo = $mdAllData['parse_info'];
			$detailsInfo = $mdAllData['parse_details'];
			$enums = $mdAllData['enum_details'];
			$logconfig = $mdAllData['logconfig_info'];
			
			$md_type = $mdInfo['md_type'];
			$md_value_type = $mdInfo['md_value_type'];
			
			
			
			// 通过检查命令域来检查
		 	$matchRule = $this->_getDetailMatchInfo($detailsInfo);
// 		 	\Think\Log::write('$matchRule ='.json_encode($matchRule));
					 	
		 	// 整理命令长度校验
// 		 	if ($matchRule['length_solid']  &&  (strlen($data) != $matchRule['min_length']) ) { 
// 		 		continue;
// 		 	}
		 	if (strlen($data) < $matchRule['min_length']){
		 		\Think\Log::write('md_code ='.$tmp_md_code.': length not matched.');
		 		continue;
		 	}
		 	
		 	// 匹配帧头帧尾
		 	$data_head = substr($data, $matchRule['head_index'], $matchRule['head_length']);
		 	if ($data_head != $matchRule['head_pattern']) {
		 		\Think\Log::write('md_code ='.$tmp_md_code.': head['.$data_head.'] not matched with pattern['.$matchRule['head_pattern'].'].');
		 		continue;
		 	}
		 	if ($matchRule['length_solid']) {
		 		$data_tail = substr($data, $matchRule['tail_index'], $matchRule['tail_length']);
		 		if ($data_tail != $matchRule['tail_pattern']) {
		 			\Think\Log::write('md_code ='.$tmp_md_code.': tail['.$data_tail.'] not matched with pattern['.$matchRule['tail_pattern'].'].');
		 			continue;
		 		}	
		 	}
		 	
		 	// check command part
		 	$data_cmd = substr($data, $matchRule['cmd_index'], $matchRule['cmd_length']);
		 	if ($data_cmd == $matchRule['cmd_pattern']) {
		 		$matchMdCode = $tmp_md_code;
		 		break;
		 	} else {
		 		\Think\Log::write('md_code ='.$tmp_md_code.': cmd['.$data_cmd.'] not matched with pattern['.$matchRule['cmd_pattern'].'].');
		 	}
		}
		
		\Think\Log::write('matchMdCode ='.$matchMdCode);
		if (empty($matchMdCode)){
			// 没有匹配到合适的元数据
			return false;
		}
		
		// 提取业务值
		$valueLength = $matchRule['value_length']; 
		\Think\Log::write('value_index='.$matchRule['value_index'].', $valueLength='.$valueLength);

		if (!$matchRule['length_solid']){
			$lengthValue = substr($data, $matchRule['length_index'], $matchRule['length_length']);
			$valueLength = (int)trim($valueStr);
		}
		$valueStr = substr($data, $matchRule['value_index'], $valueLength);
		\Think\Log::write('$valueStr ='.$valueStr);
		$value = $valueStr;
		$valueFunction = urldecode($parseInfo['parser_attr_1']);
		
		
		switch ($md_value_type) {
			case '0' : // N/A
				break;
			case '1' : // 数值型
				$valueStr = ltrim($valueStr, '0');				
				$value = hexdec($valueStr);
				\Think\Log::write('dec $value ='.$value);
				if (!empty($valueFunction)) {
					$view = new \Think\View();
					$view->assign('value', $value);
					$value = $view->fetch('', $valueFunction);
					\Think\Log::write('convert $value ='.$value);
				}
				
				break;
			case '2': //  字符型
				$value = trim($valueStr);
				
				if (!empty($valueFunction)) {
					$view = new \Think\View();
					$view->assign($value);
					$value = $view->fetch('', $valueFunction);
				}
				break;
			case '3': //  枚举型
				$value = trim($valueStr);
				foreach ($enums as $enumItem) {
					if ($enumItem['enum_key']==$value) {
						$value = $enumItem['enum_value'];
					}
				}
				break;
			default:
				break;
		}

		
		// 将数据存储到Redis
		$oldValue = $redis->hget($mac, $matchMdCode);
		\Think\Log::write('Set value 2 redis : Mac = '.$mac.' Value = '.$value);
		$redis->hset($mac, $matchMdCode, $value);
		
		// 判断产品的日志配置，如果需要，将数据存到历史库
		$dataNeedLog = false;
		if (empty($logconfig)) {
			$dataNeedLog = true; // 默认保存
			\Think\Log::write('No log config for mdcode['.$matchMdCode.'], use default True');
		} else {
			$logRequired = $logconfig['log_required'];
			$logConditionType = $logconfig['log_condition_type'];
			$logConditionValue = $logconfig['log_condition_value'];
			$logFormat = $logconfig['log_format'];
			if (!$logRequired) {
				\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], no need log');
				$dataNeedLog = false;
			} else {
				if ($logConditionType == 1) { 
					// 1:直接记录
					\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], direct record log');
					$dataNeedLog = true;
				} else if ($logConditionType == 2) {
					// 2:变化超过阀值记录
					if ($logConditionValue >= abs($value-$oldValue)) {
						\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], log when value changed out of threshold');
						$dataNeedLog = true;
					} else {
						\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], no log when value changed inside threshold');
						$dataNeedLog = false;
					}
				}
			}
		}
		
		if ($dataNeedLog) {		
			$logData = array();
			$deviceData = D('Device/Device')->where(array('device_mac'=>$mac))->find();
			$logData['device_id'] = $deviceData['id'];
			$logData['md_code'] = $matchMdCode;
			$logData['md_type'] = $md_type;
			$logData['log_value'] = $value;
			$logData['log_display_txt'] = $value; //TODO
			$logData['user_id'] = 1;
			\Think\Log::write('Log data = '.json_encode($logData));
			
			$deviceLogModel = D('Device/DeviceLog');
			$logData = $deviceLogModel->create($logData);
			if (!$logData) {
				\Think\Log::write('Insert to logdata error:'.$deviceLogModel->getError(), \Think\Log::WARN);
			} else {
				$deviceLogModel->add($logData);
			}
		}
		
		return array('md_code'=>$matchMdCode, 'value'=>$value);
	}
	
	/**
	 * 接受上传数据服务
	 */
	public function uploadJsonData($mac, $data) {
	    $mac = strtoupper($mac);
		\Think\Log::write('Enter uploadJsonData $mac='.$mac.' $data='.json_encode($data));
	
		$redis = getRedis();
		// 校验数据并解析数据
		// 从Redis中获取该设备相关的产品信息，如果没有添加（可能内存数据丢失，从数据库同步过去)
		$productCode = $redis->hget($mac, 'product_code');
		$productOwner = $redis->hget($mac, 'owner_code');
		\Think\Log::write('redis get = '.$productOwner.':'.$productCode);
	
		if (empty($productCode) || empty($productOwner)) {
			// 该设备相关的初始数据不存在，则从数据库初始化之
			$deviceData = D('Device/Device')->where(array('device_mac'=>$mac))->find();
			$productData = D('Product/Product')->find($deviceData['product_id']);
				
			$productCode = $productData['product_code'];
			$productOwner = $productData['owner_code'];
			$redis -> hset($mac, 'product_code', $productCode);
			$redis -> hset($mac, 'owner_code', $productOwner);
			$redis -> hset($mac, 'online_status', 'true');
		} else {
			$redis -> hset($mac, 'online_status', 'true');
		}
	
		// 从Redis获取该产品的主数据，并按照主数据定义来解析
		\Think\Log::write('get all\'s name = '.$productOwner.':'.$productCode);
		$productData = $redis->hgetall($productOwner.':'.$productCode);
		// 		\Think\Log::write('$productData ='.json_encode($productData));
		
	
		$mds = array_keys($productData);
	
		// 匹配元数据
		foreach ($mds as $tmp_md_code){
			//			\Think\Log::write('md_code ='.$tmp_md_code);
			// 			\Think\Log::write('mdAllData ='.$productData[$tmp_md_code]);
			// 对每一个md_code 定义规则，来匹配当前数据是否是该MD_CODE
			$mdAllData = json_decode($productData[$tmp_md_code], true);
			$mdInfo = $mdAllData['md_info'];
			$parseInfo = $mdAllData['parse_info'];
			$detailsInfo = $mdAllData['parse_details'];
			$enums = $mdAllData['enum_details'];
			$logconfig = $mdAllData['logconfig_info'];				
			$md_type = $mdInfo['md_type'];
			$md_value_type = $mdInfo['md_value_type'];
			
			\Think\Log::write('parse value of '.$tmp_md_code);
			
			if ($md_value_type == '0') { // N/A
				continue;
			}
						
			$jsonFilter = urldecode($parseInfo['parser_attr_1']);
			$filters = str2arr($jsonFilter,'#');
			
			\Think\Log::write('$jsonFilter = ' .$jsonFilter. '  $filters ='.json_encode($filters));
			
			$tempData = $data;
			$findValue = false;
			foreach ($filters as $filter) {
				if (empty($filter)) {
					continue;
				}
				\Think\Log::write('$filter = '.$filter.' data = '.$tempData );
				$tempData = $tempData[$filter];
				if (empty($tempData)) {
					break;
				} else {
					$findValue = true;
				}
			}
			$valueStr = $tempData;			
			\Think\Log::write('parse value of '.$tmp_md_code. ' value ='.$valueStr);
			if (empty($valueStr) || !$findValue) { 
				// 找到的值为空，或者没有进行过一级任何的匹配
				continue;
			} else {
				$matchMdCode = $tmp_md_code;
			}
			
			switch ($md_value_type) {
				case '0' : // N/A
					break;
				case '1' : // 数值型
					$valueStr = ltrim($valueStr, '0');
					$value = $valueStr;
			
					break;
				case '2': //  字符型
					$value = trim($valueStr);
					break;
				case '3': //  枚举型
					$value = trim($valueStr);
					foreach ($enums as $enumItem) {
						if ($enumItem['enum_key']==$value) {
							$value = $enumItem['enum_value'];
						}
					}
					break;
				default:
					break;
			}
			
			
			// 将数据存储到Redis
			$oldValue = $redis->hget($mac, $matchMdCode);
			\Think\Log::write('Set value 2 redis : Mac = '.$mac.' Value = '.$value);
			$redis->hset($mac, $matchMdCode, $value);
			
			
			// 判断是否触发Engine
			$this->triggerEngine($mac, $matchMdCode, $value);
			
			// 判断产品的日志配置，如果需要，将数据存到历史库
			$dataNeedLog = false;
			if (empty($logconfig)) {
				$dataNeedLog = true; // 默认保存
				\Think\Log::write('No log config for mdcode['.$matchMdCode.'], use default True');
			} else {
				$logRequired = $logconfig['log_required'];
				$logConditionType = $logconfig['log_condition_type'];
				$logConditionValue = $logconfig['log_condition_value'];
				$logFormat = $logconfig['log_format'];
				if (!$logRequired) {
					\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], no need log');
					$dataNeedLog = false;
				} else {
					if ($logConditionType == 1) {
						// 1:直接记录
						\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], direct record log');
						$dataNeedLog = true;
					} else if ($logConditionType == 2) {
						// 2:变化超过阀值记录
						if ($logConditionValue >= abs($value-$oldValue)) {
							\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], log when value changed out of threshold');
							$dataNeedLog = true;
						} else {
							\Think\Log::write('Log config defined for mdcode['.$matchMdCode.'], no log when value changed inside threshold');
							$dataNeedLog = false;
						}
					}
				}
			}
			
			if ($dataNeedLog) {
				$logData = array();
				$deviceData = D('Device/Device')->where(array('device_mac'=>$mac))->find();
				$logData['device_id'] = $deviceData['id'];
				$logData['md_code'] = $matchMdCode;
				$logData['md_type'] = $md_type;
				$logData['log_value'] = $value;
				$logData['log_display_txt'] = $value; //TODO
				$logData['user_id'] = 1;
				\Think\Log::write('Log data = '.json_encode($logData));
			
				$deviceLogModel = D('Device/DeviceLog');
				$logData = $deviceLogModel->create($logData);
				if (!$logData) {
					\Think\Log::write('Insert to logdata error:'.$deviceLogModel->getError(), \Think\Log::WARN);
				} else {
					$deviceLogModel->add($logData);
				}
			}
			
		}
	
		return array('md_code'=>$matchMdCode, 'value'=>$value);
	}
	
	/**
	 * 执行设备指令(异步执行）
	 */
	public function asynCommand($mac, $uid=0, $mdCode, $mdValue='') {
		\Think\Log::write('Enter asynCommand $mdCode='.$mdCode);
		$redis = getRedis();
		// 校验数据并解析Value
		// 从Redis中获取该设备相关的产品信息，如果没有添加（可能内存数据丢失，从数据库同步过去)
		$productCode = $redis->hget($mac, 'product_code');
		$productOwner = $redis->hget($mac, 'owner_code');
		
		if (empty($productCode) || empty($productOwner)) {
			// 该设备相关的初始数据不存在，则从数据库初始化之
			$deviceData = D('Device/Device')->where(array('device_mac'=>$mac))->find();
			$productData = D('Product/Product')->find($deviceData['product_id']);
			
			$productCode = $productData['product_code'];
			$productOwner = $productData['owner_code'];
			$redis -> hset($mac, 'product_code', $productCode);
			$redis -> hset($mac, 'owner_code', $productOwner);
			$redis -> hset($mac, 'online_status', 'false');
		}
		
		// 从Redis获取该产品的主数据，并按照主数据定义来解析
		$key = $productOwner.':'.$productCode;
		\Think\Log::write('$key='.$key.' $md_code='.$mdCode);
		$mdData = $redis->hget($productOwner.':'.$productCode, $mdCode);
		\Think\Log::write('$mdData 1='.json_encode($mdData));
		
		
		// 调用接入服务，将命令数据下发到设备
		$postData = array();
		$postData['device_mac'] = $mac;
		$postData['qos'] = '1';
		
		$parseInfo = $mdData['parse_info'];
		if ($parseInfo['parser_type']== '1') { // Hex format
			$mdRules = $mdData['parse_details'];
			$mdInfo =  $mdData['md_info'];
			if ($mdInfo['md_value_type'] == '1') {
				// 数值型
				$mdValue = dechex($mdValue);
				$mdValue = strtoupper($mdValue);
			}
			
			\Think\Log::write('mdrules 1='.json_encode($mdRules));
			
			$contentLenth = 0;
			$checkSumNeeded = false;
			$cmdValue = '';
			$contentValue = '';
			
			foreach ($mdRules as &$mdRule) {
				$partType = $mdRule['part_type'];
				$partLength = $mdRule['part_length'];
				$partValue = $mdRule['part_value'];
					
				if (empty($partValue)) {
					$partValue = '';
				} else {
					$partValue = substr($partValue, 0, (int)$partLength);
				}
					
				switch ($partType) {
					case 'command':
						$cmdValue = $partValue;
						break;
					case 'length':
						$contentLenth = (int)$partValue;
						$partValue = '';
						break;
					case 'content':
						if ($partLength == 0) {
							// 代表该内容域为变长
							$checkSumNeeded = true;
						} else {
							$contentLenth = $partLength;
						}
						if (empty($mdValue)) {
							// do nothing here
						} else {
							if (strlen($mdValue) > (int)$contentLenth) {
								$partValue = substr($mdValue, 0, (int)$contentLenth);
							} else {
								$partValue = sprintf('%0'.$contentLenth.'s', $mdValue);;
							}
						}
							
						$contentValue = $partValue;
						break;
					case 'chksum':
						if ($partValue == 'XX') {
							\Think\Log::write('check sum is XX need calculated.');
							$checkSumNeeded = true;
						}
						break;
					default: break;
				}
					
				$mdRule['part_value'] = $partValue;
			}
			
			// 		\Think\Log::write('mdrules 2='.json_encode($mdRules));
			
			if ($checkSumNeeded) {
				// Flyco check sum
				$checkSum = hexdec($cmdValue) + hexdec(substr($contentValue, 0, 2)) + hexdec(substr($contentValue, 2, 2));
				$checkSum = strtoupper(dechex($checkSum));
				$checkSum = sprintf('%02s', $checkSum);
				\Think\Log::write('check sum  calculated out = '.$checkSum);
			}
			
			// 拼接处命令域
			$cmd = sprintf('%-032s', $mac); // 将MAC地址拼接
			$cmdCount = 1;
			
			$cmd = $cmd.sprintf('%02s', $cmdCount);
			
			foreach ($mdRules as $rule) {
				\Think\Log::write('type='.$rule['part_type'].'  value='.$rule['part_value']);
				$ruleType = $rule['part_type'];
				if ($ruleType === 'chksum' && $checkSumNeeded) {
					$cmd = $cmd.$checkSum;
				} else {
					$cmd = $cmd.$rule['part_value'];
				}
			}
			$cmdIndex = 1;
			$cmd = $cmd.sprintf('%02s', $cmdIndex);
			$postData['data'] = $cmd;
			$url = C('INBOUND_URL').'/'.$mac.'/'.$uid;
			 
		} else if ($parseInfo['parser_type'] == '2') { 
			// Json format
			\Think\Log::write('parse by json format, json value='.$mdValue);
			if (empty($mdValue)) {
				\Think\Log::write('original value $parseInfo[parser_attr_1] value='.$parseInfo['parser_attr_1']);
				$parserAttr1Str = urldecode($parseInfo['parser_attr_1']);
				$mdValue = json_decode($parserAttr1Str);
				if ($mdValue) {
					$postData['data'] = $mdValue;
				} else {
					$postData['data'] = $parserAttr1Str;
				}
				
				\Think\Log::write('url decode value='.$postData['data']);
			} else { 
				$postData['data'] = $mdValue;
			}
			$url = C('INBOUND_URL_JSON').'/'.$mac.'/'.$uid;
		}
		
		// 调用接入服务，将命令数据下发到设备
		$postData['device_mac'] = $mac;
// 		$postData['data'] = $cmd;
		$postData['qos'] = '1';
		
		\Think\Log::write('postData='.json_encode($postData));
		$result = http_post_data($url, json_encode($postData));
		
		// 判断产品的日志配置，如果需要，将操作存到历史库		
		return array('url'=>$url,'post'=>$postData,'result'=>$result);
	}
	
	/**
	 * 执行设备指令(同步执行）
	 */
	public function synCommand($mac, $uid=0, $mdCode, $mdValue='') {
		$redis = getRedis();
		// 校验数据并解析Value
		// 从Redis中获取该设备相关的产品信息，如果没有添加（可能内存数据丢失，从数据库同步过去)
		$productCode = $redis->hget($mac, 'product_code');
		$productOwner = $redis->hget($mac, 'product_owner');
		
		if (empty($productCode) || empty($productOwner)) {
			// 该设备相关的初始数据不存在，则从数据库初始化之
			// TODO
		}
		
		// 从Redis获取该产品的主数据，并按照主数据定义来解析
		$mdData = $redis->hget($productOwner.':'.$productCode, $mdCode);
		$cmd = "";
		
		// 调用接入服务，将命令数据下发到设备
		$postData = array();
		$postData['device_mac'] = $mac;
		$postData['data'] = $cmd;
		$postData['qos'] = '2';
		$url = C('INBOUND_URL').'/'.$mac.'/'.$uid;
		
		$result = http_post_data($url, $postData);
		
		// 判断产品的日志配置，如果需要，将操作存到历史库
		
		return $result;
	}

	/**
	 * 请求接入服务提供的Service，通过其与指定设备进行通信
	 * @param unknown $deviceMac
	 * @param unknown $content
	 * @param string $qos
	 */
	public function excuteCmd2Device($deviceMac, $uid, $content, $qos='0') {
		// 构建访问接入服务提供的URL
		$url = C('INBOUND_URL_JSON').'/'.$deviceMac.'/'.$uid;
		
		$postData = array();
		$postData['device_mac'] = $deviceMac;
		$postData['data'] = $content;
		$postData['qos'] = $qos;
		
		\Think\Log::write('url='.$url.' postData='.json_encode($postData));
		$result = http_post_data($url, json_encode($postData));
		
		return $result;
	}
	
	private function _getDetailMatchInfo($parsedetails) {
		$cmdValue = '';
		$cmdIndex = 0;
		$cmdLength = 0;
		$allLength =0;
		$lengthSolid = true;
		$valueIndex = 0;
		$valueLength = 0;
		$lengthIndex = 0;
		$lengthLength = 0;
		$headIndex = 0;
		$headLength = 0;
		$headPattern = '';
		$tailIndex = 0;
		$tailLength = 0;
		$tailPattern = '';
		
		foreach ($parsedetails as $detail) {
			$pType = $detail['part_type'];
			$pLength = $detail['part_length'];
			$pValue = $detail['part_value'];
			
			switch ($pType) {
				case 'head':
					$headIndex = $allLength;
					$headLength = $pLength;
					$headPattern = $pValue;
					$allLength = $allLength + (int)$pLength;
					break;
				case 'command':
					$cmdIndex = $allLength;
					$cmdLength = (int)$pLength;
					$allLength = $allLength + $cmdLength;
					$cmdValue = $pValue;					
					break;
				case 'content':
					$valueIndex = $allLength;
					$valueLength = (int)$pLength;					
					$allLength = $allLength + $valueLength;
					break;
				case 'length':
					$lengthIndex = $allLength;
					$lengthLength = (int)$pLength;
					$allLength = $allLength + $lengthLength;					
					break;
				case 'tail':
					$tailIndex = $allLength;
					$tailLength = $pLength;
					$tailPattern = $pValue;
					$allLength = $allLength + (int)$pLength;
					break;
				case 'chksum':
					$allLength = $allLength + (int)$pLength;
					break;
			}
		}
		
		if ($valueLength == 0 ) {
			$lengthSolid = false;
		}
			
		if ($lengthIndex > 0) {
			$lengthSolid = false;
		}
		
		return array('length_solid'=>$lengthSolid, 'min_length'=> $allLength,
				'cmd_index'=>$cmdIndex, 'cmd_pattern'=>$cmdValue, 'cmd_length'=>$cmdLength,
				'value_index'=>$valueIndex, 'value_length'=>$valueLength,
				'length_index'=>$lengthIndex, 'length_length'=>$lengthLength,
				'head_index'=>$headIndex,'head_pattern'=>$headPattern, 'head_length'=>$headLength,
				'tail_index'=>$tailIndex,'tail_pattern'=>$tailPattern, 'tail_length'=>$tailLength,
		);
	}
	
	
	public function triggerEngine($deviceMac, $mdCode, $mdValue) {
		\Think\Log::write('enter triggerEngine $deviceMac='.$deviceMac.' $mdCode='.$mdCode.' $mdValue='.$mdValue);
		$engineConditionModel = D('Device/EngineCondition');
		$deviceConditions = $engineConditionModel->where(array('device_mac'=>$deviceMac))->select();
		
		if (empty($deviceConditions)) {
			return;
		}
		
		$matched = false;
		$matchedEngineId = 0;
		foreach ($deviceConditions as $condition) {
			\Think\Log::write('match condition md_code='. $condition['md_code'].'eigen_value'. $condition['eigen_value']);
			if ($condition['md_code'] == $mdCode) {
				if (!empty($condition['eigen_value'])) {
					if ($condition['eigen_value'] == $mdValue){
						$matched = true;
						\Think\Log::write('matched by mdcode and eigenvalue');
						$matchedEngineId = $condition['engine_id'];
						break;
					}
				} else {
					\Think\Log::write('matched by md code only');
					$matched = true;
					$matchedEngineId = $condition['engine_id'];
					break;
				}
			}
		}
		
		// 执行相关操作
		if ($matched) {
			// 获取该场景对应的动作
			\Think\Log::write('get situaiton actions');
			$engineActionModel = D('Device/EngineAction');
			$actions = $engineActionModel->where(array('engine_id'=>$matchedEngineId))->order('sort asc')->select();
			
			// 依次执行动作
			\Think\Log::write('excute actions');
			foreach ($actions as $action) {
				$deviceMac = $action['device_mac'];
				$mdCode = $action['md_code'];
				$mdValue = $action['eigen_value'];
				if (substr($mdValue, 0, 5) == 'class') {
					$generatorStr = substr($mdValue, 6);
					$generator = new $generatorStr();
// 					$generator = new LightColorGenerator();
					\Think\Log::write('action generator = '.$generatorStr);
					$mdValue = $generator->generate();
				} else if (json_decode($mdValue, true)) {
					$mdValue = json_decode($mdValue, true);
				}
				$this->asynCommand($deviceMac, 0, $mdCode, $mdValue);
			}
		}
		
	}
	
}