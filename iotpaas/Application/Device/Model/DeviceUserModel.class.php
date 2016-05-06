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
use Device\Model\DeviceModel;
use Common\Model\MemberModel;

/**
 * Class DeviceMacModel
 * @package Device\Model
 *
 */
class DeviceUserModel extends Model {
	const TBL_NAME = 'device_user';

	protected $tableName=self::TBL_NAME;
	
	protected $_auto = array(
			array('reg_time', NOW_TIME, self::MODEL_INSERT),
	);

	public function addDeviceUser($res_device_add, $dataList_uid, $datalist_mac) {
		\Think\Log::write('$res_device_add in Model1='.$res_device_add);
		
		\Think\Log::write('addDeviceUser in Model1='.$datalist_mac['device_id']);
		\Think\Log::write('addDeviceUser in Model2='.$dataList_uid['uid']);
//		\Think\Log::write('addDeviceUser in Model3='.$devmacinfo[0]['device_sn']);
//		\Think\Log::write('addDeviceUser in Model4='.$devmacinfo[1]['device_sn']);
			
		$data1['device_id'] = $res_device_add;		
		$data1['person_id'] = $dataList_uid['uid'];
		$data1 = $this->create($data1);
		$res = $this->add($data1);
		return $res;
	}
	
	
	public function listDeviceUser($device_id) {
		$prefix = $this->tablePrefix;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
		$tbl_du = $prefix . DeviceUserModel::TBL_NAME;
		$tbl_user = $prefix . 'member';
		 
		$condition = array();
		$condition['du.device_id']=$device_id;
		 
		$data = M()-> field('du.id, du.device_id, du.person_id, du.relation_type, du.reg_time, dv.device_mac,dv.device_sn,dv.device_name,ur.nickname ')
		-> table($tbl_du . ' du')
		->join($tbl_device . ' dv on du.device_id=dv.id')
		->join($tbl_user . ' ur on du.person_id=ur.uid')
		->where($condition)->order('du.reg_time asc') -> select();
		
		return $data;
	}
	
	public function listUserDevice($user_id) {
		$prefix = $this->tablePrefix;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
		$tbl_du = $prefix . DeviceUserModel::TBL_NAME;
		$tbl_user = $prefix . 'member';
			
		$condition = array();
		$condition['du.person_id']=$user_id;
			
		$data = M()-> field('du.id, du.device_id, du.person_id, du.relation_type, du.reg_time, dv.device_mac,dv.device_sn, dv.device_name,ur.nickname ')
		-> table($tbl_du . ' du')
		->join($tbl_device . ' dv on du.device_id=dv.id')
		->join($tbl_user . ' ur on du.person_id=ur.uid')
		->where($condition)->order('du.reg_time asc') -> select();
	
		return $data;
	}
}