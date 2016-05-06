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
 * Class ProductModel 
 * @package Product\Model
 * 
 */
class ProductModel extends Model {
	
	const TBL_NAME = 'product';
	
	protected $tableName=self::TBL_NAME;
	
    protected $_validate = array(
        array('product_code','require','编码必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
//     	array('product_code', '/^[a-zA-Z]\w{0,39}$/', '编码输入不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('product_category','require','分类必须选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );
    
    public function getMetaDataIdList($product_id, $md_type = 0) {
    	$prefix = $this->tablePrefix;
    	$tbl_pm = $prefix . ProductMetadataModel::TBL_NAME;
    	$tbl_md = $prefix . MetadataModel::TBL_NAME;
    	 
    	
    	if ($md_type == 0) {
    		$data = M()-> field('pm.metadata_id') -> table($tbl_pm . ' pm')
    		->where(array('pm.product_id'=>$product_id))->select();
    	}
    	
    	$data = M()-> field('pm.metadata_id') -> table($tbl_pm . ' pm')
    	->join($tbl_md . ' md on pm.metadata_id=md.id')
    	->where(array('md.md_type'=>$md_type, 'pm.product_id'=>$product_id))->select();
    	
    	return array_column($data, 'metadata_id');
    }
    
    public function getMetatDataList($product_id, $md_type = 0) {
    	$prefix = $this->tablePrefix;
    	$tbl_pm = $prefix . ProductMetadataModel::TBL_NAME;
    	$tbl_md = $prefix . MetadataModel::TBL_NAME;
    	
    	$condition = array();
    	$condition['pm.product_id']=$product_id;
    	if ($md_type != 0) {
    		$condition['md.md_type']=$md_type;
    	}
    	
    	$data = M()-> field('pm.id, pm.metadata_id, md.md_name, md.md_code, md.md_type, md.md_value_type, pm.parser_type,pm.parser_attr_1,pm.parser_attr_2,pm.parser_attr_3 ') 
    	-> table($tbl_pm . ' pm')
    	->join($tbl_md . ' md on pm.metadata_id=md.id')
    	->where($condition)->order('md.md_type asc') -> select();
    	 
    	return $data;
    }
    
    public function getModuleIdList($product_id){
    	$prefix = $this->tablePrefix;
    	$tbl_pm = $prefix . ProductModuleModel::TBL_NAME;
    	$tbl_cm = $prefix . ConnectModuleModel::TBL_NAME;
    	
    	$data = M() -> table($tbl_pm) -> where(array('product_id'=>$product_id))->select();
    	     	 
    	return array_column($data, 'module_id');
    }
    
    public function getFirmwareList($product_id){
    	$prefix = $this->tablePrefix;
    	$tbl_pf = $prefix . ProductFirmwareModel::TBL_NAME;
    	$tbl_mf = $prefix . ModuleFirmwareModel::TBL_NAME;
    	
    	$data = M()-> field('mf.* ')
    	-> table($tbl_pf . ' pf')
    	->join($tbl_mf . ' mf on pf.firmware_id=mf.id')
    	->where(array('pf.product_id'=>$product_id))->select();
    	
    	return $data;
    }

    public function updateProductMetadata($productId, $newMdIdList) {
    	$proudctData = $this->find($productId);
    	$where['product_id'] = $productId;
    	$pmModel = D('Product/ProductMetadata');
    	$pmList = $pmModel->where($where)->select();
    	\Think\Log::record('pmList - '.$pmList);
    	// OLD list is not exit
    	if (!isset($pmList)) {
    		\Think\Log::record("here1");
    		foreach ($newMdIdList as $metadataId) {
    			// it's a new metadata, should be added
    			unset($pmData);
    			$pmData = array('product_id'=>$productId, 'metadata_id'=>$metadataId );
    			$result = $pmModel->add($pmData);
    			if (!result) {
    				\Think\Log::record("here2");
    				return false;
    			}
    		}
    		\Think\Log::record("here3");
    		return true;
    	}
    	 
    	$oldMdIdList = array_column($pmList, 'metadata_id');
    	foreach ($newMdIdList as $metadataId) {
    		if (!in_array($metadataId, $oldMdIdList)) {
    			// it's a new metadata, should be added
    			$pmData = array('product_id'=>$productId, 'metadata_id'=>$metadataId );
    			$result = $pmModel->add($pmData);
    			if (!result) {
    				return false;
    			}
    		}
    	}
    	 
    	foreach ($oldMdIdList as $metadataId) {
    		if (!in_array($metadataId, $newMdIdList)) {
    			// it's already removed
    			$where['product_id'] = $productId;
    			$where['metadata_id'] = $metadataId;
    			$pmModel->where($where)->delete();
    		}
    	}
    	return true;
    }
    
    public function updateModuleList($product_id, $moduleIdList) {
    	if (empty($product_id)) {
    		return true;
    	}
    	
    	$proudctData = $this->field($productId);
    	$pmModel = D('Product/ProductModule');
    	$where['product_id'] = $productId;
    	$pmModel->where($where)->delete();
    	
    	foreach ($moduleIdList as $moduleId) {
    		\Think\Log::write('module Id = '.$moduleId);
    		$pmData = array('product_id'=>$product_id, 'module_id'=>$moduleId );
    		$pmData = $pmModel ->create($pmData);
    		$result = $pmModel->add($pmData);
    		if (!result) {
    			return false;
    		}
    	}
    	return true;
    }

}

