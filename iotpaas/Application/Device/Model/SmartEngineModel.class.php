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
use Product\Model\MetadataModel;

/**
 * Class DeviceMacModel
 * @package Device\Model
 *
 */
class SmartEngineModel extends Model {
	const TBL_NAME = 'smart_engine';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('engine_type','require','必须选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('engine_name','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
			array('owner_uid','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
	);
	
	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
	);
	
	public function listEngineByCondition($deviceMac, $enginType = 0) {
		$prefix = $this->tablePrefix;
		$tbl_engine = $prefix . SmartEngineModel::TBL_NAME;
		$tbl_condition = $prefix . EngineConditionModel::TBL_NAME;
			
		$condition = array();
		$condition['cd.device_mac']=$deviceMac;
		if ($enginType > 0) {
			$condition['en.engine_type']=$enginType;
		}
			
		$data = M()-> field('en.*, cd.md_code, cd.eigen_value')
		-> table($tbl_engine . ' en')
		->join($tbl_condition . ' cd on cd.engine_id=en.id')
		->where($condition) -> select();
		\Think\Log::write('Last sql = '.M()->getLastSql());
		
		return $data;
	}
	
	public function listEngineActions($engineId) {
		$prefix = $this->tablePrefix;
		$tbl_action = $prefix . EngineActionModel::TBL_NAME;
		$tbl_md = $prefix . MetadataModel::TBL_NAME;
		$tbl_device = $prefix . DeviceModel::TBL_NAME;
			
		$condition = array();
		$condition['ac.engine_id']=$engineId;
			
		$data = M()-> field('ac.*, md.md_name, dv.device_name, dv.device_sn')
		-> table($tbl_action . ' ac')
		->join($tbl_md . ' md on ac.md_code=md.md_code')
		->join($tbl_device . ' dv on ac.device_mac=dv.device_mac')
		->where($condition) -> select();
		
		\Think\Log::write('Last sql = '.M()->getLastSql());
		
		return $data;
	}
		
}