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
use Device\Service\DeviceService;



class DeviceController extends AdminController
{
	protected $deviceModel;
	protected $deviceMacModel;
	protected $productModel;
	protected $deviceUserModel;
	protected $deviceLogModel;
	
    /**
     * 业务逻辑都放在 WeiboApi 中
     * @var
     */
    public function _initialize()
    {
    	$this->deviceModel = D('Device/Device');
    	$this->deviceUserModel = D('Device/DeviceUser');
    	$this->deviceMacModel = D('Device/DeviceMac');
    	$this->productModel = D('Product/Product');
    	$this->deviceLogModel = D('Device/DeviceLog');
    	 
    	parent::_initialize();
    	trace("初始化");
    }

    public function index_old($page=1,$r=10){

    	$id = I('id');
    	$device_sn = I('device_sn');
    	$device_reg_flg = I('device_reg_flg');
		//trace("deviceid");
		//trace($device_id);
		//这里要求是已经注册有效的设备
		$map['device_reg_flg'] = 1;
		
		if($id or $device_sn){
			if ($id){
				$map['id'] = array(intval($id), array('like', '%' . $id . '%'));
				
			}
 			if ($device_sn) {
				$map['device_sn'] = array(intval($device_sn), array('like', '%' . $device_sn . '%'));
			}
			//trace("map");
			//trace($map);
				
    		$dataList = $this -> deviceModel  ->where($map)->select();
    		//trace("device id true");
		} else {
			$dataList = $this -> deviceModel  ->where($map) -> select();
			//trace("device id false");
				
		}

		//为admin_solist返回值是添加id字段
		foreach ($dataList as &$item) {
			$item['device_id'] = $item['id'];
		}
		
    	//显示页面
    	$builder = new AdminListBuilder();
    	$builder -> title('设备管理');

    	$condition = array();

    	$builder -> suggest('STEP1');
    	//----------------------------搜索框
        $builder->setSearchPostUrl(U('Admin/Device/index'))->search('设备ID', 'device_id', 'text', '请输入设备ID');
        $builder->setSearchPostUrl(U('Admin/Device/index'))->search('设备sn', 'device_sn', 'text', '请输入设备sn');
        
    	
    	//----------------------
    	//TODO:解绑定有bug，页面刷新后checkbox还是被check并且按钮被disable掉了
    	//参考对象是usercontroller的deltype
    	$builder->button('取消注册',array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要解除该设备的注册吗！）', 'url' => U('detachDevice'), 'target-form' => 'ids'))
    	->keyText("id", "设备ID")
    	->keyText('product_id', '产品ID')
    	->keyText("device_sn", "设备SN")
    	->keyText("device_mac", "设备Mac地址")
    	->keyText("device_reg_uid", "设备用户ID")
 		-> keyDoAction('controlDevice?product_id={$product_id}&device_id={$device_id}', '控制')
    	-> keyDoAction('listDeviceUser?id={$device_id}', '设备用户访问控制');
    	// 数据及分页
    	$builder->data($dataList)
    	->pagination($totalCount, $r);
    	// 显示
    	$builder->display();
    	 
    }
    
    public function index($page=1,$r=10){
    	
    	$redis = getRedis();
    	
    	$condition = array();
    	$dataList = $this -> deviceModel -> listDeviceWithProduct();
    	
    	foreach ($dataList as &$data) {
    		$data['online_status']= $redis->hget($data['device_mac'], 'online_status');
    	}
   	 
    	$builder = new AdminListBuilder();
    	$builder -> title('设备列表');
    	 
    	$builder -> keyText('device_name', '设备名称');
    	$builder -> keyText('device_sn', '设备序列号');
    	$builder -> keyText('product_name', '产品名称');
    	$builder -> keyMap('device_status', '设备状态', C('DEVICE_STATUS'));
    	$builder -> keyMap('online_status', '在线状态', C('ONLINE_STATUS'));
    	$builder -> keyText('device_firmware_updatetime', '激活时间');
    	 
    	$builder -> keyDoAction('controlDevice?product_id={$product_id}&device_id={$id}', '&nbsp;控制&nbsp;');
    	$builder -> keyDoAction('listDeviceUser?id=###', '&nbsp;授权&nbsp;');
    	$builder -> keyDoAction('deleteDevice?id=###', '&nbsp;删除&nbsp;');
    	 
    	$builder -> data($dataList);
    	$builder -> display();
    }
    
    /*
     * 设备注册
     */
    public function register(){
    	//显示页面
    	$builder = new AdminConfigBuilder();
    	$builder -> title('设备注册');
    	$condition = array();


    	//----------------------
    	
    	$builder -> keyDataSelect('device_sn', '选择设备', null, 'listDeviceMac' );
    	$builder -> keyDataSelect('uid', '选择人员', null, 'listUser' );
    	$builder -> keyText('device_name', '设备取名');
    	$builder -> buttonSubmit(U('addDevice'), '确定');
    	
    	// 数据及分页
    	$builder->data($dataList);
    	
    	// 显示
    	$builder->display();
    	
    }
    
    public function listCSVfile() {
    	$condition = array();
    	$deviceMacFileModel = D('Device/DeviceMacFile');
    	$dataList = $deviceMacFileModel -> where($condition) ->select();
    	 
    	$builder = new AdminListBuilder();
    	$builder -> title('CSV文件列表');
    	$builder -> keyText('id', 'id')
    	->keyFile('path', '文件');
    	
    	// 数据及分页
    	$builder->data($dataList);
    	 
    	// 显示
    	$builder->display();
    	 
    }
    
    public function uploadCSVfile() {
    	
    	$deviceMacFileModel = D('Device/DeviceMacFile');
    	$fileModel = D('File');
    	if (IS_POST){
    		
    		$dmfData = $deviceMacFileModel->create();
    		
    		\Think\Log::write('DeviceController.upload result.'.$result);
    		\Think\Log::write('DeviceController.upload result path.'.$dmfData['path']);
    		
    		//从upload号后的数据库中取得path字段，管理找到file 表中的文件
    		$file = D('File')->find($dmfData['path']);
    		
    		//构建文件在服务器端全路径。配置路径+文件表中保留下来的路径
    		$file_full_path = C('DOWNLOAD_UPLOAD')['rootPath'].$file['savepath'].$file['name'];
    		
    		//CSV文件导入
    		//1。打开文件
    		$handle = fopen($file_full_path, 'r');
    		//2.按CSV方式读取
    		//$out = array();
    		$newData = array();
    		$n = 0;
    		while ($data = fgetcsv($handle, 10000)) {
    			$num = count($data);
    			//处理表头
    			if($n == 0){
    				
    			} else {
//     				for ($i = 0; $i < $num; $i++) {
    					
//     					$out[$n][$i] = $data[$i];
//     					$newData[$n-1]['device_sn'] = $data[$i];
//     					\Think\Log::write('DeviceController.upload each data cell'.$data[$i]);
//     				}
    				$newData[$n-1]['device_sn'] = $data[0];
    				$newData[$n-1]['device_mac'] = $data[1];
    				$newData[$n-1]['product_id'] = $data[2];
    				$newData[$n-1]['device_wifi_mod'] = $data[3];
    				$newData[$n-1]['device_produce_batch'] = $data[4];
    				$newData[$n-1]['register_flg'] = $data[5];
    				$newData[$n-1]['update_userid'] = $data[6];
    				$newData[$n-1]['update_timestamp'] = $data[7];
    				
    			}
    			$n++;

    		}
    		//批量写入device_mac 数据库
    		$this ->deviceMacModel ->addAll($newData);
    		
    		//写入MacFile数据库
    		$result = $deviceMacFileModel->add($productData);
    		
    	}
    	$builder = new AdminConfigBuilder();
    	$builder -> title('上传CSV文件')
    	->keySingleFile('path', 'csv文件');
    	trace('DOWNLOAD_UPLOAD');
    	trace(C('DOWNLOAD_UPLOAD'));
    	 
    	$builder->buttonSubmit(U('uploadCSVfile'));
    	 
    	$builder->display();

    	 
    	 
    }
    
    /**
     * 暂没使用
     * @param number $page
     * @param number $r
     */
        
    public function listDeviceMac($page=1,$r=10){
    	\Think\Log::write('enter  listDeviceMac');
    	
    	$builder = new AdminListBuilder();
    	$builder -> title('MAC地址列表') ;
    	$condition = array();
    	$dataList = $this -> deviceMacModel -> where($condition) ->select();

    	$productMap=array();
    	foreach ($dataList as &$macItem) {
    		$productId = $macItem['product_id'];
    		$productName = $productMap[$productId];
    		\Think\Log::write('get name = '.$productName.' from id ='.$productId);
    		if (empty($productName)) {
    			$product = $this->productModel->find($productId);
    			$productName = $product['product_name'];
    			$productMap[$productId]=$productName;
    			\Think\Log::write('get name = '.$productName.' from id ='.$productId);
    		}
    		if (empty($productName)) {
    			$macItem['product_name']=$productId;
    		} else {
    			$macItem['product_name']=$productName;
    		}
    	
    	}
    	
    	$builder->buttonNew(U('addDeviceMac'));
    	$builder->keyText('product_name', '产品')
    	->keyText("device_sn", "设备SN")
    	->keyText("device_mac", "设备Mac地址")
    	->keyTime("update_timestamp", "维护时间");
    
    	$builder -> data($dataList);
    	$builder -> display();
    }
    
    public function addDeviceMac() {
    	if (IS_POST) {
    		$data = $this->deviceMacModel->create();
    		if (!$data) {
    			$this -> error($this->deviceMacModel->getError());
    			return;
    		}
    		if ($id==0) {
    			$result = $this->deviceMacModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->deviceMacModel->save($data);
    		}
    		if (!$result) {
    			$this->error('保存失败:'.$this->deviceMacModel ->getError());
    		}
    	
    		$this->success('保存成功.', U('listDeviceMac'));
    	} else {
    		// do show new or edit page
    		if ($id == 0) {
    			$data = array();
    		} else {
    			$data = $this->deviceMacModel->find($id);
    		}
    		
    		$productDatas = $this->productModel->select();
    		$proudctMap = array_combine(array_column($productDatas, 'id'), array_column($productDatas, 'product_name'));
    	
    		// build page
    		$builder = new AdminConfigBuilder();
    		$builder -> title((($id==0)?'新增':'修改') . 'MAC地址');
    		$builder -> keyId();
    		$builder -> keyText('device_mac', 'MAC地址');
    		$builder -> keyText('device_sn', '设备SN');
    		$builder -> keySelect('product_id', '产品', '', $proudctMap);
    	
    		$builder ->buttonSubmit(U('addDeviceMac')) -> buttonBack();
    		$builder -> data($data);
    	
    		$builder -> display();
    	}
    }
    
    public function listUser($page=1,$r=10){
    	$builder = new AdminListBuilder();
    	$builder -> title('人员列表') ;
    	$condition = array();
    	$dataList = D('Member') -> where($condition) -> limit(10)->select();
    	 
    	foreach($dataList as &$item){
    		$item['id'] = $item['uid'];
    	}
    	
    	$builder->keyText("uid", "用户ID");
    	
    	$builder -> data($dataList);
    	$builder -> display('admin_solist');
    	
    }
    
    public function addDevice() {
    	
    	$device_sn = I('POST.device_sn');
    	\Think\Log::write('$device_sn='.$device_sn);
		$uid =  I('POST.uid');
		\Think\Log::write('$uid='.$uid);
		$device_name = I('POST.device_name');
		
    	//获取device_mac表中记录信息。
        if ($device_sn) {
        	\Think\Log::write('判断$device_sn不为空'.$datalist_mac);
        	 
    		$map_sn['device_sn'] = array(intval($device_sn), array('like', '%' . $device_sn . '%'));
    	}
    	$datalist_mac = $this ->deviceMacModel->where($map_sn) ->select();
    	\Think\Log::write('$device_mac='.$datalist_mac);
    	 
    	
    	//获取user表中记录信息。
    	if ($uid) {
    		$map_uid['uid'] = array(intval($uid), array('like', '%' . $uid . '%'));
    	}
    	$dataList_uid = D('Member') -> where($map_uid) ->select();
    	
    	//TODO:要加入check已经注册过的布恩能够再注册
    	//     检索device_sn和已经注册有效的设备$map['device_reg_flg'] = 1;
    	//		如果存在将报错，不存在继续往下执行
    	
    	//前提条件在conf中加入addDevice url。
    	if (IS_POST){
     		//添加了设备表
    		$res_device_add = $this->deviceModel->addDevice($dataList_uid[0],$datalist_mac[0],$device_name);
    		
     		//添加设备用户表
     		$this->deviceUserModel->addDeviceUser($res_device_add, $dataList_uid, $datalist_mac);
    	}
    }
    
    public function deleteDevice($id) {
    	// remove device user
    	$this->deviceUserModel->where(array('device_id'=>$id))->delete();    	
    	// remove device data
    	$this->deviceModel->where(array('id'=>$id))->delete();
    	
    	$this->success('删除成功', '', 1);
    }
    
    /**
     * 
     * @param unknown $product_id
     * @param unknown $device_id
     */
    public function controlDevice($product_id, $device_id) {
    	
    	trace('$product_id');
    	trace('$device_id'.$device_id);
    	
    	$redis = getRedis();
    	
    	//在menu表中要加入一条controlDevice数据
    	
    	//用productID找到function list.
     	$productData = $this->productModel->find($product_id);
     	$productData['function_list']=$this->productModel->getMetatDataList($product_id, 1);
     	
     	$deviceData = $this->deviceModel->find($device_id);
     	$deviceStatusData = $redis->hgetall($deviceData['device_mac']);
     	
     	
     	
    	//画页面
    	$builder = new AdminConfigBuilder();
    	$builder -> title('设备控制界面');
    	
    	$statusKeys = array_keys($deviceStatusData);
    	foreach ($statusKeys as $itemKey) {
    		$builder->keyLabel($itemKey, $itemKey);
    	}
    	
    	$deviceStatusData['device_mac'] = $deviceData['device_mac'];    	
    	$builder->keyHidden('device_mac', '');
    	$builder->keyText('cmd_value', '命令参数');
    	
    	trace('$productData[function_list] = '.$productData['function_list']);
    	
     	foreach($productData['function_list'] as $item) {
			$builder->button($item['md_name'],array('class' => 'btn ajax-post', 'target-form'=>'form-horizontal', 'url' => U('excuteCmd', array('cmdCode'=>$item['md_code']))));
     	}
     	
     	$builder->buttonBack();
    	$builder->data($deviceStatusData);
    	$builder->display();
    }
    
    public function excuteCmd($cmdCode) {
    	$device_mac = I('POST.device_mac');
    	$cmdValue = I('POST.cmd_value');
    	if (!empty($cmdValue)) {
    		$cmdValueJson = json_decode($cmdValue);
    	}
    	
    	\Think\Log::write('cmdCode='.$cmdCode.' device_mac='.$device_mac.' cmdvalue='.$cmdValue);
    	
    	$service = new DeviceService();
    	if ($cmdValueJson) {
    		$result = $service->asynCommand($device_mac, UID, $cmdCode, $cmdValueJson);
    	} else {
    		$result = $service->asynCommand($device_mac, UID, $cmdCode, $cmdValue);
    	}
    	
    	$this->success($result['result'], '', 1);
    }
    
    /**
     * 根据ID解绑定设备，将设备的device_reg_flg置0；
     * @param unknown $ids
     */
    public function detachDevice($ids) {
    	
    	//     		//1。先取data
    	//     		//2。将data中的flg替换
    	//     		//3。保存data
    	//TODO：deviceuser表的相应处理是/无效还是/删除还是/不管？
    	$res=$this->deviceModel->where(array('id'=>array('in',$ids)))->save(array('device_reg_flg'=>0));
  
        if ($res) {
            $this->success('解注册成功');
        } else {
            $this->error('解注册失败');
        }
    }
    
    /**
     * 设备用户一览页面，用于设备用户的增删改
     * @param unknown $id
     */
    public function listDeviceUser($id) {
    	
    	//显示页面
    	$builder = new AdminListBuilder();
    	$builder -> title('设备授权用户一览');
    	
    	
    	
    	$dataList = $this -> deviceUserModel  ->listDeviceUser($id);
    	 
    	$builder
    	//此处的editDeviceUser参考usercontroller的editScoreType
        // ->buttonNew(U('editDeviceUser',array('device_id' => $id)))
    	// ->button('删除',array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除该设备用户吗？！）', 'url' => U('delDeviceUser'), 'target-form' => 'ids'))
    	->keyText("device_name", "设备")
    	->keyText('nickname', '用户')
    	->keyMap('relation_type', '权限类型', C('DEVICE_USRE_TYPE'))
    	-> keyDoAction('editDeviceUser?person_id={$person_id}&device_id={$device_id}', '更改授权')
    	-> keyDoAction('deleteDeviceUser?person_id={$person_id}&device_id={$device_id}', '解除授权');
    	// 数据及分页
    	$builder->data($dataList) ->pagination($totalCount, $r);
    	
    	$builder->display();
    	
    }
    
    /**
     * 
     */
    public function editDeviceUser() {
    	//也需要在iot_menu中加入‘editDeviceUser’这一条
    	
    	$id = I('person_id',0,'intval');
    	\Think\Log::write('function editDeviceUser aId = '.$id);
    	$device_id = I('device_id',0,'intval');
    	\Think\Log::write('function editDeviceUser $device_id = '.$device_id);
    	
    	//新增或修改标示 0:修改， 1:新增
    	$new_flg = I('new_flg',0, 'intval');
    	//有必要重定义。
    	$model = D('Device/DeviceUser');
    	
    	if (IS_POST) {
    		
    		\Think\Log::write('function editDeviceUser IS_POST');
    		\Think\Log::write('function editDeviceUser new flg = '.$new_flg);
    		
    		//post的时候做保存
    		$data['person_id'] = I('post.person_id','','intval');
    		\Think\Log::write('function editDeviceUser person id = '.$data['person_id'] );
    		$data['device_id'] = I('post.device_id','','intval');
    		$data['auth_level'] = I('post.auth_level','','op_t');
    		$data['user_type'] = I('post.user_type','','op_t');
    		
    		
    		if ($new_flg == 0) {
    			\Think\Log::write('function editDeviceUser IS_POST & have ID');
    			 
    		$res=$this->deviceUserModel->where(array('person_id'=>$data['person_id'],'device_id'=>$data['device_id']))->save($data);
    		} else {
    			//$res = $model->addType($data);
    			$res = $this ->deviceUserModel ->add($data);
    		}
    		if ($res) {
    			$this->success(($new_flg == 1 ? '添加' : '编辑') . '成功');
    		} else {
    			$this->error(($new_flg == 1 ? '添加' : '编辑') . '失败');
    		}
    		
    		
    	} else {
    		
    		\Think\Log::write('function editDeviceUser IS_NOT_POST');
    		$builder = new AdminConfigBuilder();

    		$builder->title(($id == 0 ? '新增' : '编辑').'设备用户');
    		
    		$builder->keyReadOnly('device_id', '设备ID');
    		$builder -> keyHidden('new_flg');
    		
    		//有用户ID的场景属修改权限。
    		if($id) {
    			$builder->keyReadOnly('person_id', '用户ID');
    			 
    			\Think\Log::write('function editDeviceUser IS_NOT_POST & know ID');
    			 //取修改前数据
    			$deviceUserData = $model -> where(array('person_id'=>$id,'device_id'=>$device_id)) ->select();
    			trace('data after select :'.$deviceUserData);
    			trace($deviceUserData);
    			$deviceUserData[0]['new_flg'] = 0;
    			
    			\Think\Log::write('function editDeviceUser new flg 修改= '.$deviceUserData[0]['new_flg']);    
    		//没有用户id的场景属新建
    		} else {
    			//设置设备ID
    			\Think\Log::write('设置设备ID & know ID：'.$device_id);
    			$deviceUserData[0]['device_id'] = &$device_id;
    			
    			//用户ID变成可选择输入：
    			$builder->keyDataSelect('person_id', '用户ID', null, 'listUser' );
    			$deviceUserData[0]['new_flg'] = 1;
    			\Think\Log::write('function editDeviceUser new flg 新建= '.$deviceUserData[0]['new_flg']);
    			 
    			
    		}
    		//$deviceUserData['device_id']=$device_id;
    		//$deviceUserData['person_id']=$id;
    		trace('data after set :'.$deviceUserData);
    		trace($deviceUserData);
    		
    		//设置设备ID
    		$builder->keySelect('auth_level', '权限水平', null,  array(-1 => '完全控制', 0 => '部分控制', 1 => '只读'))
    		->keySelect('user_type', '用户类型', null,  array(-1 => '所有权者', 0 => '后台管理用户', 1 => '一般使用者'))
    		->data($deviceUserData[0])
    		->buttonSubmit(U('editDeviceUser'))->buttonBack()->display();
    	}
    }
    
    public function deleteDeviceUser($person_id,$device_id) {
    	$where = array();
    	$where['device_id']=$device_id;
    	$where['person_id']=$person_id;
    	$this->deviceUserModel->where($where)->delete();
    	$this -> success('删除成功');
    }
    
    public function editUserAccess($product_id, $device_id) {
    	
    }
    
    public function listDeviceLog($page=1,$r=10) {

    	$datalist = $this->deviceLogModel->order('update_date desc')-> page($page, $r)->select();
    	$totalCount = $this->deviceLogModel->count();
    	
    	$deviceMap=array();
    	foreach ($datalist as &$logItem) {
    		$deviceId = $logItem['device_id'];
    		$deviceName = $deviceMap[$deviceId];
    		if (empty($deviceName)) {
    			$device = $this->deviceModel->find($deviceId);
    			$deviceName = $device['device_name'];
    			$deviceMap[$deviceId]=$deviceName;
    		}
    		if (empty($deviceName)) {
    			$logItem['device_name']=$deviceId;
    		} else {
    			$logItem['device_name']=$deviceName;
    		}
    		
    	}
    	
    	$builder = new AdminListBuilder();
    	$condition = array();
    	$builder -> title('设备日志列表') ;
    	$builder
    	->keyText('device_name', '设备')
    	->keyTime('update_date', '时间')
    	->keyText('md_code', '元数据')
    	->keyText("log_value", '日志值')
    	->keyText('log_display_txt', '日志信息');
    	 
    	$builder -> data($datalist) ->pagination($totalCount, $r);
    	$builder -> display();
    	 
    	
    }
    
    public function listSmartEngins($page=1,$r=10) {
    	
    	$engineModel = D('Device/SmartEngine');
    	$condition = array('owner_uid'=>UID);
    	$dataList = $engineModel -> where($condition) ->select();
    	
    	$builder = new AdminListBuilder();
    	$builder -> title('智能引擎列表') -> buttonNew(U('editSmartEngins'));
    	
    	$builder -> keyText('engine_name', '引擎名称');
    	$builder -> keyMap('engine_type', '引擎分类', C('ENGINE_TYPE'));
    	$builder -> keyText('engine_memo', '引擎备注');
    	$builder -> keyTime('update_time', '更新时间');
    	
    	$builder ->keyDoActionEdit('editSmartEngins?id=###', '编辑', '操作');
    	$builder ->keyDoAction('listEnginCondtions?id=###', '前提条件', '操作');
    	$builder ->keyDoAction('listEnginActions?id=###', '相关动作', '操作');
    	
    	$builder -> data($dataList);
    	$builder -> display();
    }
    
    public function editSmartEngins($id=0) {
    	$engineModel = D('Device/SmartEngine');
    	if (IS_POST) {
    		// save the data
    		if (!$engineModel -> create()) {
    			$this->error($engineModel ->getError());
    			return;
    		} else {
    			if ($id == 0) {
    				// add data
    				if ($engineModel -> add()){
    					$this -> success('新增成功', U('listSmartEngins'));
    				} else {
    					$this -> error('新增失败');
    				}
    			} else {
    				// update data
    				if ($engineModel -> save()){
    					$this -> success('更新成功', U('listSmartEngins'));
    				} else {
    					$this -> error('更新失败');
    				}
    			}
    		}
    	
    	} else {
    		// construct current category
    		if ($id != 0) {
    			$data = $engineModel->find($id);
    		} else {
    			$data = array('owner_uid'=>UID);
    		}
    	
    		$builder = new AdminConfigBuilder();
    		$builder -> title(($id==0)?'新增':'修改'.'智能引擎');
    		$builder -> keyId();
    		$builder -> keyText('engine_name', '引擎名称');
    		$builder -> keySelect('engine_type', '引擎分类', '', C('ENGINE_TYPE'));
    		$builder -> keyText('engine_memo', '引擎备注');
    		$builder -> keyHidden('owner_uid', '');
    		
    		$builder -> data($data);
    		$builder -> buttonSubmit(U('editSmartEngins'))->buttonBack();
    		$builder -> display();
    	}
    }
    
    public function listEnginCondtions($id) {
    	$conditionModel = D('Device/EngineCondition');
    	$where = array('engine_id'=>$id);
    	$dataList = $conditionModel -> where($where) ->select();
    	
    	$deviceData = $this->deviceUserModel->listUserDevice(UID);
    	$deviceMap = array_combine(array_column($deviceData, 'device_mac'), array_column($deviceData, 'device_name'));
    	 
    	$builder = new AdminListBuilder();
    	$builder -> title('智能引擎前提条件') -> buttonNew(U('editEnginCondtion', array('id'=>0, 'engineId'=>$id)));
    	$builder -> button('返回',array("href"=>U('listSmartEngins')));
    	 
    	$builder -> keyText('sort', '条件序号');
    	$builder -> keyMap('device_mac', '相关设备', $deviceMap);
    	$builder -> keyText('md_code', '元数据编码');
    	$builder -> keyText('eigen_value', '特征值');
    	 
    	$builder ->keyDoActionEdit('editEnginCondtion?id=###&engineId={$engine_id}', '编辑', '操作');
    	 
    	$builder -> data($dataList);
    	$builder -> display();    
    }
    
    
    public function editEnginCondtion($id=0, $engineId) {
    	$conditionModel = D('Device/EngineCondition');
    	if (IS_POST) {
    		// save the data
    		if (!$conditionModel -> create()) {
    			$this->error($conditionModel ->getError());
    			return;
    		} else {
    			if ($id == 0) {
    				// add data
    				if ($conditionModel -> add()){
    					$this -> success('新增成功', U('listEnginCondtions', array('id'=>$engineId)));
    				} else {
    					$this -> error('新增失败');
    				}
    			} else {
    				// update data
    				if ($conditionModel -> save()){
    					$this -> success('更新成功', U('listEnginCondtions', array('id'=>$engineId)));
    				} else {
    					$this -> error('更新失败');
    				}
    			}
    		}
    		 
    	} else {
    		// construct current category
    		$userDeviceData = $this->deviceUserModel->listUserDevice(UID);
    		$userDeviceMap = array_combine(array_column($userDeviceData, 'device_mac'), array_column($userDeviceData, 'device_name'));
    		
    		if ($id != 0) {
    			$data = $conditionModel->find($id);
    		} else {
    			$data = array('engine_id'=>$engineId);
    		}
    		 
    		$builder = new AdminConfigBuilder();
    		$builder -> title(($id==0)?'新增':'修改'.'智能引擎');
    		$builder -> keyId();
    		$builder -> keyHidden('engine_id', '');
    		$builder -> keySelect('device_mac', '相关设备', '', $userDeviceMap);
    		$builder -> keyText('md_code', '元数据编码', '请填写所选设备相关的MD Code,否则该条件会失效');
    		$builder -> keyText('eigen_value', '特征值');
    		$builder -> keyText('sort', '条件序号');
    		
    		$builder -> data($data);
    		$builder -> buttonSubmit(U('editEnginCondtion', array('id'=>0, 'engineId'=>$engineId)))->buttonBack();
    		$builder -> display();
    	}
    }
    
    public function listEnginActions($id) {
    	$actionModel = D('Device/EngineAction');
    	$where = array('engine_id'=>$id);
    	$dataList = $actionModel -> where($where) ->select();
    	 
    	$deviceData = $this->deviceUserModel->listUserDevice(UID);
    	$deviceMap = array_combine(array_column($deviceData, 'device_mac'), array_column($deviceData, 'device_name'));
    	
    	$builder = new AdminListBuilder();
    	$builder -> title('智能引擎执行动作') -> buttonNew(U('editEnginAction', array('id'=>0, 'engineId'=>$id)));
    	$builder -> button('返回',array("href"=>U('listSmartEngins')));
    	 
    	
    	$builder -> keyText('sort', '条件序号');
    	$builder -> keyMap('device_mac', '相关设备', $deviceMap);
    	$builder -> keyText('md_code', '元数据编码');
    	$builder -> keyText('eigen_value', '特征值');
    	
    	$builder ->keyDoActionEdit('editEnginAction?id=###&engineId={$engine_id}', '编辑', '操作');
    	
    	$builder -> data($dataList);
    	$builder -> display();    
    }
    
    
    public function editEnginAction($id=0, $engineId) {
    	$actionModel = D('Device/EngineAction');
    	if (IS_POST) {
    		// save the data
    		if (!$actionModel -> create()) {
    			$this->error($actionModel ->getError());
    			return;
    		} else {
    			if ($id == 0) {
    				// add data
    				if ($actionModel -> add()){
    					$this -> success('新增成功', U('listEnginActions', array('id'=>$engineId)));
    				} else {
    					$this -> error('新增失败');
    				}
    			} else {
    				// update data
    				if ($actionModel -> save()){
    					$this -> success('更新成功', U('listEnginActions', array('id'=>$engineId)));
    				} else {
    					$this -> error('更新失败');
    				}
    			}
    		}
    		 
    	} else {
    		// construct current category
    		$userDeviceData = $this->deviceUserModel->listUserDevice(UID);
    		$userDeviceMap = array_combine(array_column($userDeviceData, 'device_mac'), array_column($userDeviceData, 'device_name'));
    	
    		if ($id != 0) {
    			$data = $actionModel->find($id);
    		} else {
    			$data = array('engine_id'=>$engineId);
    		}
    		 
    		$builder = new AdminConfigBuilder();
    		$builder -> title(($id==0)?'新增':'修改'.'智能引擎');
    		$builder -> keyId();
    		$builder -> keyHidden('engine_id', '');
    		$builder -> keySelect('device_mac', '相关设备', '', $userDeviceMap);
    		$builder -> keyText('md_code', '元数据编码', '请填写所选设备相关的MD Code,否则该条件会失效');
    		$builder -> keyText('eigen_value', '特征值');
    		$builder -> keyText('sort', '条件序号');
    	
    		$builder -> data($data);
    		$builder -> buttonSubmit(U('editEnginAction', array('id'=>0, 'engineId'=>$engineId)))->buttonBack();
    		$builder -> display();
    	}
    }
    
    
    public function dashboard() {
    	$countmap = array();
    	// 所有产品数
    	$allProductCnt = $this->productModel->count();
    	$countmap['all_product']=$allProductCnt;
    	
    	// 有设备接入的产品数
    	$activeProductCnt = $this->deviceModel->countActiveProduct();
    	$countmap['active_product']=$activeProductCnt;
    	
    	$countmap['un_active_product']=$allProductCnt-$activeProductCnt;
    	
    	
    	// 所有设备数
    	$allDeviceCnt = $this->deviceModel->count();
    	$countmap['all_device']=$allDeviceCnt;
    	// 激活设备数
    	$activeCondition = array();
    	$activeCondition['device_status'] = array('IN', array('2', '3'));
    	$allActiveDeviceCnt = $this->deviceModel->where($activeCondition)->count();
    	$countmap['active_device']=$allActiveDeviceCnt;
    	
    	$countmap['un_active_device']=$allDeviceCnt-$allActiveDeviceCnt;
    	
    	$this->assign('count', $countmap);
    	
    	$this->display('Device@Dashboard/dashboard');
    }
    
}
