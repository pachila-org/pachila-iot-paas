
/*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where `title` = '设备监控';
delete from `iot_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);

/*删除相应的后台菜单*/
delete from `ocenter_menu` where  `url` like 'Device/%';
/*删除相应的权限节点*/
/* delete from `ocenter_auth_rule` where  `module` = 'Device';  */