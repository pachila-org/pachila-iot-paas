<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace Device\Model;
use Think\Model;
use Product\Model\ProductModel;

/**
 * Class DeviceModel
 * @package Device\Model
 *
 */
class DeviceModel extends Model {
	const TBL_NAME = 'device';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('device_mac','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('device_sn','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
	);
	
	protected $_auto = array(
			array('device_mac', 'strtoupper', self::MODEL_INSERT, 'function'),
			array('device_sn', 'strtoupper', self::MODEL_INSERT, 'function'),
			array('active_time', NOW_TIME, self::MODEL_BOTH),
	);

	
	public function getDeviceList() {
		$prefix = $this->tablePrefix;
		
		$tbl_de = $prefix . $this::TBL_NAME;
		
		$data = M() -> table($tbl_de) -> select();
		trace($tableName);
		return $data;
		
	}
	/**
	 * 添加设备信息
	 * @param unknown $uid
	 * @param unknown $info
	 */
	public function addDevice($userinfo,$devmacinfo, $device_name) {
		\Think\Log::write('device_sn in Model1='.$devmacinfo['device_sn'][0]);
		\Think\Log::write('device_sn in Model2='.$devmacinfo['device_sn'][1]);
		\Think\Log::write('device_sn in Model3='.$devmacinfo['device_sn']);
		\Think\Log::write('device_sn in Model4='.$devmacinfo['device_sn']);
		
		$data1['device_sn'] = $devmacinfo['device_sn'];
		
		$data1['device_mac'] =  $devmacinfo['device_mac'];
		$data1['product_id'] =  $devmacinfo['product_id'];
		
		$data1['device_reg_uid'] = $userinfo['uid'];
		$data1['device_reg_flg'] = '1';
		$data1['device_name'] = $device_name;
		
		$data1 = $this->create($data1);
		$res = $this->add($data1);
		\Think\Log::write('id in device Model='.$res);
		\Think\Log::write('device_reg_flg in device Model='.$date1['device_reg_flg']);
		return $res;
	}
	
	/**
	 * 目前专为解绑设备设定flg用
	 * @param unknown $ids
	 */
	public function updateDeviceList($ids) {
		
	}
	
	public function listDeviceWithProduct() {
		$prefix = $this->tablePrefix;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
		$tbl_product = $prefix . ProductModel::TBL_NAME;
			
		$condition = array();
			
		$data = M()-> field('dv.*, pd.product_name  ')
		-> table($tbl_device . ' dv')
		->join($tbl_product . ' pd on dv.product_id=pd.id')
		->where($condition)-> select();
		
		return $data;
		
	}
	
	public function getPrdocutInfo($deviceSn) {
		$prefix = $this->tablePrefix;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
		$tbl_product = $prefix . ProductModel::TBL_NAME;
		$tbl_picture = $prefix . 'picture';
			
		$condition = array();
		$condition['dv.device_sn']=$deviceSn;
			
		$data = M()-> field('pd.product_name,pd.product_code,pd.logo_img,pc.path')
		-> table($tbl_product . ' pd')
		->join($tbl_device . ' dv on dv.product_id=pd.id')
		->join($tbl_picture . ' pc on pd.logo_img=pc.id')
		->where($condition)-> find();
		
		\Think\Log::write('last sql - '. M()->getLastSql());
		
		return $data;
	}
	
	public function countActiveProduct() {
		$prefix = $this->tablePrefix;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
		$tbl_product = $prefix . ProductModel::TBL_NAME;
			
			
		$data = M()->distinct(TRUE)->field('pd.product_code')
		-> table($tbl_product . ' pd')
		->join($tbl_device . ' dv on dv.product_id=pd.id') -> count();
		
		\Think\Log::write('last sql - '. M()->getLastSql());
		
		return $data;
		
	}
}