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
 * Class AppModel 
 * @package Appmgr\Model
 * 
 */
class MemberModel extends Model {
	
	const TBL_NAME = 'member';

    protected $tableName=self::TBL_NAME;
    
//     protected $_validate = array(
//         array('sms_id','require','消息ID', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
//         array('memeber_id','require','会员ID', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
//         array('send_status','require','消息发送状态', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
//     );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );

}