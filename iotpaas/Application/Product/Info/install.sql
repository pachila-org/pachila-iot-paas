
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `iot_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,  
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `md_type` tinyint(4) NOT NULL,
  `md_code` varchar(32) NOT NULL,
  `md_name` varchar(32) NOT NULL,
  `md_value_type` varchar(16) NOT NULL,
  `md_scope` varchar(16) NOT NULL,
  `md_description` longtext,
  `md_owner_code` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

/*==============================================================*/
/* Table: iot_connect_module                                    */
/*==============================================================*/
create table iot_connect_module
(
   id                   int(11) not null auto_increment,
   module_type          tinyint not null,
   module_name          varchar(64),
   vendor_name          varchar(64),
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

/*==============================================================*/
/* Table: iot_module_firmware                                   */
/*==============================================================*/
create table iot_module_firmware
(
   id                   int(11) not null auto_increment,
   module_id            int not null,
   firmware_name        varchar(64) not null,
   firmware_version     varchar(32) not null,
   file_ids             varchar(64),
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

alter table iot_module_firmware add constraint FK_firmware_module foreign key (module_id)
      references iot_connect_module (id) on delete restrict on update restrict;


/*==============================================================*/
/* Table: iot_product_type                                */
/*==============================================================*/
CREATE TABLE IF NOT EXISTS iot_product_category (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(64) NOT NULL,
  pid int(11) NOT NULL,
  sort int(11) NOT NULL,
  create_time int(11) NOT NULL,
  update_time int(11) NOT NULL,
  status tinyint(4) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;


/*==============================================================*/
/* Table: iot_product                                           */
/*==============================================================*/
create table iot_product
(
   id                   int(11) not null auto_increment,
   product_category     int not null,
   product_code         varchar(16) NOT NULL,
   product_name         varchar(32) NOT NULL,
   connect_type         tinyint,
   logo_img             varchar(32),
   logo_length          int,
   logo_height          int,
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   creater_id           int,
   owner_code           varchar(32) NOT NULL,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product add constraint FK_FK_proudct_type foreign key (product_category)
      references iot_product_category (id) on delete restrict on update restrict;

/*==============================================================*/
/* Table: iot_product_metadata                                  */
/*==============================================================*/
create table iot_product_metadata
(
   id                   int(11) not null auto_increment,
   product_id           int not null,
   metadata_id          int not null,
   parser_type          tinyint not null,
   parser_attr_1        varchar(128),
   parser_attr_2        varchar(128),
   parser_attr_3        varchar(128),
   ext_attr_1           varchar(128),
   ext_attr_2           varchar(128),
   ext_attr_3           varchar(128),
   status				tinyint(4) NOT NULL,
   create_time			int(11) NOT NULL,
   update_time			int(11) NOT NULL,  
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product_metadata add constraint FK_proudct_metadata_left foreign key (product_id)
      references iot_product (id) on delete restrict on update restrict;

alter table iot_product_metadata add constraint FK_proudct_metadata_right foreign key (metadata_id)
      references iot_metadata (id) on delete restrict on update restrict;

/*==============================================================*/
/* Table: iot_digital_parse_rule                                */
/*==============================================================*/
create table iot_digital_parse_rule
(
   id                   int(11) not null auto_increment,
   product_id           int not null,
   metadata_id          int not null,
   part_no              int not null,
   part_type            varchar(16) not null,
   part_length          int not null,
   part_value           varchar(16),
   status				tinyint(4) NOT NULL,
   create_time			int(11) NOT NULL,
   update_time			int(11) NOT NULL, 
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;


alter table iot_digital_parse_rule add constraint FK_metadata_parse_relation foreign key (metadata_id)
      references iot_metadata (id) on delete restrict on update restrict;

alter table iot_digital_parse_rule add constraint FK_product_parse_rule foreign key (product_id)
      references iot_product (id) on delete restrict on update restrict;

/*==============================================================*/
/* Table: iot_product_firmware                                  */
/*==============================================================*/
create table iot_product_firmware
(
   id                   int(11) not null auto_increment,
   product_id           int not null,
   firmware_id          int not null,
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product_firmware add constraint FK_product_firmware_lef foreign key (product_id)
      references iot_product (id) on delete restrict on update restrict;

alter table iot_product_firmware add constraint FK_product_firware_right foreign key (firmware_id)
      references iot_module_firmware (id) on delete restrict on update restrict;
            
/*==============================================================*/
/* Table: iot_product_module                                    */
/*==============================================================*/
create table iot_product_module
(
   id                   int(11) not null  auto_increment,
   product_id           int not null,
   module_id            int not null,
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product_module add constraint FK_product_module_left foreign key (product_id) references iot_product (id) on delete restrict on update restrict;

alter table iot_product_module add constraint FK_product_module_right foreign key (module_id) references iot_connect_module (id) on delete restrict on update restrict;

/*==============================================================*/
/* Table: iot_product_logconfig                                 */
/*==============================================================*/
create table iot_product_logconfig
(
   id                   int(11) not null  auto_increment,
   metadata_id          int not null,
   product_id           int not null,
   log_required         char(10),
   log_condition_type   tinyint,
   log_condition_value  varchar(32),
   log_format           varchar(64),
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product_logconfig add constraint FK_metadata_logconfig_relation foreign key (metadata_id)
      references iot_metadata (id) on delete restrict on update restrict;

alter table iot_product_logconfig add constraint FK_product_logconfig_relation foreign key (product_id)
      references iot_product (id) on delete restrict on update restrict;
      
/*==============================================================*/
/* Table: iot_product_enum                                 */
/*==============================================================*/
create table iot_product_enum
(
   id                   int(11) not null  auto_increment,
   metadata_id          int not null,
   product_id           int not null,
   enum_key             char(10),
   enum_value           varchar(32),
   display_value        varchar(32),
   create_time          int not null,
   update_time          int not null,
   status               tinyint not null,
   primary key (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

alter table iot_product_enum add constraint FK_metadata_enum_relation foreign key (metadata_id)
      references iot_metadata (id) on delete restrict on update restrict;

alter table iot_product_enum add constraint FK_product_enum_relation foreign key (product_id)
      references iot_product (id) on delete restrict on update restrict;

/*==============================================================*/
/* Initiate data                                                */
/*==============================================================*/

-- 配置元素据类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('MD_TYPE', 3, '元素据类型', 6, '', '元素据分类', 1379228036, 1384418383, 1, '1:功能
2:传感
3:状态
4:错误', 51);

-- 配置元数据的值类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('MD_VALUE_TYPE', 3, '值类型', 6, '', '元素据值类型', 1379228036, 1384418383, 1, '0:N/A
1:数值型
2:字符型
3:枚举型 ', 52);

-- 配置元数据的作用域范围
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('MD_SCOPE', 3, '作用域', 6, '', '元素据作用域范围', 1379228036, 1384418383, 1, '1:公共型
2:私有型 ', 53);

-- 配置元数据的解析方式
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('PARSER_TYPE', 3, '解析类别', 6, '', '针对该元数据的解析方式', 1379228036, 1384418383, 1, '1:16进制数值信号
2:JSON文本
3:流媒体', 61);

-- 配置元数据的数据片段类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('PART_TYPE', 3, '片段类型', 6, '', '解析中定义的数据片段', 1379228036, 1384418383, 1, 'head:帧头
command:命令域
length:内容长
content:内容域
tail:帧尾
chksum:校验值', 62);

-- 配置联网模组的类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('MODULE_TYPE', 3, '模组分类', 6, '', '联网模组的分类', 1379228036, 1384418383, 1, '1:WIFI模组
2:蓝牙模组
3:SIM卡
4:ZIGBEE', 63);

-- 日志记录类型
INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('LOG_CONDITION_TYPE', 3, '日志记录类型', 6, '', '日志记录类型', 1379228036, 1384418383, 1, '1:直接记录
2:变化超过阀值记录', 64);



INSERT INTO `iot_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
( '智能硬件', 0, 60, 'Product/product', 1, '', '', 0, 'wrench', 'product');

