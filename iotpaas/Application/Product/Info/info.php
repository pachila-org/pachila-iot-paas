<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------

return array(
    //模块名
    'name' => 'Product',
    //别名
    'alias' => '智能硬件',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '用于智能硬件产品的定义，管理',
    //开发者
    'developer' => 'xxxx科技有限公司',
    //开发者网站
    'website' => 'http://www.xxxx.com',
    //前台入口，可用U函数
    'entry' => 'Product/index/index',

    'admin_entry' => 'Admin/Product/index',

    'icon' => 'wrench',

    'can_uninstall' => 1
);