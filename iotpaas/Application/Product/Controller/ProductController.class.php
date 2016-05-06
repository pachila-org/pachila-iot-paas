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



class ProductController extends AdminController
{
	
	protected $categoryModel;
	protected $productModel;
	protected $metadataModel;
	protected $productMetadataModel;
	protected $connectModuleModel;
	protected $moduleFirmwareModel;
	protected $digitalParseRuleModel;
	protected $mdEnumModel;
	protected $logConfigModel;
	
    public function _initialize()
    {
    	$this->categoryModel = D('Product/Category');
    	$this->productModel = D('Product/Product');
    	$this->metadataModel = D('Product/Metadata');
    	$this->productMetadataModel = D('Product/ProductMetadata');
    	$this->connectModuleModel = D('Product/ConnectModule');
    	$this->moduleFirmwareModel = D('Product/ModuleFirmware');
    	$this->digitalParseRuleModel = D('Proudct/DigitalParseRule');
    	$this->mdEnumModel = D('Proudct/ProductEnum');
    	$this->logConfigModel = D('Product/LogConfig');
    	
    	parent::_initialize();
    }
    
    public function metadatas($page=1,$r=10, $md_type=0){
    	// construct data model
    	if($md_type != 0) {
    		$map = array('md_type' => $md_type);
    		$list = $this->metadataModel -> where($map) ->order('md_type asc') -> page($page, $r)->select();
    		$totalCount = $this->metadataModel -> where($map) ->count();
    	} else {
    		$list = $this->metadataModel->page($page, $r) ->order('md_type asc') ->select();
    		$totalCount = $this->metadataModel->count();
    	}
    	 
    	//显示页面
    	$builder = new AdminListBuilder();
    	 
    	// 头部
    	$builder->title('元数据列表') ->buttonNew(U('editMetadata', array('id' => '0', 'md_type'=>$md_type)), "新增" );
    	$builder->buttonDelete(U('setStatus', array('Model'=>'Metadata')));
    	// 列表定义
    
    	$builder-> keyId() -> keyMap("md_type", "类型", C('MD_TYPE'))
    	->keyText('md_code', '编码')
    	->keyText("md_name", "名称")
    	->keyMap("md_value_type", "值类型", C('MD_VALUE_TYPE'))
    	->keyMap("md_scope", "作用域", C('MD_SCOPE'))
    	->keyStatus()
    	->keyUpdateTime()
    	->keyDoActionEdit('editMetadata?id=###');
    	
    	$builder->pagination($totalCount, $r);
    	// 数据及分页
    	$builder->data($list);
    	// 显示
    	$builder->display();
    }
    
    /**
     * 添加、编辑 传感器信息
     *
     * @param
     *        	$id
     */
    public function editMetadata($id = 0, $md_type = 1) {
    	if (IS_POST) {
    		$data = $this->metadataModel->create();
    		if (!$data){
    			$this->error($this->metadataModel ->getError());
    		}
    			
    		if ($id != 0) {
    			if ($this->metadataModel->save($data)) {
    				$this->success('编辑成功。', U('metadatas'));
    			} else {
    				$this->error('编辑失败。');
    			}
    		} else {
    			$result = $this->metadataModel->add($data);
    			if ($result) {
    				$this->success('新增成功。', U('metadatas'));
    			} else {
    				$error_info = $this->metadataModel->getError();
    				$this->error('新增失败:'.$error_info);
    			}
    		}
    			
    	} else {
    		// Construct data
    		if ($id != 0) {
    			$data = M ('Metadata' )->find($id);
    		} else {
    			$data = $this->metadataModel->select();
    			$data['md_type'] = $md_type;
    			$data['status'] = 1;
    			$data['md_scope'] = 2;
    		}
    			
    		// build page
    		$builder = new AdminConfigBuilder ();
    		$builder->title ( "修改元数据" );
    		$builder->keyReadOnly ( "id", "标识", 'ID' )
    		->keySelect ( 'md_type', '类型','不同功能选择不同类型', C('MD_TYPE') )
    		->keyText ( 'md_code', '编码' )
    		->keyText ( 'md_name', '名称' )
    		->keyRadio( 'md_value_type', '值类型', '',  C('MD_VALUE_TYPE'))
    		->keySelect ( 'md_scope', '作用域', '该元数据被使用的范围',  C('MD_SCOPE'))
    		->keyText ( 'md_description', '描述' )
    		->keyText ( 'md_owner_code', '作者' )
    		->keyStatus();
    		$builder->buttonSubmit ( U ( 'editMetadata' ), $id == 0 ? "添加" : "修改" )->buttonBack ();
    		$builder->data ( $data );
    		$builder->display ();
    	}
    }
    
