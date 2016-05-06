<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */

return array(
		
		
// 		'DATA_CACHE_TYPE'       =>  'Redis',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
	
		'URL_MODEL' => 0, //PATHINFO模式
		
		'ENCRIPT_VERIFY_NEED' =>false,	// API 接口访问 是否适用安全加密认证

		'URL_ROUTER_ON' => true,
    
		'URL_ROUTE_RULES' => array(
			'users/login' =>array('Users/login', '', array('method'=>'POST')),
			'users/loginby3rd' =>array('Users/login3rd', '', array('method'=>'POST')),
			'users/profile/:uid' =>array('Users/profile', '',  array('method'=>'GET')),
			'users/nickname' =>array('Users/updateNickname', '', array('method'=>'POST')),
// 			'users/sendcode' =>array('Users/sendVerifySMS', '', array('method'=>'POST')),
// 			'users/checkcode' =>array('Users/checkVerifySMS', '',  array('method'=>'GET')),
			'users/reg' =>array('Users/registry','',  array('method'=>'POST')),
			'users/logout' =>array('Users/logout', '',  array('method'=>'POST')),
				
			'system/appversion/:mobileType/:businessType'=>array('System/appCurrentVersion','', array('method'=>'GET')),
			'message/list'=>array('Message/listMessage','', array('method'=>'GET')),
			'message/get/:messageId'=>array('Message/getMessage','', array('method'=>'GET')),

			'devices/bindowner'=>array('Devices/bindDeviceOwner', '', array('method'=>'POST')),
			'devices/list'=>array('Devices/listDevices','',  array('method'=>'GET')),
			'devices/name'=>array('Devices/updateDeviceName','',  array('method'=>'POST')),
			'devices/unbindowner'=>array('Devices/unbindDeviceOwner', '', array('method'=>'POST')),
			'deviceusers/:deviceSn'=>array('Devices/getDeviceUsers','',  array('method'=>'GET')),
			'deviceusers/apply'=>array('Devices/applyDeviceUser', '', array('method'=>'POST')),
			'deviceusers/auth'=>array('Devices/authDeviceUser', '', array('method'=>'POST')),
			'deviceusers/unauth'=>array('Devices/unAuthDeviceUser', '', array('method'=>'POST')),
			'devices/registry'=>array('Devices/registryDevice','',  array('method'=>'POST')),
			'devices/:deviceSn/status'=>array('Devices/getDeviceStatus','',  array('method'=>'GET')),			
			'devices/asyncommand'=>array('Devices/asynCommand','',  array('method'=>'POST')),
			'devices/syncommand'=>array('Devices/synCommand','',  array('method'=>'POST')),
			'devices/cancellation'=>array('Devices/cancellation','',  array('method'=>'POST')),
			'devices/requestota'=>array('Devices/requestOTA','',  array('method'=>'POST')),
			'devices/:deviceSn/otastatus'=>array('Devices/getOTAStatus','',  array('method'=>'GET')),
			'barcode/check'=>array('Devices/barcodeCheck','',  array('method'=>'POST')),
			'devices/:deviceSn'=>array('Devices/getDevice','',  array('method'=>'GET')),
			'products/:productId'=>array('Devices/getProductDetail','',  array('method'=>'GET')),
				
			'engins/situations/list'=>array('Sengine/listAllSitEngines','',  array('method'=>'GET')),
			'engins/situations/excute'=>array('Sengine/excuteSitEngine','',  array('method'=>'POST')),
			'engins/triggers/list/:deviceSn'=>array('Sengine/listTriggers','',  array('method'=>'GET')),
			
			'testget/:id/:password' => array('System/test', '', array('method'=>'GET')),
			'testpost/:id' => array('System/test', '', array('method'=>'POST')),
		),
);
