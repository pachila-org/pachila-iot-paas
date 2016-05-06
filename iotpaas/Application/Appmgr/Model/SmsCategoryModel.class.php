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
 * CREATE TABLE `iot_sms_category` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
     primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment =100;

 */
/**
 * Class AppModel 
 * @package Appmgr\Model
 * 
 */
class SmsCategoryModel extends Model {
	
	const TBL_NAME = 'sms_category';

    protected $tableName=self::TBL_NAME;
    
    protected $_validate = array(
        array('title','require','消息分类', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );

    public function getTree($id = 0) {
    	/* 如果有当前分类，获取当前分类 */
    	if ($id){
    		$info = $this ->where(array('id'=>$id)) -> find();
    		$id   = $info['id'];
    	}
    
    	/* 获取所有分类 */
    	$map  = array('status' => array('gt', 0));
    	$list = $this ->where($map) -> select();
    	$list = list_to_tree($list,'id', 'pid', '_', $id);
    
    
    	if (isset($info)) {
    		$result = $info;
    		$result['_'] = $list;
    	} else {
    		$result = $list;
    	}
    
    	return $result;
    }
}