<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace OpenAPI\Controller;

use Appmgr\Model\AppModel;
class SystemController extends JsonAPIController {
	
	public function test_get() {
		\Think\Log::write('testAPI called');
		
		$data = array('get_key'=>'This is test');
		$id = I('get.id');
		$password = I('get.password');
// 		$id = $_GET['id'];
// 		$password = $_GET['password'];
		
		if ($id) {
			$data['id'] = $id;
			$data['password'] = $password;
		} else {
			$data = $data + array('id'=>'No param input');
		}
		
		return $this->returnSuccess($data);
	}
	
	public function test_post($id = 0) {
// 		$data = array('post_key'=>'post_value');
		$data = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
		return $this->returnSuccess($data);		
	}
	
	/**
	 * 获取指定类型的APP最新版本
	 * @param unknown $mobileType 手机类型:Android,Ios
	 * @param unknown $businessType 业务类型:SmartAppliance,ChargingPiles
	 */
	public function appCurrentVersion_get($mobileType, $businessType) {

		// 返回最新的版本号以及下载地址
		$mobileType = I('get.mobileType');	//TODO	Android,Ios
		$businessType = I('get.businessType');//TODO	SmartAppliance,ChargingPiles
		
		//Retrieve Model		
		$prefix = $this->tablePrefix;
		$tbl_app = "iot_app";		//TODO $tbl_app = $prefix . AppModel::TBL_NAME;
		$tbl_file = "iot_file";   //TODO		$tbl_file = $prefix . FileModel;
		
		$appdata = M()-> field(' app.app_type,app.business_type,app.app_name,app.app_version,file.savepath,file.savename ')
		->table($tbl_app . ' app')
		->join($tbl_file . ' file on app.file_name=file.id')
		->where(array('app.status' => 1, 'app.app_type'=>$mobileType, 'app.business_type'=>$businessType))->order(' app.id desc')->find();
		 
		return $this->returnSuccess($appdata);
		 
	}
	// 		$condition = array('status' => 1);
	// 		$condition['app_type'] = $mobileType;
	// 		$condition['buisiness_type'] = $buisinessType;
	
	// 		$this->appModel = D('Appmgr/App');
	// 		$appdata = $this->appModel -> where($condition) -> field('app_version,file_name')->order('id desc')->find();
	// 		return $this->returnSuccess($appdata);
	
	//    	return array_column($data, 'metadata_id');
	//		businesstype <>businessType
	// 		return $this->returnSuccess(array('prefix' =>$prefix, 'tbl_app'=>$tbl_app, '$tbl_file' =>$tbl_file,'app.status' => 1, 'app.app_type'=>$mobileType, 'app.business_type'=>$businessType,'sql' => $appdata));
	
	
}

