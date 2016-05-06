
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `opencenter`
--

-- --------------------------------------------------------

--
-- 表的结构 `iot_app`
--

CREATE TABLE `iot_app` (
  `id` int(11) NOT NULL  auto_increment,
  `app_type` varchar(64) NOT NULL,
  `business_type` varchar(64) NOT NULL,
  `app_name` varchar(64) NOT NULL,
  `app_version` varchar(32) NOT NULL,
  `app_update_comment` varchar(200) NOT NULL,
  `file_num` int(11) DEFAULT NULL,
  `file_name` varchar(64) DEFAULT NULL,
  `file_path` varchar(256) DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE `iot_sms_category` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
     primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment =100;


CREATE TABLE `iot_sms_message` (
  `id` int(11) NOT NULL auto_increment,
  `sms_category` int(11) NOT NULL,
  `sms_title` varchar(64) NOT NULL,
  `sms_content` varchar(200) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
       primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment =1;


CREATE TABLE `iot_sms_send_log` (
  `id` int(11) NOT NULL  auto_increment,
  `sms_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `read_status` tinyint(4) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
       primary key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 auto_increment=1;


/*==============================================================*/
/* Initiate data                                                */
/*==============================================================*/

INSERT INTO `iot_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `icon`) VALUES
( 'App管理', 0, 60, 'Appmgr/Appmgr', 1, '', '', 0, 'wrench');

set @tmp_id=0;
select @tmp_id:= id from `iot_menu` where title = 'App管理';

INSERT INTO `iot_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( 'App管理', @tmp_id, 2, 'Appmgr/index', 0, '', 'App管理', 0),
( '新增APP', @tmp_id, 21, 'Appmgr/addAPP', 1, '', 'App管理', 0),
( 'listAPP', @tmp_id, 22, 'Appmgr/listAPP', 1, '', 'App管理', 0),
( 'removeAPP', @tmp_id, 23, 'Appmgr/removeAPP', 1, '', 'App管理', 0),
( '消息管理', @tmp_id, 3, 'Appmgr/listSmsMessage', 0, '', '消息管理', 0),
( 'listSmsMessage', @tmp_id, 31, 'Appmgr/listSmsMessage', 1, '', '消息管理', 0),
( '添加消息', @tmp_id, 32, 'Appmgr/addSmsMessage', 1, '', '消息管理', 0);

-- INSERT INTO `iot_auth_rule` ( `module`, `type`, `name`, `title`, `status`, `condition`) VALUES
-- ( 'Issue', 1, 'addIssueContent', '专辑投稿权限', 1, ''),
-- ( 'Issue', 1, 'editIssueContent', '编辑专辑内容（管理）', 1, '');

INSERT INTO `iot_sms_category` (`id`, `title`, `pid`, `sort`, `create_time`, `update_time`, `status`) VALUES
(102, 'danger', 3, 3, 20151223, 20151223, 1),
(101, 'warning', 2, 2, 20151223, 20151223, 1),
(100, 'info', 1, 1, 20151223, 20151223, 1);


INSERT INTO `iot_config` (`name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`) VALUES
( 'APP_TYPE', 3, 'app_type', 0, '', '', 1452050455, 1452050455, 1, 'Android:Android\r\nIos:Ios', 0),
( 'BUSINESS_TYPE', 3, 'business_type', 0, '', '', 1452050491, 1452050727, 1, 'SmartAppliance:SmartAppliance\r\nChargingPiles:ChargingPiles\r\nBracelet:Bracelet', 0);