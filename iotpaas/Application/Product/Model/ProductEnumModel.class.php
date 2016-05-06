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

class ProductEnumModel extends Model {
	
	const TBL_NAME = 'product_enum';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('enum_key','require','源格式必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
	);
	
	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('status', '1', self::MODEL_BOTH),
	);
	
	
}