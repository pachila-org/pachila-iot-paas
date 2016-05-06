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
class DeviceLogModel extends Model {
	const TBL_NAME = 'device_log';
	
	protected $tableName=self::TBL_NAME;
	
}