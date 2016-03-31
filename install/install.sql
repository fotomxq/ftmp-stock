-- phpMyAdmin SQL Dump
-- version 4.2.7.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2015-06-18 11:15:45
-- 服务器版本： 5.6.20
-- PHP Version: 5.5.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ftmp`
--

-- --------------------------------------------------------

--
-- 表的结构 `sys_config`
--

CREATE TABLE IF NOT EXISTS `sys_config` (
`id` int(10) unsigned NOT NULL COMMENT '索引ID',
  `config_name` varchar(100) COLLATE utf8_bin NOT NULL COMMENT '名称',
  `config_value` longtext COLLATE utf8_bin NOT NULL COMMENT '值'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;

--
-- 转存表中的数据 `sys_config`
--

INSERT INTO `sys_config` (`id`, `config_name`, `config_value`) VALUES
(1, 'WEB-TITLE', 'FTMP'),
(2, 'USER-LIMIT-TIME', '1800'),
(3, 'USER-VCODE-OPEN', '1');

-- --------------------------------------------------------

--
-- 表的结构 `sys_user`
--

CREATE TABLE IF NOT EXISTS `sys_user` (
`id` int(10) unsigned NOT NULL COMMENT '索引ID',
  `user_nicename` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '昵称',
  `user_login` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '登录用户名',
  `user_passwd` varchar(41) COLLATE utf8_bin NOT NULL COMMENT '登录密码',
  `user_date` datetime NOT NULL COMMENT '用户创建时间',
  `user_ip` varchar(39) COLLATE utf8_bin NOT NULL COMMENT '当前登录IP',
  `user_status` tinyint(4) NOT NULL COMMENT '当前登录状态'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `sys_user`
--

INSERT INTO `sys_user` (`id`, `user_nicename`, `user_login`, `user_passwd`, `user_date`, `user_ip`, `user_status`) VALUES
(1, 'admin', 'admin@admin.com', '433a9b7b0e9a8bff8b68a812be4fc7e4d70a3d44', '2014-03-28 17:16:30', '::1', 1);

-- --------------------------------------------------------

--
-- 表的结构 `sys_usermeta`
--

CREATE TABLE IF NOT EXISTS `sys_usermeta` (
`id` bigint(20) unsigned NOT NULL COMMENT '索引ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `meta_name` varchar(50) COLLATE utf8_bin NOT NULL COMMENT '标识',
  `meta_value` text COLLATE utf8_bin COMMENT '值'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `sys_usermeta`
--

INSERT INTO `sys_usermeta` (`id`, `user_id`, `meta_name`, `meta_value`) VALUES
(1, 1, 'POWER', 'ADMIN|CENTER');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sys_config`
--
ALTER TABLE `sys_config`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_user`
--
ALTER TABLE `sys_user`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_usermeta`
--
ALTER TABLE `sys_usermeta`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_config`
--
ALTER TABLE `sys_config`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `sys_user`
--
ALTER TABLE `sys_user`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sys_usermeta`
--
ALTER TABLE `sys_usermeta`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',AUTO_INCREMENT=4;