    public function categories(){
    	
    	$builder = new AdminTreeListBuilder();
    	$builder -> setModel('Category');
    	$builder -> title('产品分类管理') -> buttonNew(U('addCategory'));
    	$treeData = $this->categoryModel ->getTree();
    	trace($treeData);
    	$builder -> data($treeData);
    	$builder -> display();
    	 
    }
    
    public function addCategory($id = 0, $pid = 0){
    	if (IS_POST) {
    		// save the data
    		if (!$this->categoryModel -> create()) {
    			$this->error($this->categoryModel ->getError());
    			return;
    		} else {
    			if ($id == 0) {
    				// add data
    				if ($this->categoryModel -> add()){
    					$this -> success('新增成功', U('categories'));
    				} else {
    					$this -> error('新增失败');
    				}
    			} else {
    				// update data
    				if ($this->categoryModel -> save()){
    					$this -> success('更新成功', U('categories'));
    				} else {
    					$this -> error('更新失败');
    				}
    			}
    		}
    		
    	} else {
    		// construct parent list optoin
    		$categories = $this->categoryModel->select();
    		$option = array();
    		foreach ($categories as $category) {
    			$option[$category['id']] = $category['title'];
    		}
    		
    		// construct current category
    		if ($id != 0) {
    			$data = $this->categoryModel->find($id);
    		} else {
    			$data = array('pid'=>$pid);
    		}
    		
    		$builder = new AdminConfigBuilder();
    		$builder -> title(($id==0)?'新增':'修改'.'分类');
    		$builder -> keyId();
    		$builder -> keyText('title', '分类名称');
    		$builder -> keySelect('pid', '父分类','当前分类的父分类', array('0' => '')+$option);
    		if ($id != 0) { 
    			$builder -> keyStatus();
    		}
    		$builder -> data($data);
    		$builder -> buttonSubmit(U('addCategory'))->buttonBack();
    		$builder -> display();
    	}
    
    }

    public function index($page=1,$r=10){
    	$condition = array();
    	$dataList = $this -> productModel -> where($condition) ->select();
    	 
    	$categories = $this->categoryModel->select();
    	$categoryMap = array_combine(array_column($categories, 'id'), array_column($categories, 'title'));
    	trace($categoryMap);
    	 
    	
    	$builder = new AdminListBuilder();
    	$builder -> title('产品管理') -> buttonNew(U('addProduct'));
    	$builder -> ajaxButton(U('sync2redis'), array(), '配置同步至Redis');
    	
    	$builder -> keyMap('product_category', '类别', $categoryMap);
    	$builder -> keyText('product_code', '产品编码');
    	$builder ->key('product_name', '产品名称');
    	$builder -> keyImage('logo_img', '产品图片');
    	
    	$builder -> keyDoActionEdit('addProduct?id=###');
    	$builder -> keyDoAction('listMetadata?id=###', '&nbsp;数据解析配置&nbsp;');
    	$builder -> keyDoAction('listLogConfig?id=###', '&nbsp;数据日志配置&nbsp;');
    	$builder -> keyDoAction('exportAPIDoc?id=###', '&nbsp;导出接口文件&nbsp;');
    	
    	$builder -> data($dataList);
    	$builder -> display();
    }
    
    public function sync2redis() {
    	$ids = I('request.ids');
    	\Think\Log::write('sync2redis request.ids ='. json_encode($ids));
    	if (empty($ids)) {
    		$this->error('请选择要操作的数据');
    	}
    	
    	$condition['product_id'] = array('IN', $ids);
    	$alldatas = $this->productMetadataModel->where($condition)->select();
    	
    	foreach ($alldatas as $item) {
    		$this->_saveMDParse2Redis($item['product_id'], $item['metadata_id']);
    	}
    	$this->success('同步成功');
    }
    
    public function listProduct($page=1,$r=10){
    	$builder = new AdminListBuilder();
    	$builder -> title('产品列表') ;
    	$condition = array();
    	$dataList = $this -> productModel -> where($condition) -> limit(10)->select();
    	
    	$categories = $this->categoryModel->select();
    	$categoryMap = array_combine(array_column($categories, 'id'), array_column($categories, 'title'));
    	
    	$builder -> keyMap('product_category', '类别', $categoryMap);
    	$builder -> keyText('product_code', '产品编码') ->key('product_name', '产品名称');
    	$builder -> keyImage('logo_img', '产品图片');
    	 
    	$builder -> data($dataList);
    	$builder -> display('solist');
    }
    
