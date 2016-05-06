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
    'name' => 'OpenAPI',
    //别名
    'alias' => '开放接口',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '提供智能设备PaaS相关的开放接口，并提供接口的管理',
    //开发者
    'developer' => '帕启拉科技有限公司',
    //开发者网站
    'website' => 'http://www.pachila.cn',
    //前台入口，可用U函数 : 展现接口API的Doc
    'entry' => 'index/openapi',

    'admin_entry' => 'OpenApi/apiList',

    'icon' => 'book',

    'can_uninstall' => 1
);