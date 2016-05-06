<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Appmgr\Model;
use Think\Model;

/**
 * 
CREATE TABLE `iot_sms_message` (
  `id` int(11) NOT NULL auto_increment,
  `sms_category` int(11) NOT NULL,
  `sms_title` varchar(64) NOT NULL,
  `sms_content` varchar(200) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
       primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment =1;

 */
/**
 * Class AppModel 
 * @package Appmgr\Model
 * 
 */
class SmsMessageModel extends Model {
	
	const TBL_NAME = 'sms_message';

    protected $tableName=self::TBL_NAME;
    
    protected $_validate = array(
        array('sms_category','require','消息分类必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
        array('sms_title','require','消息标题必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
        array('sms_content','require','消息内容必须有', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );
}