-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 11 月 18 日 13:30
-- 服务器版本: 5.5.38
-- PHP 版本: 5.3.28

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



-- --------------------------------------------------------

INSERT INTO iot_config(name, type, title, `group`, extra, remark, create_time, update_time, status, `value`, sort)
VALUES('API_ERROR_MSG', 3, '值类型', 6, '', '接口返回错误', 1379228036, 1384418383, 1, 'PASSWORD_ERR_ENCRIPT:密码加密错误,
TOKEN_EXPIRED:令牌已经失效,
SYSTEM_ERROR:系统错误', 52);


set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where title = '安全';

INSERT INTO `iot_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
( '接口管理', tmp_id, 60, 'OpenAPI/apiList', 0, '', '接口安全', 0, 'book', 'OpenAPI');
、
