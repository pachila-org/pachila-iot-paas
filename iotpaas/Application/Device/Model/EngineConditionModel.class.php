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
class EngineConditionModel extends Model {
	const TBL_NAME = 'engine_condition';
	
	protected $tableName=self::TBL_NAME;
	
	protected $_validate = array(
			array('engine_id','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
// 			array('device_id','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
			array('md_code','require','必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
	);
	
	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
	);
	
}