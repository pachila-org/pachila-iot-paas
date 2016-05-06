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


--
-- 数据库: `110`
--

-- --------------------------------------------------------

--
-- 表的结构 `ocenter_issue`
--


INSERT INTO `iot_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( '授权管理', 0, 55, 'Device/index', 1, '', '', 0);

set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where title = '设备监控';

INSERT INTO `iot_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( 'XXX统计', @tmp_id, 32, 'DeviceReport/index', 0, '', '统计报表', 0);

