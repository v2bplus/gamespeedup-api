CREATE DATABASE IF NOT EXISTS `game` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `game`;
CREATE TABLE IF NOT EXISTS `gs_admin_user` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(128) DEFAULT NULL COMMENT '登陆用户名',
  `real_name` varchar(255) DEFAULT NULL COMMENT '真实名字',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '管理员邮箱地址',
  `password` varchar(255) DEFAULT NULL COMMENT '管理员用户密码',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `last_login_time` int(11) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0：停用 1：可用',
  `remark` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`user_name`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COMMENT='管理员用户表';

CREATE TABLE IF NOT EXISTS `gs_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `nickname` varchar(64) NULL DEFAULT '' COMMENT '用户昵称',
  `mobile` varchar(20) NULL DEFAULT '' COMMENT '用户手机号',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '用户邮箱地址',
  `uuid` varchar(64) NOT NULL DEFAULT '' COMMENT '用户UUID',
  `plan_id` int(11) NOT NULL DEFAULT '0' COMMENT '当前使用的订阅ID',
  `invite_user_id` int(11) DEFAULT NULL COMMENT '邀请的用户id',
  `group_id` int(11) unsigned DEFAULT 1 COMMENT '用户组id',
  `php_password` varchar(255) NOT NULL DEFAULT '' COMMENT '原始密码的php密码值',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `real_status` int(11) NOT NULL DEFAULT '0' COMMENT '实名认证状态 0-未认证 1-已认证',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0：停用 1：可用',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

CREATE TABLE IF NOT EXISTS `gs_user_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NULL DEFAULT '' COMMENT '名称',
  `attribute` text NOT NULL COMMENT '用户组权益',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户权限组';

CREATE TABLE IF NOT EXISTS `gs_user_vip` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `time_num` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '次数',
  `status` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '-1:不是VIP 0：过期 1：正常',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `start_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户VIP表';

CREATE TABLE IF NOT EXISTS `gs_vip_plan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `plan_name`varchar(64) NOT NULL COMMENT '套餐名称',
  `money` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '支付金额(分)',
  `day_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '时长(天)',
  `gift_day_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '赠送时长(天)',
  `content` text,
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序(数字越大越靠前)',
  `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0: 不显示 1: 显示',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `plan_name` (`plan_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员套餐表';

CREATE TABLE IF NOT EXISTS `gs_user_real` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `real_name` varchar(64) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `id_card_number` varchar(64) NOT NULL DEFAULT '' COMMENT '身份证号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '审核状态 0-审核中 1-审核通过 2-审核失败',
  `content` varchar(255) DEFAULT NULL,
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `audit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实名认证审核表';

CREATE TABLE IF NOT EXISTS `gs_region` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '节点的区域名字',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序(数字越大越靠前)',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注名字',
  -- `is_free` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='区域列表';

CREATE TABLE IF NOT EXISTS `gs_game` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏名字',
  `alias` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏别名',
  `type` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游戏客户端类型 1:pc 2:android: 3:ios 4:ps 5:待定',
  `logo_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'logo的url地址',
  `cover_img_url` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏封面图片url',
  `rule_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '规则id(用途待定)',
  `region_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '区域服id列表',
  `status` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '状态(1启用 0禁用)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户端游戏列表';

CREATE TABLE IF NOT EXISTS `gs_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '节点名称',
  `region_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '节点的区域id',
  `region_name` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '节点的区域名字(冗余字段)',
  `protocol` varchar(255) NOT NULL DEFAULT '' COMMENT '节点协议类型',
  `host_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '节点服务器地址',
  `host_port` varchar(255) NOT NULL DEFAULT '' COMMENT '节点服务器端口',
  `allow_insecure` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许不安全',
  `capacity_limit` int(11) DEFAULT '-1' COMMENT '容纳最大用户量（-1 不限制）',
  `json_values` text NOT NULL COMMENT '更多设置内容(json格式)',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序(数字越大越靠前)',
  `status` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '状态(1启用 0禁用)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `region_id` (`region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='服务节点列表';

