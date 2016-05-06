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
CREATE TABLE `iot_sms_send_log` (
  `id` int(11) NOT NULL  auto_increment,
  `sms_id` int(11) NOT NULL,
  `memeber_id` int(11) NOT NULL,
  `send_status` tinyint(4) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
       primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment=1;


 */
/**
 * Class AppModel 
 * @package Appmgr\Model
 * 
 */
class SmsSendLogModel extends Model {
	
	const TBL_NAME = 'sms_send_log';

    protected $tableName=self::TBL_NAME;
    
    protected $_validate = array(
        array('sms_id','require','消息ID', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
        array('member_id','require','会员ID', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
        array('send_status','require','消息发送状态', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );

    public function updateSmsSendLog($messageID, $selectedMemberList){
    	if (empty($messageID)) {
    		return true;
    	}
    	 
    	$sms_send_logModel = D('SmsSendLog');
    	
//     	delete records which have not sent out and keep those which have sent
    	$where['read_status'] = 0;
    	$where['sms_id'] = $messageID;
    	$sms_send_logModel->where($where)->delete();

    	foreach ($selectedMemberList as $memberId) {
    		$sms_send_logData = array('sms_id'=>$messageID, 'member_id'=>$memberId, 'read_status'=>0, 'status'=>1,'create_time' =>time(),'update_time' =>time());
    		$sms_send_logData = $sms_send_logModel ->create($sms_send_logData);
    		$result = $sms_send_logModel->add($sms_send_logData);
    		if (!$result) {
    			return false;
    		}
    	}
    	return true;
    }
    
    public function getSmsSendLog($messageID){
    	$prefix = $this->tablePrefix;
    	$tbl_memberModel = $prefix .MemberModel::TBL_NAME;
    	$tbl_sms_send_logModel = $prefix .SmsSendLogModel::TBL_NAME;

       	$data = M()-> field(' mm.uid, mm.nickname ')
    	-> table($tbl_memberModel . ' mm')
    	->join($tbl_sms_send_logModel .' sslm on sslm.member_id = mm.uid')
    	->where(array('sslm.send_status = 0', 'sslm.sms_id'=>$messageID))->select();
    	
    	return array_column($data, 'uid');
    }
    
    //C:\WORKSPACES\php_server\opencenter\Application\Product\Model\ProductModel.class.php
//     	$tbl_sms_send_logModel = $this->tableName; --生成的表名没有IoT前缀（无效表名）
    //Array ( [0] => Array ( [uid] => 1 [nickname] => admin ) [1] => Array ( [uid] => 100 [nickname] => test1 ) [2] => Array ( [uid] => 101 [nickname] => test2 ) [3] => Array ( [uid] => 102 [nickname] => test3 ) )
    //Array ( [id] => 1 [sms_category] => 100 [sms_title] => abcde [sms_content] => 123456 [create_time] => 1450943529 [update_time] => 1451014615 [status] => 1 [memberlist] => Array ( [0] => 100 [1] => 1 ) )
}