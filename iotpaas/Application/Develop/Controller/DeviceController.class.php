<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Device\Service\DeviceService;



class DeviceController extends AdminController
{
	protected $deviceModel;
	protected $deviceMacModel;
	protected $productModel;
	protected $deviceUserModel;
	protected $deviceLogModel;
	
    /**
     * 业务逻辑都放在 WeiboApi 中
     * @var
     */
    public function _initialize()
    {
    	$this->deviceModel = D('Device/Device');
    	$this->deviceUserModel = D('Device/DeviceUser');
    	$this->deviceMacModel = D('Device/DeviceMac');
    	$this->productModel = D('Product/Product');
    	$this->deviceLogModel = D('Device/DeviceLog');
    	 
    	parent::_initialize();
    	trace("初始化");
    }

    
    
    public function index($page=1,$r=10){
    	
    }
    
   
}