drop table if exists iot_app;

drop table if exists iot_sms_category;

drop table if exists iot_sms_message;

drop table if exists iot_sms_send_log;

/*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where `title` = 'App管理';
delete from `iot_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);

/*删除APP相关配置*/
delete from `iot_config` where name = 'APP_TYPE' or name = 'BUSINESS_TYPE';