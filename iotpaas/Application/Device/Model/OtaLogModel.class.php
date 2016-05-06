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

/**
 * Class DeviceMacModel
 * @package Device\Model
 *
 */
class OtaLogModel extends Model {
	const TBL_NAME = 'ota_log';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('product_code','require','编码必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('product_category','require','分类必须选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
	);
	
	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('ota_status', '1', self::MODEL_INSERT),
	);
	
}