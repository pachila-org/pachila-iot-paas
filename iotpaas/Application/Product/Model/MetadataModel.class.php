<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

namespace Product\Model;
use Think\Model;

/**
 * 
 */
class MetadataModel extends Model {

	const TBL_NAME = 'metadata';
	
	protected $tableName=self::TBL_NAME;

    protected $_validate = array(
        array('md_code','require','编码必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
//     	array('md_code', '/^[a-zA-Z]\w{0,39}$/', '编码输入不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    	array('md_name','require','名称必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
    	array('md_value_type','require','类型必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
    	array('md_scope','require','范围必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证
    );
    
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    	array('status', '1', self::MODEL_BOTH),
    );


}