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
    'name' => 'Appmgr',
    //别名
    'alias' => 'App管理',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '对App应用的版本管理，消息推送管理等',
    //开发者
    'developer' => 'xxxx科技有限公司',
    //开发者网站
    'website' => 'http://www.xxxx.com',
    //前台入口，可用U函数
    'entry' => '',

    'admin_entry' => 'Admin/Appmgr/index',

    'icon' => 'wrench',

    'can_uninstall' => 1
);