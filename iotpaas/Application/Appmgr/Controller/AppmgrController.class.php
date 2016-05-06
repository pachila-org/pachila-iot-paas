<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;

class AppmgrController extends AdminController
{
	protected $appModel;
	protected $smscategoryModel;
	protected $smsmessageModel;
	protected $smssendlogModel;
	protected $memberModel;
	
    public function _initialize()
    {
    	$this->appModel = D('Appmgr/App');
    	$this->smscategoryModel = D('Appmgr/SmsCategory');
    	$this->smsmessageModel = D('Appmgr/SmsMessage');
    	$this->smssendlogModel = D('Appmgr/SmsSendLog');
    	$this->memberModel = D('Appmgr/Member');
    	 
    	parent::_initialize();
    }
 
    public function index($page=1,$r=10){
    	$builder = new AdminListBuilder();
    	$builder -> title('APP管理') -> buttonNew(U('addAPP'));
    	$condition = array('status' => 1);
    	$dataList = $this ->appModel -> where($condition) -> limit(10)->select();
    	trace($dataList);
    	
    	$builder->keyText('app_name', 'APP名称');
    	$builder->keyText('app_version', 'APP版本');
    	$builder->keyText('app_update_comment', '更新说明');
    	$builder->keyDoAction('addAPP?id=###', '编辑');    	 
//     	$builder->keyDoAction('removeAPP?id=###', '删除');
    	
    	$builder -> data($dataList);
    	$builder -> display();

    }
   
    public function listAPP() {
    	// fetch list data
    	$condition = array('status' => 1);
//     	$appdataList = $this->appModel -> where($condition) -> field('id, app_name,app_version,app_update_comment,file_num,file_name,file_path') ->select();
    	$appdataList = $this->appModel -> where($condition) ->select();
    	 
    	trace($appdataList);
    	
    	// build list page
    	$builder = new AdminListBuilder();
    	$builder->title('APP管理');
    	$builder->buttonNew(U('addAPP'));
    	$builder->keyText('app_name', 'APP名称');
    	$builder->keyText('app_version', 'APP版本');
    	$builder->keyText('app_update_comment', '更新说明');
    	$builder->keyDoAction('addAPP?id=###', '编辑');
//     	$builder->keyDoAction('removeAPP?id=###', '删除');
    	 
    	$builder->data($appdataList)->display();
    }
    
    public function addAPP($id = 0){
    	if (IS_POST) {
    		//do save data
    		$data = $this->appModel->create();
    		if (!$data) {
    			$this -> error($this->appModel->getError());
    			return;
    		}
    		if ($id==0) {
    			$result = $this->appModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->appModel->save($data);
    		}
    		
    		if (!$result) {
    			$this->error('保存失败:'.$this->appModel ->getError());
    		}
    		
    		$this->success('保存成功.', U('listAPP'));
    	} else {
    		// do show new or edit page
    		if ($id != 0) {
    			$data = $this->appModel->find($id);
    		}
    		trace($data);
    		
    		
    		$app_type = C("APP_TYPE");
    		$business_type = C("business_type");
    		
    		$builder = new AdminConfigBuilder();
    		$builder -> title((($id==0)?'新增':'修改') . 'APP版本');
    		$builder -> keyId(); 
    		$builder -> keySelect('app_type', 'App类型', '选择一个类型', $app_type); //
    		$builder -> keySelect('business_type', '业务类型', '选择一个类型', $business_type); //
    		$builder -> keyText('app_name', 'APP名称');
    		$builder -> keyText('app_version', 'APP版本');
    		$builder -> keyText('app_update_comment', 'APP更新说明');    		
    		$builder -> keyMultiFile('file_name', 'APP文件', '可以包含多个文件');
    		
    		$builder ->buttonSubmit(U('addAPP')) -> buttonBack();
    		$builder ->data($data);
    		
    		$builder -> display();
    	}
    }
    	
    	public function removeAPP($id = 0){
    		//do save data
    		$data = $this->appModel->create();
    		if (!$data) {
    			$this -> error($this->appModel->getError());
    			return;
    		} else {
    			//删除数据
    			$data['status'] = '0';
    			$result = $this->appModel->save($data);
    		}
    		
    		if (!$result) {
    			$this->error('删除失败:'.$this->appModel ->getError());
    		}
    		
    		$this->success('删除成功.', U('listAPP'));
    	}
   
    	public function listSmsMessage() {
    		// fetch list data
    		$condition = array('status' => 1);
    		$appdataList = $this->smsmessageModel->select();
    	
    		trace($appdataList);
    		 
    		$categories = $this->smscategoryModel->select();
    		$categoryMap = array_combine(array_column($categories, 'id'), array_column($categories, 'title'));
    		trace($categoryMap);
    		
    		// build list page
    		$builder = new AdminListBuilder();
    		$builder->title('消息管理');
    		$builder->buttonNew(U('addSmsMessage'));
    		
    		$builder -> keyMap('sms_category', '消息类别', $categoryMap);
    		$builder->keyText('sms_title', '消息标题');
    		$builder->keyText('sms_content', '消息内容');
    		$builder->keyDoAction('addSmsMessage?id=###', '编辑');
    	
    		$builder->data($appdataList)->display();
    	}
    	
    	public function addSmsMessage($id = 0) {
    	if (IS_POST) {
    		//do save data
    		$data = $this->smsmessageModel->create();
    		if (!$data) {
    			$this -> error($this->smsmessageModel->getError());
    			return;
    		}
    		if ($id==0) {
    			$result = $this->smsmessageModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->smsmessageModel->save($data);
    		}
    		
    		if (!$result) {
    			$this->error('保存失败:'.$this->smsmessageModel ->getError());
    		}
    		
    		$messageID = $id;
    		$selectedMembers = I('post.memberlist');
    		$selectedMemberList = $selectedMembers?$selectedMembers:array();
    		trace($selectedMemberList);
    		
    		$result = $this->smssendlogModel -> updateSmsSendLog($messageID, $selectedMemberList);
    		
    		if ($result) {
    			$this->success('保存成功.', U('listSmsMessage'));
    		} else {
    			$this->error('保存失败');
    		}
    	} else {
    		// init category options
    		$categories = $this->smscategoryModel->select();
    		$categoryMap = array_combine(array_column($categories, 'id'), array_column($categories, 'title'));
    		trace($categoryMap);
    		 
    		// init user list
    		$condition['status'] = '1' ;
    		$members = $this ->memberModel -> field('uid,nickname') -> where($condition)-> order('nickname asc') -> select();
    		trace($members);
    		
   			// do show new or edit page
    		if ($id != 0) {
    			$data = $this->smsmessageModel->find($id);
    			$data['memberlist'] = $this->smssendlogModel->getSmsSendLog($id);
    			trace($data);
    		}
    		
    		// build page
    		$builder = new AdminConfigBuilder();
    		$builder -> title((($id==0)?'新增':'修改') . '消息');
    		$builder -> keyId(); 
    		$builder -> keySelect('sms_category', '消息类别', '选择一个消息类型', $categoryMap); //
    		$builder -> keyText('sms_title', '消息标题');
    		$builder -> keyText('sms_content', '消息内容');    		
    		$builder -> keyChosen('memberlist', '接收消息的用户', '选择接收该消息的用户，可以多选', $members);
    		
    		$builder ->buttonSubmit(U('addSmsMessage'));
    		$builder->buttonBack();
    		$builder ->data($data);
    		$builder -> display();
    	}
    }
}