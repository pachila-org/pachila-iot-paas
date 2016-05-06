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
 * Class DigitalParseRuleModel
 * @package Product\Model
 */
class DigitalParseRuleModel extends Model {

	const TBL_NAME = 'digital_parse_rule';
	
	protected $tableName=self::TBL_NAME;
	

	protected $_validate = array(
			array('product_id','require','关联产品必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('metadata_id','require','关联元数据必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('part_no','require','必须有字段排序值', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('part_type','require','必须有字段类型', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('part_length','require','必须有字段长', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
	);

	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('status', '1', self::MODEL_INSERT),
	);
	

}