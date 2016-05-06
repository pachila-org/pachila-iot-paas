<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace Product\Model;
use Think\Model;

/**
 * 
 */
class LogConfigModel extends Model {
	
	const TBL_NAME = 'product_logconfig';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('log_condition_type','require','类型必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			//     	array('md_code', '/^[a-zA-Z]\w{0,39}$/', '编码输入不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('status', '1', self::MODEL_BOTH),
	);
	
	public function listAllConfigs($productId) {
		$prefix = $this->tablePrefix;
		$tbl_pm = $prefix . ProductMetadataModel::TBL_NAME;
		$tbl_md = $prefix . MetadataModel::TBL_NAME;
		$tbl_lc = $prefix . self::TBL_NAME;
		
		$result = M()-> field('lc.id, pm.metadata_id, pm.product_id, md.md_code, md.md_name, md.md_type, lc.log_required, lc.log_condition_type, lc.log_condition_value')
		->table($tbl_pm . ' pm')
		->join($tbl_md . ' md on pm.metadata_id=md.id')
		->join($tbl_lc.' lc on pm.product_id=lc.product_id and pm.metadata_id=lc.metadata_id', 'LEFT')
		->where(array('pm.product_id'=>$productId))->select();
		
		return $result;
	}
	
	
}