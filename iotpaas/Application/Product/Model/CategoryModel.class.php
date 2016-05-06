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
 * CREATE TABLE IF NOT EXISTS iot_product_category (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(40) NOT NULL,
  pid int(11) NOT NULL,
  sort int(11) NOT NULL,
  create_time int(11) NOT NULL,
  update_time int(11) NOT NULL,
  status tinyint(4) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;
 * 
 */
/**
 * Class ProductModel
 * @package Product\Model
 * 
 * 
 *
 */
class CategoryModel extends Model {

	const TBL_NAME = 'product_category';
	
	protected $tableName=self::TBL_NAME;
	

	protected $_validate = array(
			array('title','require','分类名称必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH), //默认情况下用正则进行验证、
			//     	array('md_code', '/^[a-zA-Z]\w{0,39}$/', '编码输入不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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