    public function addProduct($id=0){
    	\Think\Log::write('Enter addProduct');
    	
    	if (IS_POST){
    		$productData = $this->productModel->create();
    		if (!$productData){
    			$this->error($this->productModel ->getError());
    		}
    		
    		$currentUser = D('Member') -> find(UID);
    		\Think\Log::write('current user '.json_encode($currentUser));
    		$productData['owner_code'] = $currentUser['nickname'];
    		
    		if ($id==0) {
    			$result = $this->productModel->add($productData);
    			$id = $result;
    		} else {
    			$result = $this->productModel->save($productData);
    		}
    		if (!$result) {
    			$this->error('保存失败:'.$this->productModel ->getError());
    		}
    		
    		$functionList = I('post.function_list');
    		$functionList = $functionList?$functionList:array(); 
    		
    		$sensorList = I('post.sensor_list');
    		$sensorList = $sensorList?$sensorList:array();
    		
    		$statusList = I('post.status_list');
    		$statusList = $statusList?$statusList:array();
    		$exceptionList = I('post.exception_list');
    		$exceptionList = $exceptionList?$exceptionList:array();
    		
    		$metadataList = array_merge($functionList,$sensorList); 
    		$metadataList = array_merge($metadataList,$statusList);
    		$metadataList = array_merge($metadataList,$exceptionList);
    		\Think\Log::record('---'.$metadataList);
    		foreach ($metadataList as $mditem) {
    			\Think\Log::record($mditem);
    		}
    		
    		$result = $this->productModel->updateProductMetadata($id, $metadataList);
    		if (!$result) {
    			$this->error('保存失败');
    		}
    		
    		$moduleList = I('post.module_list'); 
    		$moduleList = $moduleList?$moduleList:array();
    		\Think\Log::record('---'.$moduleList);
    		foreach ($moduleList as $mditem) {
    			\Think\Log::record($mditem);
    		}
    		$result = $this->productModel -> updateModuleList($id, $moduleList);
    		
    		if ($result) {
    			$this->success('保存成功', U('index'));
    		} else {
    			$this->error('保存失败');
    		}
    		
    	}
    	
    	// init category options
    	$categories = $this->categoryModel->select();
    	$categoryMap = array_combine(array_column($categories, 'id'), array_column($categories, 'title'));
    	trace($categoryMap);
    	
    	// init metadata options
    	$conditionMd['md_type'] ='1';
    	$conditionMd['status'] = '1' ;
    	$functions = $this -> metadataModel -> field('id,md_name') -> where($conditionMd)-> order('md_code asc') -> select();
    	trace($functions);
    	$conditionMd['md_type'] ='2';
    	$sensors = $this -> metadataModel -> field('id,md_name') -> where($conditionMd)-> order('md_code asc') -> select();
    	trace($sensors);    	
    	$conditionMd['md_type'] ='3';
    	$statuses = $this -> metadataModel -> field('id,md_name') -> where($conditionMd)-> order('md_code asc') -> select();
    	trace($statuses);
    	$conditionMd['md_type'] ='4';
    	$exceptions = $this -> metadataModel -> field('id,md_name') -> where($conditionMd)-> order('md_code asc') -> select();
    	trace($exceptions);
    	
    	$module_list = $this->connectModuleModel ->field('id, module_name') ->select();
    	trace($module_list);
    	
    	// build the page
    	// construct original product data
    	if ($id == 0) {
    		$productData = array();
    	} else {
    		$productData = $this->productModel->find($id);
    		$productData['function_list']=$this->productModel->getMetaDataIdList($id, '1');
    		$productData['sensor_list']=$this->productModel->getMetaDataIdList($id, '2');
    		$productData['status_list']=$this->productModel->getMetaDataIdList($id, '3');
    		$productData['exception_list']=$this->productModel->getMetaDataIdList($id, '4');
    		$productData['module_list']=$this->productModel->getModuleIdList($id);
    		trace($productData);
    	}
    	
    	$builder = new AdminConfigBuilder();
    	$builder -> title('新增智能产品');
    	$builder -> keyId();
    	$builder -> keySelect('product_category', '产品分类', '选择一个产品类型', $categoryMap); //
    	$builder -> keyText('product_code', '产品编号');
    	$builder -> keyText('product_name', '产品名称');
    	$builder -> keyReadOnly('owner_code', '拥有者编码');
    	$builder -> keySelect('connect_type', '联网方式', '', C('MODULE_TYPE'));
    	$builder -> keyChosen('module_list', '选用模组', '选择该产品适用的联网模组，可以多选', $module_list);
    	$builder -> keyChosen('function_list', '功能元数据', '选择相关的功能性数据', $functions);
    	$builder -> keyChosen('sensor_list', '传感元数据', '选择相关的传感元素据', $sensors);
    	$builder -> keyChosen('status_list', '状态元数据', '选择相关的状态元素据', $statuses);
    	$builder -> keyChosen('exception_list', '异常元数据', '选择相关的异常元素据', $exceptions);
    	$builder -> keySingleImage('logo_img', '产品图像');
    	
//     	$builder -> group('基本信息', array('id','product_category','product_code', 'product_name','product_model','logo_img'));
//     	$builder -> group('元数据信息', array('function_list','sensor_list','status_list', 'exception_list'));
    	
    	$builder->buttonSubmit(U('addProduct'));
    	$builder->buttonBack();
    	$builder->data($productData);
    	$builder -> display();
    }
    
