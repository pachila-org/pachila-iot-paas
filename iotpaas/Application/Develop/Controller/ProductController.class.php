<?php


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;



class ProductController extends AdminController
{
	
	protected $categoryModel;
	protected $productModel;
	protected $metadataModel;
	protected $productMetadataModel;
	protected $connectModuleModel;
	protected $moduleFirmwareModel;
	protected $digitalParseRuleModel;
	protected $mdEnumModel;
	protected $logConfigModel;
	
    public function _initialize()
    {
    	$this->categoryModel = D('Product/Category');
    	$this->productModel = D('Product/Product');
    	$this->metadataModel = D('Product/Metadata');
    	$this->productMetadataModel = D('Product/ProductMetadata');
    	$this->connectModuleModel = D('Product/ConnectModule');
    	$this->moduleFirmwareModel = D('Product/ModuleFirmware');
    	$this->digitalParseRuleModel = D('Proudct/DigitalParseRule');
    	$this->mdEnumModel = D('Proudct/ProductEnum');
    	$this->logConfigModel = D('Product/LogConfig');
    	
    	parent::_initialize();
    }
    
}