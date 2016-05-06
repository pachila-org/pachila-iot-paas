<?php

return array(
    //模块名
    'name' => 'Security',
    //别名
    'alias' => '授权管理',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '对物联网的设备，用户，API做授权管理及配置',
    //开发者
    'developer' => 'xxxx科技有限公司',
    //开发者网站
    'website' => 'http://www.xxxx.com',
    //前台入口，可用U函数
    'entry' => '',

    'admin_entry' => 'Admin/Security/index',

    'icon' => 'mobile',

    'can_uninstall' => 1
);