    public function listMetadata($id){
    	// list related metadatas
    	$list = $this->productModel->getMetatDataList($id);
    	
    	// build list page
    	$builder = new AdminListBuilder();
    	$builder -> title('产品相关的元数据解析');
    	$builder -> button('返回', array('href'=>U('index'))); 	
    	$builder ->keyText('md_name', '元数据名称');
    	$builder ->keyText('md_code', '元数据编码');
    	$builder ->keyMap('md_type', '类型', C('MD_TYPE'));
    	$builder ->keyMap('parser_type', '解析类型', C('PARSER_TYPE'));
//     	$builder ->keyText('parser_attr_1', '解析参数1');
//     	$builder ->keyText('parser_attr_2', '解析参数2');
    	
    	$builder ->keyDoActionModalPopup('editMDParser?id=###', '编辑', '操作', array('data-title'=>'修改解析规则'));
    	$builder ->keyDoActionModalPopup('editMDParserDetail?id=###', '&nbsp;流规则详细&nbsp;', '操作', array('data-title'=>'定义流规则详细'));
    	$builder ->keyDoActionModalPopup('editMDEnumDetail?id=###', '&nbsp;值解析详细&nbsp;', '操作', array('data-title'=>'定义值解析详细'));
    	
    	$builder -> data($list);
    	$builder -> display();
    	
    }
    
    public function editMDParser($id) {
    	if (IS_POST) {
    		$data = $this->productMetadataModel->create();
    		if (!$data){
    			$this->error($this->productMetadataModel ->getError());
    		}
    		if ($id==0) {
    			$result = $this->productMetadataModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->productMetadataModel->save($data);
    		}
    		if (!$result) {
    			$this->error('保存失败:'.$this->productMetadataModel ->getError());
    		}
    		
    		$product_id = $data['product_id'];
    		$metadata_id = $data['metadata_id'];
    		$this->_saveMDParse2Redis($product_id, $metadata_id);
    		
    		$this->success('保存成功');
    		
    	} else {
	    	$pmData = $this->productMetadataModel->find($id);
	    	$mdData = $this->metadataModel->find($pmData['metadata_id']);
	    	$pmData['md_name']=$mdData['md_name'];
	    	$pmData['md_type']=$mdData['md_type'];
	    	$pmData['md_value_type']=$mdData['md_value_type'];
	    	
	    	$builder = new AdminConfigBuilder();
	    		
	    	$builder -> keyReadOnly('md_name', '元数据名称');
	    	$builder -> keySelect('md_type', '元数据分类','修改无效', C('MD_TYPE'));
	    	$builder -> keySelect('md_value_type', '值类型','修改无效', C('MD_VALUE_TYPE'));
	    	$builder -> keySelect('parser_type', '值解析类型', '请选择合适的类型', C('PARSER_TYPE'));
	    	$builder -> keyText('parser_attr_1', '解析参数1', '');
	    	$builder -> keyText('parser_attr_2', '解析参数2', '');
	    	$builder -> keyText('parser_attr_3', '解析参数3', '');
	    	$builder -> keyHidden('id', '') -> keyHidden('metadata_id', '') -> keyHidden('product_id', '');
	    	
	    	$builder -> buttonSubmit(U('editMDParser'));
	    	$builder -> data($pmData);
	    	$builder -> display('admin_config_modal');
    	}
    }
    
