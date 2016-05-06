<?php 
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

use Common\Driver\Redis;


function getRedis() {
	static $redis = '';
	if (empty($redis)) {
		\Think\Log::write('redis_set new redis');
		$redis = new Redis(array('type'=>'redis', 'host'=>C('REDIS_HOST', null, '127.0.0.1'),'port'=>C('REDIS_PORT', null, 6379), 'persistent'=>false));
	}
	return $redis;
}

function releaseRedis($redisIns = null) {
	// TODO
}

