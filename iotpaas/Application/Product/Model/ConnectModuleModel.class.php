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
 * Class ConnectModuleModel
 * @package Product\Model
 * 
 * 
 *
 */
class ConnectModuleModel extends Model {

	const TBL_NAME = 'connect_module';
	
	protected $tableName=self::TBL_NAME;
	

	protected $_validate = array(
			array('module_type','require','分类名称必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			//     	array('md_code', '/^[a-zA-Z]\w{0,39}$/', '编码输入不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
			array('module_name','require','型号必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			array('module_name', '', '型号必须维一', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
	);

	protected $_auto = array(
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_BOTH),
			array('status', '1', self::MODEL_INSERT),
	);

}