    public function editMDParserDetail($id = 0) {
    	
    	if (IS_POST) {
    		
    		$metadata_id = I('POST.metadata_id');
    		$product_id = I('POST.product_id');
    		$rules = I('POST.rule');
    		
    		\Think\Log::write('$metadata_id' . $metadata_id);
    		\Think\Log::write ( '$product_id' . $product_id );
			trace ( $rules );
			$ruleCount = count ( $rules ['part_type'] );
			
			if ($ruleCount > 0) {
				$where['metadata_id']=$metadata_id;
				$where['product_id']=$product_id;
				
				$this->digitalParseRuleModel->where($where)->delete();
				
				for($i = 0; $i < $ruleCount; $i ++) {
					$data ['metadata_id'] = $metadata_id;
					$data ['product_id'] = $product_id;
					$data ['part_no'] = $rules['part_no'][$i];
					$data ['part_type'] = $rules['part_type'][$i];
					$data ['part_length'] = $rules['part_length'][$i];
					$data ['part_value'] = $rules['part_value'][$i];
					
					trace ( $data );
					$data = $this->digitalParseRuleModel->create ( $data );
					trace ( $data );
					if (! $data) {
						$this->error ( $this->digitalParseRuleModel->getError () );
	    			    return;
	    			}
	    			$this->digitalParseRuleModel->add($data);
	    		}
	    		
	    		$this->_saveMDParse2Redis($product_id, $metadata_id);
	    		
// 	    		$builder = new AdminConfigBuilder();
// 	    		$builder -> display();
	    		$this->success('规则修改成功');
			} else {
				$this->error('规则不能为0个');
			}
    		
    	} else {
	    	if ($id == 0) {
	    		$this ->error('ID不能为空');
	    		return;
	    	}   	
	    	
	    	// fetch data
	    	$pmData = $this->productMetadataModel->find($id);
	    	$where['metadata_id'] = $pmData['metadata_id'];
	    	$where['product_id'] = $pmData['product_id'];
	    	$list = $this->digitalParseRuleModel->where($where)->order('part_no asc')->select();
	    	
	    	if (empty($list)) {
	    		$list[0] = array('part_no'=>1, 'part_type'=>'head', 'part_length'=>2, 'part_value'=>'AA');
	    		$list[1] = array('part_no'=>2, 'part_type'=>'command', 'part_length'=>2);
// 	    		$list[2] = array('part_no'=>3, 'part_type'=>'length', 'part_length'=>4);
	    		$list[2] = array('part_no'=>3, 'part_type'=>'content', 'part_length'=>4);
	    		$list[3] = array('part_no'=>4, 'part_type'=>'chksum', 'part_length'=>2);
	    		$list[4] = array('part_no'=>5, 'part_type'=>'tail', 'part_length'=>2, 'part_value'=>'55');
	    	}
	    	
	    	$this->assign('part_type_ops', C('PART_TYPE'));
			$this->assign('list', $list);
			$this->assign('metadata_id', $pmData['metadata_id']);
			$this->assign('product_id', $pmData['product_id']);
	    	$this -> display(T('Product@Product/editmdparserdetail'));
    	}
    }
    
    public function editMDEnumDetail($id = 0) {
    	 
    	if (IS_POST) {    
    		$metadata_id = I('POST.metadata_id');
    		$product_id = I('POST.product_id');
    		$value_function = I('POST.value_function');
    		$enums = I('POST.enums');
    
    		\Think\Log::write('$metadata_id=' . $metadata_id);
    		\Think\Log::write ( '$product_id=' . $product_id );
    		\Think\Log::write ( '$value_function=' . $value_function );
    		$ruleCount = count ( $enums ['enum_key'] );
    		
    		\Think\Log::write ( '$value_function=' . $value_function );
    		
    		if (!empty($value_function)) {
    			$pmData = $this->productMetadataModel->where(array('product_id'=>$product_id, 'metadata_id'=>$metadata_id))->find();
    			$pmData['parser_attr_1']=$value_function;
    			
    			$this->productMetadataModel->save($pmData);
    			\Think\Log::write ( 'pmdata saved =' . json_encode($pmData) );
    		}
    		
    		if ($ruleCount > 0) {
    			$where['metadata_id']=$metadata_id;
    			$where['product_id']=$product_id;
    			$this->mdEnumModel->where($where)->delete();
    			
    			\Think\Log::write ( 'delete old enums');
    			
    			for($i = 0; $i < $ruleCount; $i ++) {
    				$data['metadata_id'] = $metadata_id;
    				$data['product_id'] = $product_id;
    				$data['enum_key'] = $enums['enum_key'][$i];
    				$data['enum_value'] = $enums['enum_value'][$i];
    				$data['display_value'] = $enums['display_value'][$i];
    				
    				\Think\Log::write ( 'before data ='.json_encode($data, true));
    				$data = $this->mdEnumModel->create($data);
    				\Think\Log::write ( 'after data ='.json_encode($data, true));
    				\Think\Log::write ( 'error = '.$this->mdEnumModel->getError ());
    				
    				if (! $data) {
    					$this->error ( $this->mdEnumModel->getError () );
    					return;
    				}
    				$this->mdEnumModel->add($data);
    			}
    	   
    			$this->_saveMDParse2Redis($product_id, $metadata_id);
    		} else {
    			$where['metadata_id']=$metadata_id;
    			$where['product_id']=$product_id;
    			$this->mdEnumModel->where($where)->delete();
    		}

    		$this->_saveMDParse2Redis($product_id, $metadata_id);
    		
    		$this->success('修改成功');
    	} else {
    		if ($id == 0) {
    			$this ->error('ID不能为空');
    			return;
    		}
    
    		// fetch data
    		$pmData = $this->productMetadataModel->find($id);
    		$where['metadata_id'] = $pmData['metadata_id'];
    		$where['product_id'] = $pmData['product_id'];
    		
    		$mdData = $this->metadataModel->find($pmData['metadata_id']);
    		$list = $this->mdEnumModel->where($where)->order('enum_key asc')->select();
    		
    		\Think\Log::write ( 'pmdata  =' . json_encode($pmData) );
    
    		$this->assign('list', $list);
    		$this->assign('md_value_type', $mdData['md_value_type']);
    		$this->assign('value_function', $pmData['parser_attr_1']);
    		$this->assign('metadata_id', $pmData['metadata_id']);
    		$this->assign('product_id', $pmData['product_id']);
    		$this -> display(T('Product@Product/editmdenum'));
    	}
    }
    
