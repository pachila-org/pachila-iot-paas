<?php


namespace Develop\Controller;




class AjaxController
{
	protected $deviceModel;
	protected $deviceMacModel;
	protected $productModel;
	protected $deviceUserModel;
	protected $deviceLogModel;
	
    /**
     */
    public function _initialize()
    {
    	$this->deviceModel = D('Device/Device');
    	$this->deviceUserModel = D('Device/DeviceUser');
    	$this->deviceMacModel = D('Device/DeviceMac');
    	$this->productModel = D('Product/Product');
    	 
    	parent::_initialize();
    	trace("初始化");
    }

   
    public function index($page=1,$r=10){
    	
    	// TODO
    	
    }
      
}