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
 * Class ProductFirmwareModel 
 * @package Product\Model
 * 
 */
class ProductFirmwareModel extends Model {
	
	const TBL_NAME = 'product_firmware';

    protected $tableName=self::TBL_NAME;
    
    protected $_validate = array(
        array('product_id','require','相关产品必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
    	array('firmware_id','require','相关固件必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );


}