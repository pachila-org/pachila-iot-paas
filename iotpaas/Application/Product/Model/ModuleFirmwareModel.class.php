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
 * Class ModuleFirmwareModel
 * @package Product\Model
 */
class ModuleFirmwareModel extends Model {

	const TBL_NAME = 'module_firmware';
	
	protected $tableName=self::TBL_NAME;
	

	protected $_validate = array(
			array('module_id','require','联网模组必须选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('firmware_version','require','版本必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('file_ids','require','至少指定一个文件', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
	);

	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('status', '1', self::MODEL_INSERT),
	);
	
	public function updateFirmwareProducts($firmwareId, $productIds) {
		if (empty($firmwareId)) {
			return true;
		}
		$pfModel = D('Product/ProductFirmware');
		$where['firmware_id'] = $firmwareId;
		$pfModel->where($where)->delete();
		 
		foreach ($productIds as $productId) {
			$pfData = array('product_id'=>$productId, 'firmware_id'=>$firmwareId );
			$pfData = $pfModel ->create($pfData);
			$result = $pfModel->add($pfData);
			if (!result) {
				return false;
			}
		}
		return true;
	}
	
	public function getProductIdList($firmwareId){
		$prefix = $this->tablePrefix;
		$tbl_pf = $prefix . ProductFirmwareModel::TBL_NAME;
		 
		$data = M()-> field('product_id ')
		-> table($tbl_pf)
		->where(array('firmware_id'=>$firmwareId))->select();
		 
		return array_column($data, 'product_id');
	}

}