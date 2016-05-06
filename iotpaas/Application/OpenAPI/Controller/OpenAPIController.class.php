<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Admin\Builder\AdminListBuilder;

class OpenAPIController extends AdminController {
	
	
	public function apiList($page=1,$r=10) {
		$data = C('URL_ROUTE_RULES');
		trace($data);
		
		$builder = new AdminListBuilder();
		$builder->title('所有开放接口列表');
		$builder->keyId();
		
		$builder->data($data);
		$builder->display();		
	}
	
	public function apiEdit($id) {
		
	}
	
}