    public function listConnectModule() {
    	// fetch data list
    	$data = $this->connectModuleModel->select();
    	
    	// build the list page
    	$builder = new AdminListBuilder();
    	$builder -> title('联网模组管理') ->buttonNew(U('addConnectModule'));
    	
    	$builder->keyText('module_name', '模组型号');
    	$builder->keyMap('module_type', '模组分类', C('MODULE_TYPE'));
    	$builder->keyText('vendor_name', '厂商');
    	
    	$builder->keyDoActionEdit('addConnectModule?id=###');
    	$builder->data($data);
    	$builder->display();
    }
    
    public function addConnectModule($id = 0){
    	if (IS_POST) {
    		// do save data
    		$data = $this->connectModuleModel->create();
    		if (!$data) {
    			$this -> error($this->connectModuleModel->getError());
    			return;
    		}
    		if ($id==0) {
    			$result = $this->connectModuleModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->connectModuleModel->save($data);
    		}
    		if (!$result) {
    			$this->error('保存失败:'.$this->connectModuleModel ->getError());
    		}
    		$this->success('保存成功.', U('listConnectModule'));
    	} else {
    		// do show new or edit page
    		if ($id == 0) {
    			$data['module_type'] = '1';
    		} else {
    			$data = $this->connectModuleModel->find($id);
    		}
    		trace($data);
    		
    		// build page
    		$builder = new AdminConfigBuilder();
    		$builder -> title((($id==0)?'新增':'修改') . '模组型号');
    		$builder -> keyId(); 
    		$builder -> keyText('module_name', '模组型号');
    		$builder -> keySelect('module_type', '模组分类', '必须选择一个类型', C('MODULE_TYPE'));
    		$builder -> keyText('vendor_name', '厂商');
    		
    		$builder ->buttonSubmit(U('addConnectModule')) -> buttonBack();
    		$builder -> data($data);
    		
    		$builder -> display();
    	}
    }
    
    public function listFirmware() {
    	// fetch list data
    	$moduleOpt = $this-> connectModuleModel -> field('id, module_name as value') ->select();
    	trace($moduleOpt);
    	
    	$module_id = I('GET.module_id');
    	trace($module_id);
    	$map['status'] = 1;
    	if (! empty($module_id)) {
    		$map['module_id'] = $module_id;
    	}
    	$firmwareList = $this -> moduleFirmwareModel -> where($map) -> select();
    	
    	// build list page
    	$builder = new AdminListBuilder();
    	$builder->title('固件列表');
    	$builder->buttonNew(U('addFirmware'));
    	$builder->setSelectPostUrl(U('listFirmware'));
    	$builder->select('模组型号：','module_id','select','','','',$moduleOpt);
    	
    	$builder->keyText('firmware_name', '固件名称');
    	$builder->keyText('firmware_version', '固件版本');
    	$builder->keyFile('file_ids', '相关文件');
    	$builder->keyUpdateTime();
    	$builder->keyDoAction('addFirmware?id=###', '编辑');
//     	$builder->keyDoAction('firmwareProduct?id=###', '兼容产品');
    	
    	$builder->data($firmwareList)->display();
    }
    
