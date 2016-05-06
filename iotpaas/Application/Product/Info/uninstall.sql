drop table if exists iot_product_firmware;
drop table if exists iot_module_firmware;
drop table if exists iot_product_module;
drop table if exists iot_connect_module;

drop table if exists iot_digital_parse_rule;
drop table if exists iot_product_metadata;
drop table if exists iot_product_logconfig;
drop table if exists iot_product_enum;

drop table if exists iot_product; 
drop table if exists iot_product_category;


delete from iot_config where name='PARSER_TYPE';
delete from iot_config where name='PART_TYPE';
delete from iot_config where name='MODULE_TYPE'; 

/*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where `title` = '智能硬件';
delete from `iot_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);
