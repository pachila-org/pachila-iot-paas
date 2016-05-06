
-- 
-- /*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where `title` = '设备管理';
delete from `iot_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);
-- 
-- /*删除相应的后台菜单*/
delete from `iot_menu` where  `url` like 'Device/%';



drop table iot_device_log;
drop table iot_device_user;
drop table iot_device_mac;
drop table iot_device;
drop table iot_device_mac_file;
drop table iot_ota_log;
drop table iot_engine_action;
drop table iot_engine_condition;