    public function addFirmware($id = 0){
    	if (IS_POST) {
    		$data = $this->moduleFirmwareModel->create();
    		if (!$data) {
    			$this -> error($this->moduleFirmwareModel->getError());
    			return;
    		}
    		if ($id==0) {
    			$result = $this->moduleFirmwareModel->add($data);
    			$id = $result;
    		} else {
    			$result = $this->moduleFirmwareModel->save($data);
    		}
    		if (!$result) {
    			$this->error('保存失败:'.$this->moduleFirmwareModel ->getError());
    		}
    		
    		$productIds = I('POST.product_ids');
    		$productIds = $productIds?$productIds:array();
    		$result = $this->moduleFirmwareModel->updateFirmwareProducts($id, $productIds);
    		if (!$result) {
    			$this->error('保存失败.');
    		}
    		
    		$this->success('保存成功.', U('listFirmware'));
    	} else {
    		// do show new or edit page
    		if ($id == 0) {
    			$data['module_type'] = '1';
    		} else {
    			$data = $this->moduleFirmwareModel->find($id);
    			$data['product_ids'] = $this->moduleFirmwareModel->getProductIdList($id);
    			trace($data);
    		}
    		$moduleList = $this-> connectModuleModel -> field('id, module_name') ->select();
    		$moduleOpt = array_combine(array_column($moduleList, 'id'), array_column($moduleList, 'module_name'));
    		
    		$productList = $this->productModel ->field('id, product_name')->select();
    		
    		// build page
    		$builder = new AdminConfigBuilder();
    		$builder -> title((($id==0)?'新增':'修改') . '固件版本');
    		$builder -> keyId(); 
    		$builder -> keySelect('module_id', '模组型号', '必须选择一个类型', $moduleOpt);
    		$builder -> keyText('firmware_name', '固件名称');
    		$builder -> keyText('firmware_version', '固件版本');
    		$builder -> keyMultiFile('file_ids', '固件文件', '可以包含多个文件');
    		$builder -> keyChosen('product_ids', '兼容的产品', '', $productList);
    		$builder -> keyDataSelect('product_ids_new', '兼容的产品', '这个字段目前还在开发阶段', 'listProduct');
    		
    		$builder ->buttonSubmit(U('addFirmware')) -> buttonBack();
    		$builder -> data($data);
    		
    		$builder -> display();
    	}
    }
    
    /**
     * @param unknown $id product id 
     */
    public function listLogConfig($id) {
    	// fetch config by product id
    	$data = $this->logConfigModel->listAllConfigs($id);
    	
    	// build config list
    	$builder = new AdminListBuilder();
    	$builder->title('产品相关的日志配置列表')->button('返回', array('href'=>U('index')));
    	$builder->keyMap('md_type', '元数据类型', C('MD_TYPE'))->keyText('md_name', '元数据名称');
    	$builder->keyYesNo('log_required', '是否记录日志')->keyMap('log_condition_type', '记录类型', C('LOG_CONDITION_TYPE'));
    	$builder->keyText('log_condition_value', '记录值', 20);
    	$builder->keyDoActionEdit('addLogConfig?id=###&metadata_id={$metadata_id}&product_id={$product_id}');
    	$builder->data($data)->display();
    }
    
    /**
     * @param number $id LogConfig model id
     */
    public function addLogConfig($id=0, $metadata_id, $product_id) {
    	if (IS_POST) {
    		\Think\Log::write('post id='.$id.' metadata_id='.$metadata_id);
    		$data = $this->logConfigModel->create();
    		\Think\Log::write('create data end.');
    		if (!$data){
    			\Think\Log::write('err'. $this->logConfigModel ->getError());
    			$this->error($this->logConfigModel ->getError());
    		}
    		if ($id != 0) {
    			if ($this->logConfigModel->save($data)) {
    				$this->_saveMDParse2Redis($product_id, $metadata_id);
    				$this->success('编辑成功。', U('listLogConfig', array('id'=>$product_id)));
    			} else {
    				$this->error('编辑失败。');
    			}
    		} else {
    			\Think\Log::write('err');
    			$result = $this->logConfigModel->add($data);
    			if ($result) {
    				
    				$this->_saveMDParse2Redis($product_id, $metadata_id);
    				$this->success('新增成功。'.$result, U('listLogConfig', array('id'=>$product_id)));
    			} else {
    				$error_info = $this->logConfigModel->getError();
    				$this->error('新增失败:'.$error_info);
    			}
    		}
    		
    	} else {
	    	// fetch config detail data
	    	if ($id != 0) {
	    		$data = $this->logConfigModel->find($id);
	    	} else {
	    		$data['product_id'] = $product_id;
	    		$data['metadata_id'] = $metadata_id;
	    		$data['log_required'] = 1;
	    	}
	    	$metadata = $this->metadataModel->find($metadata_id);
	    	$data['md_name'] = $metadata['md_name'];
	    	
	    	// build config detail page
	    	$builder = new AdminConfigBuilder();
	    	$builder->title('日志配置设定');
	    	$builder->keyId() ->keyLabel('md_name', '元数据名称') ->keyHidden('product_id')->keyHidden('metadata_id');
	    	$builder->keyBool('log_required', '日志开关','该元数据是否需要日志');
	    	$builder->keySelect('log_condition_type', '记录日志的条件类型','', C('LOG_CONDITION_TYPE'));
	    	$builder->keyText('log_condition_value', '记录日志的条件');
	    	$builder->keyText('log_format', '日志格式','输出型日志格式');
	    	$builder->buttonSubmit(U('addLogConfig'))->buttonBack();
	    	$builder->data($data);
	    	$builder->display();
    	}
    }
    
