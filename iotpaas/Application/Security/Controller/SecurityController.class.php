<?php


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;



class AppmgrController extends AdminController
{
    public function _initialize()
    {
    	parent::_initialize();
    }

    public function index($page=1,$r=10){
    	//读取列表
		$list = array();
    	$totalCount = 0;
	    
    	//显示页面
    	$builder = new AdminListBuilder();
    	$attr['class'] = 'btn ajax-post';
    	$attr['target-form'] = 'ids';
    
    
    	$builder->title('XXXX列表') ->buttonNew() 
    	->keyId()
    	->data($list)
    	->pagination($totalCount, $r)
    	->display();
    }
    

   
}