set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where title = '智能硬件';

INSERT INTO `iot_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `module`) VALUES
( '所有', @tmp_id, 11, 'Product/metadatas', 0, '', '元数据管理', 0, 'product'),
( '功能类', @tmp_id, 12, '/Admin/Product/metadatas/md_type/1', 0, '', '元数据管理', 0, 'product'),
( '传感类', @tmp_id, 13, '/Admin/Product/metadatas/md_type/2', 0, '', '元数据管理', 0, 'product'),
( '状态类', @tmp_id, 14, '/Admin/Product/metadatas/md_type/3', 0, '', '元数据管理', 0, 'product'),
( '异常类', @tmp_id, 15, '/Admin/Product/metadatas/md_type/4', 0, '', '元数据管理', 0, 'product'),
( '编辑传感类元素据', @tmp_id, 16, '/Admin/Product/editMetadata', 1, '', '元数据管理', 0, 'product'),
( '产品管理', @tmp_id, 20, 'Product/index', 0, '', '产品管理', 0, 'product'),
( '产品添加修改', @tmp_id, 201, 'Product/addProduct', 1, '', '产品管理', 0, 'product'),
( '产品配置列表', @tmp_id, 202, 'Product/listConfig', 1, '', '产品管理', 0, 'product'),
( '产品配置修改', @tmp_id, 203, 'Product/addConfig', 1, '', '产品管理', 0, 'product'),
( '产品相关元数据列表', @tmp_id, 205, 'Product/listMetadata', 1, '', '产品管理', 0, 'product'),
( '产品元数据编辑', @tmp_id, 206, 'Product/editMDParser', 1, '', '产品管理', 0, 'product'),
( '产品元数据解析规则编辑', @tmp_id, 207, 'Product/editMDParserDetail', 1, '', '产品管理', 0, 'product'),
( '产品导航', @tmp_id, 26, 'Product/wizard', 1, '', '产品管理', 0, 'product'),
( '产品分类', @tmp_id, 27, 'Product/categories', 0, '', '产品管理', 0, 'product'),
( '产品分类编辑', @tmp_id, 28, 'Product/addCategories', 1, '', '产品管理', 0, 'product'),
( '联网模组', @tmp_id, 31, 'Product/listConnectModule', 0, '', '固件管理', 0, 'product'),
( '模组添加修改', @tmp_id, 32, 'Product/addConnectModule', 1, '', '固件管理', 0, 'product'),
( '固件维护', @tmp_id, 33, 'Product/listFirmware', 0, '', '固件管理', 0, 'product'),
( '固件添加修改', @tmp_id, 34, 'Product/addFirmware', 1, '', '固件管理', 0, 'product');
