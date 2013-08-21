-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成時間: 2013 年 8 月 20 日 15:48
-- サーバのバージョン: 5.1.44
-- PHP のバージョン: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- データベース: `op_coupon`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `dc_ip_user_id`
--

CREATE TABLE IF NOT EXISTS `dc_ip_user_id` (
  `ip_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `op_uniq_id` char(32) NOT NULL,
  `mailaddr_md5` char(32) NOT NULL,
  `card_number` char(4) NOT NULL DEFAULT '' COMMENT 'クレジットカード番号の末尾4桁',
  `card_expire` char(4) NOT NULL DEFAULT '' COMMENT 'クレジットカードの有効期限（MMYY）',
  `card_brand` enum('VISA','JCB','Master','UC') DEFAULT NULL COMMENT 'クレジットカードのブランド',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='IP_USER_IDを保存するテーブル' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_account`
--

CREATE TABLE IF NOT EXISTS `t_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_md5` char(32) NOT NULL COMMENT 'md5(email)',
  `email` varchar(200) NOT NULL COMMENT 'Blowfish::Encrypt(email)',
  `password` varchar(32) NOT NULL COMMENT 'md5(password)',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_md5` (`email_md5`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_address`
--

CREATE TABLE IF NOT EXISTS `t_address` (
  `account_id` int(11) NOT NULL,
  `seq_no` int(11) NOT NULL DEFAULT '1' COMMENT '順番号',
  `last_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `first_name` varchar(20) CHARACTER SET utf8 NOT NULL,
  `zipcode` varchar(100) CHARACTER SET utf8 NOT NULL,
  `pref` int(10) NOT NULL,
  `city` varchar(10) CHARACTER SET utf8 NOT NULL,
  `address` varchar(30) CHARACTER SET utf8 NOT NULL,
  `building` varchar(20) CHARACTER SET utf8 NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`,`seq_no`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='住所テーブル。１つのアカウントに複数の住所が紐づく';

-- --------------------------------------------------------

--
-- テーブルの構造 `t_buy`
--

CREATE TABLE IF NOT EXISTS `t_buy` (
  `buy_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `num` int(11) NOT NULL COMMENT '数量',
  `sid` varchar(32) NOT NULL COMMENT '取引コード（購入商品と決済情報を紐付ける）',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`buy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_coupon`
--

CREATE TABLE IF NOT EXISTS `t_coupon` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'クーポンID',
  `coupon_title` varchar(30) NOT NULL COMMENT 'クーポンのタイトル',
  `coupon_description` text NOT NULL COMMENT 'クーポンの説明',
  `coupon_normal_price` int(11) NOT NULL COMMENT 'クーポンの通常販売金額',
  `coupon_sales_price` int(11) NOT NULL COMMENT 'クーポンの販売価格',
  `coupon_sales_num_top` int(11) NOT NULL COMMENT '販売枚数の上限',
  `coupon_sales_num_bottom` int(11) NOT NULL COMMENT '販売間数の下限',
  `coupon_sales_start` datetime NOT NULL COMMENT '[GMT] クーポンの販売開始時間',
  `coupon_sales_finish` datetime NOT NULL COMMENT '[GMT] クーポンの販売終了時間',
  `coupon_expire` datetime NOT NULL COMMENT '[GMT] クーポンの利用有効期限',
  `coupon_person_num` int(11) NOT NULL DEFAULT '9' COMMENT '一人が購入できる枚数',
  `coupon_hidden` datetime DEFAULT NULL COMMENT 'クーポンを非表示にする（購入可能）',
  `shop_id` int(11) NOT NULL COMMENT 't_customer.shop_id',
  `memo` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
  PRIMARY KEY (`coupon_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_customer`
--

CREATE TABLE IF NOT EXISTS `t_customer` (
  `account_id` int(11) NOT NULL,
  `shop_id` int(11) DEFAULT NULL COMMENT 't_customerからshop_idを分離する',
  `shop_flag` tinyint(1) DEFAULT NULL,
  `memo` text,
  `nick_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `gender` enum('M','F') NOT NULL COMMENT '性別',
  `favorite_pref` int(11) DEFAULT NULL,
  `favorite_city` int(11) DEFAULT NULL,
  `birthday` date NOT NULL,
  `address_seq_no` int(11) NOT NULL DEFAULT '1' COMMENT 't_addressの主たる所在地',
  `uid` varchar(32) NOT NULL COMMENT 'For credit card',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `shop_id` (`shop_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_forget`
--

CREATE TABLE IF NOT EXISTS `t_forget` (
  `sent_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(39) NOT NULL,
  `email_forget` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_photo`
--

CREATE TABLE IF NOT EXISTS `t_photo` (
  `shop_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `seq_no` int(11) NOT NULL,
  `url` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`shop_id`,`coupon_id`,`seq_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Saves photo url';

-- --------------------------------------------------------

--
-- テーブルの構造 `t_shop`
--

CREATE TABLE IF NOT EXISTS `t_shop` (
  `shop_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ショップID',
  `shop_name` varchar(200) NOT NULL COMMENT '店名',
  `shop_description` text NOT NULL COMMENT 'お店の説明',
  `shop_pref` varchar(100) NOT NULL,
  `shop_city` varchar(100) NOT NULL,
  `shop_address` text NOT NULL,
  `shop_building` varchar(100) NOT NULL,
  `shop_tel` varchar(100) NOT NULL,
  `shop_holiday` varchar(100) NOT NULL,
  `shop_open` time NOT NULL,
  `shop_close` time NOT NULL,
  `shop_railway` varchar(100) NOT NULL,
  `shop_station` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
  PRIMARY KEY (`shop_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `t_test`
--

CREATE TABLE IF NOT EXISTS `t_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text CHARACTER SET utf8 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
