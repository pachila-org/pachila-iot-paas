<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace OpenAPI\Controller;

use Device\Service\DeviceService;
/**
 * 用户相关的开放接口
 */

class SengineController extends JsonAPIController {
	
	/**
	 * 用户名登录
	 * 方法：POST
	 * POST参数: username
	 * POST参数: password 
	 */
	public function listAllSitEngines(){
		\Think\Log::record('enter listAllSitEngines');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		$smartEngineModel = D('Device/SmartEngine');
		
		// 检验当前用户是否某产品的创造者，如果是创造者，则返回所有该产品的设备
		
		// 		$productModel->find();
		
		// 根据用户获取其有权限访问的Device列表
		$condition['owner_uid'] = $this->currentUid;
		$condition['engine_type'] = 1; // 1:情景模式
		$engineList = $smartEngineModel->where($condition)->order('create_time asc')->select();
		\Think\Log::record('listAllSitEngines sql '.$smartEngineModel->getLastSql());
		return $this->returnSuccess($engineList);
		
	}
	
	public function excuteSitEngine() {
		\Think\Log::record('enter excuteSitEngine');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		// 获取INPUT信息
		$postData = $this->getJsonPostContent();
		$sitId = $postData['situation_id'];
		$uid =  $this->currentUid;
		$deviceService = new DeviceService();
		
		// 获取该场景
		\Think\Log::record('get situaiton');
		$smartEngineModel = D('Device/SmartEngine');
		$situation = $smartEngineModel->find($sitId);
		if (empty($situation)) {
			return $this->returnErrorNotExist($sitId);
		}
		
		// 获取该场景对应的动作
		\Think\Log::record('get situaiton actions');
		$engineActionModel = D('Device/EngineAction');
		$actions = $engineActionModel->where(array('engine_id'=>$sitId))->order('sort asc')->select();
		
		// 依次执行动作
		\Think\Log::record('excute actions');
		foreach ($actions as $action) {
			$deviceMac = $action['device_mac'];
			$mdCode = $action['md_code'];
			$mdValue = $action['eigen_value'];
			if (json_decode($mdValue)) {
				$mdValue = json_decode($mdValue);
			}
			$deviceService->asynCommand($deviceMac, $this->currentUid, $mdCode, $mdValue);
		}
				
		return $this->returnSuccess();
	}
	
	
	public function listTriggers($deviceSn) {
		\Think\Log::record('enter listTriggers');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
		
		\Think\Log::record('check device exist or not');
		$deviceModel = D('Device/Device');
		$device = $deviceModel->where(array('device_sn'=>$deviceSn))->find();
		if (empty($device)) {
			return $this->returnErrorNotExist($deviceSn);
		}
		
		$smartEngineModel = D('Device/SmartEngine');
		$engineActionModel = D('Device/EngineAction');
		
		\Think\Log::record('list trigger by device ');
		$engineList = $smartEngineModel -> listEngineByCondition($device['device_mac'], 2);
		
		foreach ($engineList as &$engine) {
			$actions = $smartEngineModel -> listEngineActions($engine['id']);
			foreach ($actions as &$action) {
				$deviceSn = $action['device_sn'];
				$action['product']=$deviceModel->getPrdocutInfo($deviceSn);
			}
			$engine['actions'] = $actions;
		}
		
		return $this->returnSuccess($engineList);
	}
	

}

