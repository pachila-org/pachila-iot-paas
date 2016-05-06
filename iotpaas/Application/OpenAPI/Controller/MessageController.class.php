<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace OpenAPI\Controller;

class MessageController extends JsonAPIController {
	
	/**
	 * 获取指定用户的消息列表
	 * @param unknown $userId
	 */
	public function listMessage_get($userId) {
		\Think\Log::write('enter listMessage_get ');
		if (!$this->tokenValidation()) {
			return $this->returnErrorToken();
		}
	
		$userId = $this->currentUid;
// 		$userId = 101;
// 		$userId = I('get.userId');	
		
		//验证userId是否存在，不存在的话直接返回错误
		$memberModel = D('Appmgr/Member');
		$condition = array('status' => 1);
		$condition['uid'] = $userId;
		$uid = $memberModel -> where($condition) -> field('uid')->find();
		if (!$uid){
			return $this->returnErrorNotExist($userId);
		}
		
		//从`iot_sms_send_log`与`iot_sms_message`里面取出message id等信息返回前端
		$tbl_issl = "iot_sms_send_log";		
		$tbl_ism = "iot_sms_message";   
		$tbl_isc = "iot_sms_category";
	
		$messagedata = M()-> field(' issl.id messageid,ism.sms_title,ism.sms_content,issl.update_time,issl.member_id,isc.title category')
		->table($tbl_issl . ' issl')
		->join($tbl_ism . ' ism on issl.sms_id= ism.id')
		->join($tbl_isc . ' isc on ism.sms_category= isc.id')
		->where(array('issl.status' => 1,  'issl.member_id'=>$userId))->order(' issl.update_time desc')->select();
			
		return $this->returnSuccess($messagedata);
	}

	/**
	 * 获取指定用户的消息列表
	 * @param unknown $userId
	 * @param unknown $messageId
	 */
	public function getMessage_get($messageId) {
	
// 		$uid = $this->currentUid;
		$messageId = I('get.messageId');	
		
		//验证messageId是否存在，不存在的话直接返回错误
		$smsSendLogModel = D('Appmgr/SmsSendLog');
		$condition = array('status' => 1);
		$condition['id'] = $messageId;
		$message = $smsSendLogModel -> where($condition) -> field('id')->find();
		
		if ($message){
			//从`iot_sms_send_log`与`iot_sms_message`里面取出message id等信息返回前端
			$tbl_issl = "iot_sms_send_log";		
			$tbl_ism = "iot_sms_message";   
			$tbl_isc = "iot_sms_category";
		
			$messagedata = M()-> field(' issl.id messageid,ism.sms_title,ism.sms_content,issl.update_time,issl.member_id,isc.title category')
			->table($tbl_issl . ' issl')
			->join($tbl_ism . ' ism on issl.sms_id= ism.id')
			->join($tbl_isc . ' isc on ism.sms_category= isc.id')
			->where(array('issl.status' => 1,  'issl.id'=>$messageId))->select();

			$data['read_status'] = 1;
			$smsSendLogModel->where($condition)->save($data);
			
			return $this->returnSuccess($messagedata);
		} else {
			return $this->returnErrorNotExist($messageId);		
		}
	}	
}