    public function exportAPIDoc($id) {
    	$product = $this->productModel->find($id);
    	$mdList = $this->productModel->getMetatDataList($id);
    	$title = array('编号', '类型', '元数据编码', '元数据名称',  '值详细描述');
    	$data = array();
    	
    	$md_type_conf = C('md_type');
//     	$md_value_type_conf = C('md_value_type');
    	
    	$index = 0;
    	foreach ($mdList as $item) {
    		$index = $index + 1;
    		$tempData = array();
    		$tempData['no']=$index;
    		$tempData['md_code']=$item['md_code'];
    		$tempData['md_name']=$item['md_name'];
    		$tempData['md_type']=$md_type_conf[$item['md_type']];
//     		$tempData['md_value_type']=$md_value_type_conf[$item['md_value_type']];
    		switch ($item['md_value_type']) {
    			case '0' : // N/A
    				$tempData['value_memo']='N/A';
					break;
				case '1' : // 数值型
					$tempData['value_memo']='数值';
					break;
				case '2': //  字符型
					$tempData['value_memo']='字符串';
					break;
				case '3': //  枚举型
					$enumData = $this->mdEnumModel->where(array('product_id'=>$id, 'metadata_id'=>$item['metadata_id']))->select();
					$memo = '';
					foreach ($enumData as $enumItem) {
						$memo = $memo.$enumItem['enum_value'].':'.$enumItem['display_value'];
						$memo = $memo.'; ';
					}
					$tempData['value_memo']=$memo;
					break;
				default:
					break;
    		}
    		array_push($data, $tempData);
    	}
    	
    	$this->exportexcel($data, $title, $product['product_code']);
    }
    /**
     * 导出数据为excel表格
     *@param $data    一个二维数组,结构如同从数据库查出来的数组
     *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
     *@param $filename 下载的文件名
     *@examlpe
     $stu = M ('User');
     $arr = $stu -> select();
     exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
     */
    function exportexcel($data=array(),$title=array(),$filename='report'){
    	header("Content-type:application/octet-stream");
    	header("Accept-Ranges:bytes");
    	header("Content-type:application/vnd.ms-excel;");
    	header("Content-Disposition:attachment;filename=".$filename.".xls");
    	header("Pragma: no-cache");
    	header("Expires: 0");
    	//导出xls 开始
//     	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    	if (!empty($title)){
    		foreach ($title as $k => $v) {
    			$title[$k]=iconv("UTF-8", "GB2312",$v);
    		}
    		$title= implode("\t", $title);
    		echo "$title\n";
    	}
    	if (!empty($data)){
    		foreach($data as $key=>$val){
    			foreach ($val as $ck => $cv) {
    				$data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
    			}
    			$data[$key]=implode("\t", $data[$key]);
    
    		}
    		echo implode("\n",$data);
    	}
    }
    
    private function  _saveMDParse2Redis($product_id, $metadata_id) {
    	\Think\Log::write('enter save 2 redis ');
    	$redis = getRedis();
    	try {
	    	$productData = $this->productModel->find($product_id);
	    	$metadataData = $this->metadataModel->find($metadata_id);
	    	$whereMap = array('product_id'=>$product_id, 'metadata_id'=>$metadata_id);
	    	$pmData = $this->productMetadataModel->where($whereMap)->find();
	    	$paraseDetails = $this->digitalParseRuleModel->where($whereMap)->order('part_no asc')->select();
	    	$enumDetails = $this->mdEnumModel->where($whereMap)->order('enum_key asc')->select();
	    	$logConfig = $this->logConfigModel->where($whereMap)->find();
	    	
	    	// refine json value
	    	if (!empty($pmData['parser_attr_1'])) {
	    		$pmData['parser_attr_1'] = urlencode ($pmData['parser_attr_1']);
	    	}
	    	
	    	$redisKey = $productData['owner_code'].':'.$productData['product_code'];
	    	$redisField = $metadataData['md_code'];
	    	$redisValue = array();
	    	$redisValue['md_info']=$metadataData;
	    	$redisValue['parse_info']=$pmData;
	    	$redisValue['parse_details']=$paraseDetails;
	    	$redisValue['enum_details']=$enumDetails;
	    	$redisValue['logconfig_info']=$logConfig;
	    	
	    	$redis->hset($redisKey, $redisField, $redisValue);
	    	
    	} finally {
    		releaseRedis($redis);
    	}
    	
    }
   
}