CREATE TABLE IF NOT EXISTS `gs_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `order_no` varchar(36) NOT NULL DEFAULT '' COMMENT '订单号',
  `order_type` varchar(36) NOT NULL DEFAULT '' COMMENT '订单业务类型',
  `total_amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单支付总金额(分)',
  `invite_user_id` int(11) DEFAULT NULL COMMENT '邀请的用户id',
  `commission_balance` int(11) NOT NULL DEFAULT '0' COMMENT '佣金',
  `pay_type` tinyint(4) unsigned NOT NULL COMMENT '支付类型',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:待支付 1:已完成 2:已取消',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `pay_time` int(11) DEFAULT '0' COMMENT '支付时间',
  PRIMARY KEY (`id`),
  KEY `order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单列表';

CREATE TABLE IF NOT EXISTS `gs_payment_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_no` varchar(255) NOT NULL COMMENT '订单号',
  `pay_amount` decimal(10,2) unsigned NOT NULL COMMENT '支付金额',
  `currency_code` varchar(16) DEFAULT NULL COMMENT '货币代码',
  `pay_type` tinyint(4) unsigned NOT NULL COMMENT '支付类型',
  `trade_number` varchar(255) NOT NULL COMMENT '第三方订单号',
  `status` tinyint(4) NOT NULL COMMENT '0 未支付 1 已支付 2 退款',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `return_msg` varchar(1000) DEFAULT NULL COMMENT '支付返回信息',
  `remark` varchar(255) DEFAULT NULL,
  `field1` varchar(255) DEFAULT NULL,
  `field2` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trade_number` (`trade_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付信息列表';

INSERT INTO `gs_admin_user` (`id`, `user_name`, `real_name`, `password`,`create_time`,`update_time`,`last_login_time`,`status`) VALUES (1, 'admin', '默认管理员', '$2y$10$FJAKYIXnRhXPLFbhBjLNI.EB0ZMwlyWeCZAOM56FcMyriez.DT9sS', 0, 0,0, 1);
INSERT INTO `gs_user_group` (`id`, `name`, `remark`, `create_time`, `update_time`, `attribute`) VALUES (1, '默认', '默认组', 0, 0, '{\"commission_rate\":5}');
INSERT INTO `gs_region` (`id`, `name`, `sort`,`remark`, `create_time`) VALUES (1, '亚洲',  11, '备注1',0);
INSERT INTO `gs_region` (`id`, `name`, `sort`,`remark`, `create_time`) VALUES (2, '欧洲',  10, '备注2',0);
INSERT INTO `gs_region` (`id`, `name`, `sort`,`remark`, `create_time`) VALUES (3, '美洲',  9, '备注3',0);

-- 第二阶段
-- 第二阶段
-- 第二阶段

CREATE TABLE IF NOT EXISTS `gs_user_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `total` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '当前账户余额，单位为分',
  `freeze` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '当前冻结余额，单位为分',
  `commission_total` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '累计佣金 单位为分',
  `charged_total` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '累计充值 单位为分',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户账户表';

CREATE TABLE IF NOT EXISTS `gs_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_count` int(11) NOT NULL COMMENT '订单数量',
  `order_total` int(11) NOT NULL COMMENT '订单合计',
  `commission_count` int(11) NOT NULL,
  `commission_total` int(11) NOT NULL COMMENT '佣金合计',
  `register_count` int(11) NOT NULL COMMENT '注册数量',
  `invite_count` int(11) NOT NULL COMMENT '邀请数量',
  `paid_count` int(11) NOT NULL,
  `paid_total` int(11) NOT NULL,
  `date` date NOT NULL COMMENT '日期',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单统计';

CREATE TABLE IF NOT EXISTS `gs_user_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL COMMENT '游戏id',
  `node_id` int(11) NOT NULL COMMENT '服务器节点id',
  `upload` bigint(20) NOT NULL,
  `download` bigint(20) NOT NULL,
  `date` date NOT NULL COMMENT '统计日期',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户数据统计';

CREATE TABLE IF NOT EXISTS `gs_server_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL COMMENT '服务器节点id',
  `node_type` char(11) NOT NULL COMMENT '节点类型',
  `upload` bigint(20) NOT NULL,
  `download` bigint(20) NOT NULL,
  `date` date NOT NULL COMMENT '统计日期',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点数据统计';

CREATE TABLE IF NOT EXISTS `gs_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `payment` varchar(16) NOT NULL COMMENT '支付对象(类型)',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `icon` varchar(255) DEFAULT NULL,
  `config` text NOT NULL,
  `notify_domain` varchar(128) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序(数字越大越靠前)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付配置';

CREATE TABLE IF NOT EXISTS `gs_user_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `oauth_type` varchar(255) NOT NULL DEFAULT '' COMMENT '第三方登陆类型()',
  `oauth_id` varchar(125) NOT NULL DEFAULT '' COMMENT '第三方用户唯一标识 (user_id openid)',
  `unionid` varchar(125) NOT NULL DEFAULT '' COMMENT '微信unionID',
  `is_delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `oauth_type` (`oauth_type`),
  KEY `oauth_type_2` (`oauth_type`,`oauth_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='第三方用户信息表';

CREATE TABLE IF NOT EXISTS `gs_user_reg_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `device_type` tinyint(4) DEFAULT NULL COMMENT '1 pc 2 iphone 3 android 4 h5',
  `account_type` tinyint(4) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL COMMENT '设备唯一标识符',
  `ua` varchar(255) DEFAULT NULL COMMENT 'USER_AGENT',
  `ip` varchar(255) DEFAULT NULL,
  `time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户注册日志表';

CREATE TABLE IF NOT EXISTS `gs_tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `platform` varchar(255) NOT NULL,
  `level` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:已开启 1:已关闭',
  `reply_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:待回复 1:已回复',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工单';

CREATE TABLE IF NOT EXISTS `gs_ticket_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `message` text CHARACTER SET utf8mb4 NOT NULL,
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工单内容';

CREATE TABLE IF NOT EXISTS `gs_notices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL,
  `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(1显示 0不显示)',
  `img_url` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公告';

CREATE TABLE IF NOT EXISTS `gs_uuids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uuid` varchar(64) NOT NULL DEFAULT '' COMMENT '用户UUID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未使用 1:已使用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='UUID库';


