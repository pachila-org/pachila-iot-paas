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
class DeviceMacModel extends Model {
	const TBL_NAME = 'device_mac';
	
	protected $tableName=self::TBL_NAME;
	
	
	protected $_auto = array(
			array('device_mac', 'strtoupper', self::MODEL_INSERT, 'function'),
			array('device_sn', 'strtoupper', self::MODEL_INSERT, 'function'),
			array('active_time', NOW_TIME, self::MODEL_INSERT),
	);
	
	public function updateDeviceMac() {
		
	}
	
	
}