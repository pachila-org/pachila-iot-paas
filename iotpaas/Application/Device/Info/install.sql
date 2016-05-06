
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- create 表的结构 `iot_device_mac`
--
--
CREATE TABLE IF NOT EXISTS iot_device_mac
(  
   id                   int NOT NULL AUTO_INCREMENT,
   device_sn            varchar(32) not null,
   device_mac           varchar(32) not null,
   product_id			int not null,
   device_wifi_mod      char(10),
   device_produce_batch char(10),
   register_flg         char(10),
   update_userid        char(10),
   update_timestamp     char(10),
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;
--
-- create 表的结构 `iot_device`
--
--
CREATE TABLE IF NOT EXISTS iot_device
(
   id                       int NOT NULL AUTO_INCREMENT,
   product_id           	int,
   device_sn            	varchar(32),
   device_mac           	varchar(32),
   device_name           	varchar(32),
   device_reg_uid       	int,
   device_reg_addr      	text,
   device_reg_flg       	int,
   device_addr          	text,
   device_ip_addr       	text,
   device_status 			tinyint not null,
   online_status 			tinyint not null,
   device_firmware_ver		varchar(32),
   device_firmware_updatetime timestamp,
   active_time              timestamp,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;
--
-- create 表的结构 `iot_device_user`
--
--
CREATE TABLE IF NOT EXISTS iot_device_user
(
   id                   int not null AUTO_INCREMENT,
   device_id            int not null,
   person_id            int not null,
   auth_level           char(10),
   relation_type        tinyint not null,
   reg_time             char(10),
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- drop table if exists iot_device_log;

create table if not exists iot_device_log
(
   id            int not null AUTO_INCREMENT,
   device_id            int not null,
   md_code       varchar(32),
   md_type		 int,
   log_value            varchar(30),
   log_display_txt      varchar(30),
   user_id              int,
   update_date            timestamp,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

create table  if not exists iot_device_mac_file
(
   id                   int not null AUTO_INCREMENT,
   path                 varchar(255),
   update_date          timestamp,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

create table  if not exists iot_ota_log 
(
   id            int not null AUTO_INCREMENT,
   device_id            int not null,
   ota_status           tinyint not null,
   request_uid          int,
   start_time           timestamp, 
   end_time             timestamp, 
   update_time          timestamp,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

create table if not exists iot_smart_engine 
(
   id            int not null AUTO_INCREMENT,
   engine_type          tinyint not null,
   engine_name          varchar(32),
   owner_uid            int,
   engine_memo          varchar(256),
   create_time          timestamp,
   update_time          timestamp,
   primary key (id)
	
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

create table if not exists iot_engine_condition 
(
   id                   int not null AUTO_INCREMENT,
   engine_id            int not null,
   device_mac           varchar(32),
   md_code              varchar(32),
   eigen_value          varchar(256),
   sort					int,
   create_time          timestamp,
   update_time          timestamp,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

create table if not exists iot_engine_action 
(
   id                   int not null AUTO_INCREMENT,
   engine_id            int not null,
   device_mac           varchar(32),
   md_code              varchar(32),
   eigen_value          varchar(256),
   sort					int,
   create_time          timestamp,
   update_time          timestamp,
   primary key (id)
	
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;


-- 配置设备状态
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('DEVICE_STATUS', 3, '设备状态', 6, '', '设备的生命状态', 1379228036, 1384418383, 1, '1:初始化
2:激活
3:绑定', 61);

INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('ONLINE_STATUS', 3, '在线状态', 6, '', '设备的在线状态', 1379228036, 1384418383, 1, '0:不在线
1:在线', 62);

-- 配置用户设备的关系
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('DEVICE_USRE_TYPE', 3, '用户设备关系', 6, '', '用户相对设备的权限类型', 1379228036, 1384418383, 1, '1:设备管理者
2:设备所有者
3:设备使用者
4:没有关系', 63);

-- 配置OTA状态
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('OTA_STATUS', 3, 'OTA状态', 6, '', 'OTA的升级状态', 1379228036, 1384418383, 1, '1:开始下载
2:设备端开始下载
3:设备端下载完成
4:设备端升级完成', 64);

-- 智能引擎类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('ENGINE_TYPE', 3, '智能引擎类型', 6, '', '智能引擎类型', 1379228036, 1384418383, 1, '1:情景模式
2:设备互联', 65);

-- Menu

INSERT INTO `iot_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`,  `module`) VALUES
( '设备管理', 0, 55, 'Device/index', 1, '', '', 0,'device');
set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where title = '设备管理';
INSERT INTO `iot_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`,  `module`) VALUES
( '设备主页', @tmp_id, 0, 'Device/dashboard', 0, '', '设备管理', 0, 'device'),
( '设备一览', @tmp_id, 1, 'Device/index', 0, '', '设备管理', 0, 'device'),
( 'MAC地址维护', @tmp_id, 1, 'Device/listDeviceMac', 0, '', '设备管理', 0, 'device'),
( 'MAC地址添加', @tmp_id, 3, 'Device/addDeviceMac', 1, '', '设备管理', 0, 'device'),
( '设备控制', @tmp_id, 4, 'Device/controlDevice', 1, '', '设备管理', 0, 'device'),
( '设备用户编辑', @tmp_id, 6, 'Device/editDeviceUser', 1, '', '设备管理', 0, 'device'),
( '设备日志', @tmp_id, 7, 'Device/listDeviceLog', 0, '', '设备管理', 0, 'device'),
( 'MAC文件上传', @tmp_id, 9, 'Device/uploadCSVfile', 1, '', '设备管理', 0, 'device'),
( '设备用户', @tmp_id, 22, 'Device/listDeviceUser', 1, '', '设备授权', 0, 'device'),
( '设备智能', @tmp_id, 30, 'Device/listSmartEngins', 0, '', '设备管理', 0, 'device'),
( '编辑智能', @tmp_id, 31, 'Device/editSmartEngins', 1, '', '设备管理', 0, 'device'),
( '引擎条件', @tmp_id, 32, 'Device/listEnginCondtions', 1, '', '设备管理', 0, 'device'),
( '编辑引擎条件', @tmp_id, 33, 'Device/editEnginCondtion', 1, '', '设备管理', 0, 'device'),
( '引擎动作', @tmp_id, 34, 'Device/listEnginActions', 1, '', '设备管理', 0, 'device'),
( '编辑引擎动作', @tmp_id, 35, 'Device/editEnginAction', 1, '', '设备管理', 0, 'device');

