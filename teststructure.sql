--
-- Database: `kxx`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE IF NOT EXISTS `ads` (
  `ad_id` smallint(6) NOT NULL,
  `ad_position` varchar(3) NOT NULL,
  `ad_display` smallint(6) NOT NULL,
  `ab_boards` varchar(255) NOT NULL,
  `ad_code` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ads`
--


-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL,
  `parentid` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `postedat` int(11) NOT NULL,
  `postedby` varchar(75) NOT NULL,
  `message` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `announcements`
--


-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `id` int(11) NOT NULL,
  `type` smallint(6) DEFAULT '0',
  `expired` smallint(6) DEFAULT '0',
  `allowread` smallint(6) DEFAULT '1',
  `ip` varchar(50) NOT NULL,
  `ipmd5` varchar(32) NOT NULL,
  `globalban` smallint(6) DEFAULT '0',
  `boards` varchar(255) NOT NULL,
  `by` varchar(75) NOT NULL,
  `at` int(11) NOT NULL,
  `until` int(11) NOT NULL,
  `reason` text NOT NULL,
  `staffnote` text NOT NULL,
  `appeal` text,
  `appealat` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `banlist`
--


-- --------------------------------------------------------

--
-- Table structure for table `bannedhashes`
--

CREATE TABLE IF NOT EXISTS `bannedhashes` (
  `id` int(11) NOT NULL,
  `md5` varchar(255) NOT NULL,
  `bantime` int(11) DEFAULT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bannedhashes`
--


-- --------------------------------------------------------

--
-- Table structure for table `blotter`
--

CREATE TABLE IF NOT EXISTS `blotter` (
  `id` int(11) NOT NULL,
  `important` smallint(6) NOT NULL,
  `at` int(11) NOT NULL,
  `message` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `blotter`
--


-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `board_id` int(11) NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  `board_name` varchar(75) DEFAULT NULL,
  `board_type` smallint(6) DEFAULT '0',
  `start` int(11) NOT NULL,
  `board_upload_type` smallint(6) DEFAULT NULL,
  `desc` varchar(75) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `section` smallint(6) DEFAULT '0',
  `board_max_upload_size` int(11) NOT NULL DEFAULT '1024000',
  `maxpages` int(11) NOT NULL DEFAULT '11',
  `maxage` int(11) NOT NULL DEFAULT '0',
  `markpage` smallint(6) DEFAULT '9',
  `maxreplies` int(11) NOT NULL DEFAULT '200',
  `messagelength` int(11) NOT NULL DEFAULT '8192',
  `createdon` int(11) NOT NULL,
  `board_locked` smallint(6) DEFAULT '0',
  `includeheader` text,
  `redirecttothread` smallint(6) DEFAULT '0',
  `anonymous` varchar(255) DEFAULT 'Anonymous',
  `forcedanon` smallint(6) DEFAULT '0',
  `embeds_allowed` varchar(255) DEFAULT NULL,
  `trial` smallint(6) DEFAULT '0',
  `popular` smallint(6) DEFAULT '0',
  `defaultstyle` varchar(50) DEFAULT NULL,
  `locale` varchar(30) DEFAULT NULL,
  `showid` smallint(6) DEFAULT '0',
  `compactlist` smallint(6) DEFAULT '0',
  `enablereporting` smallint(6) DEFAULT '1',
  `enablecaptcha` smallint(6) DEFAULT '0',
  `enablenofile` smallint(6) DEFAULT '0',
  `enablearchiving` smallint(6) DEFAULT '0',
  `enablecatalog` smallint(6) DEFAULT '1',
  `loadbalanceurl` varchar(255) DEFAULT NULL,
  `loadbalancepassword` varchar(255) DEFAULT NULL,
  `board_max_files` smallint(6) DEFAULT '1',
  `newsage` smallint(6) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`board_id`, `order`, `board_name`, `board_type`, `start`, `board_upload_type`, `desc`, `image`, `section`, `board_max_upload_size`, `maxpages`, `maxage`, `markpage`, `maxreplies`, `messagelength`, `createdon`, `board_locked`, `includeheader`, `redirecttothread`, `anonymous`, `forcedanon`, `embeds_allowed`, `trial`, `popular`, `defaultstyle`, `locale`, `showid`, `compactlist`, `enablereporting`, `enablecaptcha`, `enablenofile`, `enablearchiving`, `enablecatalog`, `loadbalanceurl`, `loadbalancepassword`, `board_max_files`, `newsage`) VALUES
(1, 1, 'b', 0, 0, 2, 'what', '', 0, 1024000, 11, 0, 9, 200, 8192, 0, 0, NULL, 0, 'Anonymous', 0, '', 0, 0, '', '', 0, 0, 1, 0, 0, 0, 1, '', '', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `board_filetypes`
--

CREATE TABLE IF NOT EXISTS `board_filetypes` (
  `type_board_id` smallint(6) DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `board_filetypes`
--

INSERT INTO `board_filetypes` (`type_board_id`, `type_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `embeds`
--

CREATE TABLE IF NOT EXISTS `embeds` (
  `embed_id` int(11) NOT NULL,
  `embed_ext` varchar(3) NOT NULL,
  `embed_name` varchar(255) NOT NULL,
  `embed_url` text NOT NULL,
  `embed_width` smallint(6) NOT NULL,
  `embed_height` smallint(6) NOT NULL,
  `embed_code` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `embeds`
--


-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `name` varchar(255) NOT NULL,
  `at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `events`
--


-- --------------------------------------------------------

--
-- Table structure for table `filetypes`
--

CREATE TABLE IF NOT EXISTS `filetypes` (
  `type_id` int(11) NOT NULL,
  `type_ext` varchar(255) NOT NULL,
  `type_mime` varchar(255) DEFAULT NULL,
  `type_image` varchar(255) DEFAULT NULL,
  `type_image_width` int(11) NOT NULL DEFAULT '0',
  `type_image_height` int(11) NOT NULL DEFAULT '0',
  `type_force_thumb` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `filetypes`
--

INSERT INTO `filetypes` (`type_id`, `type_ext`, `type_mime`, `type_image`, `type_image_width`, `type_image_height`, `type_force_thumb`) VALUES
(1, 'png', 'image/png', '', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `front`
--

CREATE TABLE IF NOT EXISTS `front` (
  `id` int(11) NOT NULL,
  `page` smallint(6) DEFAULT '0',
  `order` smallint(6) DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `poster` varchar(75) DEFAULT '',
  `email` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `front`
--


-- --------------------------------------------------------

--
-- Table structure for table `irc`
--

CREATE TABLE IF NOT EXISTS `irc` (
  `irc_id` int(11) NOT NULL,
  `irc_server` varchar(255) NOT NULL,
  `irc_port` smallint(6) NOT NULL DEFAULT '6667',
  `irc_channels` text,
  `irc_commands` text,
  `irc_nickname` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `irc`
--


-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE IF NOT EXISTS `loginattempts` (
  `username` varchar(255) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `loginattempts`
--


-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE IF NOT EXISTS `maps` (
  `mapname` varchar(255) DEFAULT NULL,
  `mapval` varchar(5) DEFAULT NULL,
  `ip` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `maps`
--


-- --------------------------------------------------------

--
-- Table structure for table `modlog`
--

CREATE TABLE IF NOT EXISTS `modlog` (
  `entry` text NOT NULL,
  `user` varchar(255) NOT NULL,
  `category` smallint(6) DEFAULT '0',
  `timestamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modlog`
--


-- --------------------------------------------------------

--
-- Table structure for table `module_settings`
--

CREATE TABLE IF NOT EXISTS `module_settings` (
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(255) DEFAULT 'string'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `module_settings`
--


-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_board` smallint(6) NOT NULL,
  `post_parent` int(11) NOT NULL DEFAULT '0',
  `post_name` varchar(255) NOT NULL,
  `post_tripcode` varchar(30) NOT NULL,
  `post_email` varchar(255) NOT NULL,
  `post_subject` varchar(255) NOT NULL,
  `post_message` text NOT NULL,
  `post_password` varchar(255) NOT NULL,
  `post_ip` varchar(75) NOT NULL,
  `post_ip_md5` varchar(32) NOT NULL,
  `post_tag` varchar(5) NOT NULL,
  `post_timestamp` int(11) NOT NULL,
  `post_stickied` smallint(6) DEFAULT '0',
  `post_locked` smallint(6) DEFAULT '0',
  `post_authority` smallint(6) DEFAULT '0',
  `post_reviewed` smallint(6) DEFAULT '0',
  `post_delete_time` int(11) NOT NULL DEFAULT '0',
  `post_deleted` smallint(6) DEFAULT '0',
  `post_bumped` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`,`post_board`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=120 ;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `post_board`, `post_parent`, `post_name`, `post_tripcode`, `post_email`, `post_subject`, `post_message`, `post_password`, `post_ip`, `post_ip_md5`, `post_tag`, `post_timestamp`, `post_stickied`, `post_locked`, `post_authority`, `post_reviewed`, `post_delete_time`, `post_deleted`, `post_bumped`) VALUES
(3, 1, 0, '', '', '', '', 'what', '', 'iYQ1gZ7T530SR1xlCXHwYf1Tf2IjM9wEiGexR1Dj+L0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296287743, 0, 0, 0, 0, 0, 0, 1296287743),
(4, 1, 0, '', '', '', '', 'what', '', 'viuCbUa7Y4HCmi6kI80lw5ybeclcro+wCFV19XQ06j4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296287797, 0, 0, 0, 0, 0, 0, 1296287797),
(5, 1, 0, '', '', '', '', 'what', '', 'EHmRjpMcnFL31hmd0JEpj6BLtx5xopuf1AxCNKXv6Ok=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296287816, 0, 0, 0, 0, 0, 0, 1296287816),
(6, 1, 0, '', '', '', '', 'what', '', 'OaIn+8DltAwmzgG0aN0xZke4UlyFS+yJncNKC7N21ms=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296287826, 0, 0, 0, 0, 0, 0, 1296287826),
(7, 1, 0, '', '', '', '', 'what', '', 'kCNvdce5coLPJgliPJI64Y9yYaG9ULBNNDj2Ub9AK/A=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296287883, 0, 0, 0, 0, 0, 0, 1296287883),
(8, 1, 0, '', '', '', '', 'what', '', 'Ux/xn+bXUWLYSKxwbL3ufNBaZ88KD+Nq323CQxh39UM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288032, 0, 0, 0, 0, 0, 0, 1296288032),
(9, 1, 0, '', '', '', '', 'what', '', 'zXyi8L+nQwy2tAabj/0Mw2yTIDpbHZWDICWUUl1eIb4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288114, 0, 0, 0, 0, 0, 0, 1296288114),
(10, 1, 0, '', '', '', '', 'what', '', 'aDw+/s3nrlaJ25RKX+kZxbmF21CLUy9POetYqJoB37s=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288172, 0, 0, 0, 0, 0, 0, 1296288172),
(11, 1, 0, '', '', '', '', 'what', '', 'PCaGKkLZICfq9jZaHV3dw8XpXVIG2qy9bceqo0PyOuU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288200, 0, 0, 0, 0, 0, 0, 1296288200),
(12, 1, 0, '', '', '', '', 'what', '', 'gs3wa2LDuZy+YiEjDCmmvOB1oc/BL1MRh57pWKZkVpQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288438, 0, 0, 0, 0, 0, 0, 1296288438),
(13, 1, 0, '', '', '', '', 'what', '', 'T3xlhBaw0Eilau0jw3/N0aUqXkhCdyA85sm3IQAX0Ps=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288605, 0, 0, 0, 0, 0, 0, 1296288605),
(14, 1, 0, '', '', '', '', 'what', '', 'qMyG6I20OonuKFxPYoIWXVfybjCYzcLPQ36EKPi015I=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288715, 0, 0, 0, 0, 0, 0, 1296288715),
(15, 1, 0, '', '', '', '', 'what', '', 'nugk5dXMy5x9bb8KD2f2C/bwz8zLaNf8ZlbIVRdFBuk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288810, 0, 0, 0, 0, 0, 0, 1296288810),
(16, 1, 0, '', '', '', '', 'what', '', 'BZDzmsg3Tr2CIlinGt694wI7cSpCo9RoaEpcJ9c4CwU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296288955, 0, 0, 0, 0, 0, 0, 1296288955),
(17, 1, 0, '', '', '', '', 'test', '', 'oGXiRHGxH63DibTNpahIP0mkfnaVVkxZ/3z8crRTq6g=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296324131, 0, 0, 0, 0, 0, 0, 1296324131),
(18, 1, 0, '', '', '', '', 'test', '', 'gfUenjqq3Fl550CdzwSrgBFlPGumcmQZgDsvA1C0qdQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326130, 0, 0, 0, 0, 0, 0, 1296326130),
(19, 1, 0, '', '', '', '', 'test', '', 'Bo/FT5liA/lQ3wPoIIrCm/a7EEpQ6BlTRyDQPRnQQwo=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326170, 0, 0, 0, 0, 0, 0, 1296326170),
(20, 1, 0, '', '', '', '', 'test', '', 'eido8Zq7hkZI39y1DKV+3kunpyY47Cq2piRViWYrtb8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326253, 0, 0, 0, 0, 0, 0, 1296326253),
(21, 1, 0, '', '', '', '', 'test', '', 'HLgOu6eUl3IzQLF27sCNy8UMMtbnEieeEy1HZEZoCS0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326562, 0, 0, 0, 0, 0, 0, 1296326562),
(22, 1, 0, '', '', '', '', 'test', '', '1+/M5lhAxoXEU4mZcjU5Ny1kWs1Rl1GRYaB42Jig124=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326893, 0, 0, 0, 0, 0, 0, 1296326893),
(23, 1, 0, '', '', '', '', 'test', '', 'RrfFzSSh3vHrMPfJzyqlVh30CcTk2faoFd3qZR8trbE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296326968, 0, 0, 0, 0, 0, 0, 1296326968),
(24, 1, 0, '', '', '', '', 'test', '', 'FPuzYyEj+pd571/J88Cn65kjA0Zg3wOZ4AO0bjkOY3M=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327019, 0, 0, 0, 0, 0, 0, 1296327019),
(25, 1, 0, '', '', '', '', 'test', '', '8KxnM9NU+9rbgNa+ILecZjXJnKgej+oFbo4Rv9HGXiE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327302, 0, 0, 0, 0, 0, 0, 1296327302),
(26, 1, 0, '', '', '', '', 'test', '', 'ehitEs55U2HKnhCYQeixTrY15Djhb6czb3rP713ATNM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327410, 0, 0, 0, 0, 0, 0, 1296327410),
(27, 1, 0, '', '', '', '', 'test', '', 'LdU0hAGGqbrJnXzRt8rhGKNWGRvYNN2BIc797+6vYpc=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327430, 0, 0, 0, 0, 0, 0, 1296327430),
(28, 1, 0, '', '', '', '', 'test', '', '3Zt+qSyH2gcFQd0HakPY8VddzsUouY80kzFoIghXWZ4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327476, 0, 0, 0, 0, 0, 0, 1296327476),
(29, 1, 0, '', '', '', '', 'test', '', 'suB3mqfY08XUDbd/yixVTrmVj4WNGWY/lSzMrHu2/cQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296327547, 0, 0, 0, 0, 0, 0, 1296327547),
(30, 1, 0, '', '', '', '', 'test', '', 'uzq+kD2SiTqquwVWZTUrDBxler+jrYOmexKnttCgNSM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296345605, 0, 0, 0, 0, 0, 0, 1296345605),
(31, 1, 0, '', '', '', '', 'test', '', 'lK0X3ERcVjHOMyRUGiDAoJRWOjwmku2+dg/apcnrvFo=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296345656, 0, 0, 0, 0, 0, 0, 1296345656),
(32, 1, 0, '', '', '', '', 'test', '', 'qS29RFwpMXgj3Bnf9ImXKJsTWOTIUQcnbvt4Rv4/LHU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296345717, 0, 0, 0, 0, 0, 0, 1296345717),
(33, 1, 0, '', '', '', '', 'test', '', 'u8XFZMi2Yp0ZbtPvZr++4EEVam3DHoTDLrVGZLVpMJc=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296345748, 0, 0, 0, 0, 0, 0, 1296345748),
(34, 1, 0, '', '', '', '', 'test', '', '2NdN4jTl7wP9U9svdEAR3txE6oxNKzUlPBPslq9TnUE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296345904, 0, 0, 0, 0, 0, 0, 1296345904),
(35, 1, 0, '', '', '', '', 'test', '', 'Bik0SgsmnWmWBhDkWfsEkmlNECKtlLdaLuH8BHLUPdw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346109, 0, 0, 0, 0, 0, 0, 1296346109),
(36, 1, 0, '', '', '', '', 'test', '', 'Xspk3Whyfmgsv/YpTbLOapgIYzvmYsZeJDg9b4p5sQM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346135, 0, 0, 0, 0, 0, 0, 1296346135),
(37, 1, 0, '', '', '', '', 'test', '', 'a1EumRduKvJBWGKtfLeBcXAGy+B6T2EhdvgX3m8xFsw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346179, 0, 0, 0, 0, 0, 0, 1296346179),
(38, 1, 0, '', '', '', '', 'test', '', 'k3jq76L4bjibDGh5MGWp8JWdx+mZyeRppuyo3vXE7bE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346267, 0, 0, 0, 0, 0, 0, 1296346267),
(39, 1, 0, '', '', '', '', 'test', '', 'RSCi9jVPmZbZfGdXU6eDN9xmP78Bk8zrZBb2/BjwKrg=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346352, 0, 0, 0, 0, 0, 0, 1296346352),
(40, 1, 0, '', '', '', '', 'test', '', '3vafUv1K8kdYx3ypBuyEVfs4SwGAsTFpRLdOToB/ohY=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346363, 0, 0, 0, 0, 0, 0, 1296346363),
(41, 1, 0, '', '', '', '', 'test', '', 'glMxv02DtZL62sRFcRh3EJLt154O7phuL6gwazi9/dk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346371, 0, 0, 0, 0, 0, 0, 1296346371),
(42, 1, 0, '', '', '', '', 'test', '', 'S0s1/p69ruCJJX+ukADJiMUWYgP254PeAg3XwZZwq3Q=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346382, 0, 0, 0, 0, 0, 0, 1296346382),
(43, 1, 0, '', '', '', '', 'test', '', 'PQWV0k78Ck7tBzh3aUz4EeAFIyjyICvw5Mq+5Ub6gic=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346429, 0, 0, 0, 0, 0, 0, 1296346429),
(44, 1, 0, '', '', '', '', 'test', '', 'Dgow0evUVoI4HGOngQ1h7QDTwHFQKfxrB3EbpvMAbQI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346440, 0, 0, 0, 0, 0, 0, 1296346440),
(45, 1, 0, '', '', '', '', 'test', '', 'lMyjE8F6K6EojNVAE5/McTC8XCb0ArWgAW5u1SY99jg=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346507, 0, 0, 0, 0, 0, 0, 1296346507),
(46, 1, 0, '', '', '', '', 'test', '', 'QQDRjsJA3cYHHRGxh8wDbebhOP57/z5W5aVJflGVjfo=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346520, 0, 0, 0, 0, 0, 0, 1296346520),
(47, 1, 0, '', '', '', '', 'test', '', 'TCXEui4+5vAhJx+ksDayac6ktdq0srxJL2fjzl74PrI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346540, 0, 0, 0, 0, 0, 0, 1296346540),
(48, 1, 0, '', '', '', '', 'test', '', '6N7VycKahJ0KAzHZn3YM/dgOfNBQe/YiRaxflmV+dSk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346553, 0, 0, 0, 0, 0, 0, 1296346553),
(49, 1, 0, '', '', '', '', 'test', '', 'yOBm2jdgbUv+2e59b6O00vpdQCfjGjySyZFekBa2rI8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346575, 0, 0, 0, 0, 0, 0, 1296346575),
(50, 1, 0, '', '', '', '', 'test', '', 'l21FiHxAwzPpXj6uqqzwMuIF0SQHR/IzU6qoWSyVFCc=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346605, 0, 0, 0, 0, 0, 0, 1296346605),
(51, 1, 0, '', '', '', '', 'test', '', 'puPvutHI8I1lXCOOkCNMb5un/ZlHwY5VfKJLwbGpjv0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346618, 0, 0, 0, 0, 0, 0, 1296346618),
(52, 1, 0, '', '', '', '', 'test', '', '7fi5RzOH9RQqDv+Nmlgsg96jyDOOmg4lVKj/HrkwYTE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346634, 0, 0, 0, 0, 0, 0, 1296346634),
(53, 1, 0, '', '', '', '', 'test', '', 'ULLK1Rhr30KfnKgffxclwF4whhMobCXNv8sJ+1JuZXw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346647, 0, 0, 0, 0, 0, 0, 1296346647),
(54, 1, 0, '', '', '', '', 'test', '', 'SxFJE9oZv9Xg/1XTcV05g7XHG7fHd10zgmDS/408WYc=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346661, 0, 0, 0, 0, 0, 0, 1296346661),
(55, 1, 0, '', '', '', '', 'test', '', 'AApC12NgGMZya0dZWlZgZntqL+yIG2AQi+5pFm3Hhm8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346672, 0, 0, 0, 0, 0, 0, 1296346672),
(56, 1, 0, '', '', '', '', 'test', '', 'PE/udeWDo9dKr0SigXFV1PQE9kKLS5xYnvqRkQB0wZE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346689, 0, 0, 0, 0, 0, 0, 1296346689),
(57, 1, 0, '', '', '', '', 'test', '', 'KHTxL0QGY7b3Rz+SehBOif8DynoulFEoyStcNGw7zi0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346696, 0, 0, 0, 0, 0, 0, 1296346696),
(58, 1, 0, '', '', '', '', 'test', '', 'PeN/Lwk8iiZ/C2fbN37feGTlaCbuNZ7BBSdzsxWGZ44=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346710, 0, 0, 0, 0, 0, 0, 1296346710),
(59, 1, 0, '', '', '', '', 'test', '', 'PieWlYJ4o+Ve7JwdC1pqkOntzdbleB7WJsaTZCGWtdw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346806, 0, 0, 0, 0, 0, 0, 1296346806),
(60, 1, 0, '', '', '', '', 'test', '', 'ZepoNq9Ft4wkSqjWaPUqd2GlGvj4WIu5k6PL9feMXgY=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346849, 0, 0, 0, 0, 0, 0, 1296346849),
(61, 1, 0, '', '', '', '', 'test', '', 'VJroxArzokTOA5Itk2BUj5+jo82V/FZfjTE9BShxzTI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346872, 0, 0, 0, 0, 0, 0, 1296346872),
(62, 1, 0, '', '', '', '', 'test', '', 'RZy0RfnIbOPgZr+kogIZfyjNScAkXxYM6bvdEXqvMD0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346952, 0, 0, 0, 0, 0, 0, 1296346952),
(63, 1, 0, '', '', '', '', 'test', '', '2ETXE5926KX1oV7SnVgk2qB+xRoXMCGJ5jWPSlBJid8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346957, 0, 0, 0, 0, 0, 0, 1296346957),
(64, 1, 0, '', '', '', '', 'test', '', '+ZCXn4M96bpNsONlfLFj/YHxFJUZ2efbuHRKxpecuYs=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296346983, 0, 0, 0, 0, 0, 0, 1296346983),
(65, 1, 0, '', '', '', '', 'test', '', 'B+c35W+jUjnqWAGFPRnHJ9PxOEH3QPCTvugdnCNJjIY=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347766, 0, 0, 0, 0, 0, 0, 1296347766),
(66, 1, 0, '', '', '', '', 'test', '', 'ZkTkzjp0oKmE55gSjn8laELKUIX65tJ0VcdRbZliqkw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347827, 0, 0, 0, 0, 0, 0, 1296347827),
(67, 1, 0, '', '', '', '', 'test', '', 'FEze/wD373Dn7e+EeOfUd+CWfFXBIK91ETNx+TnOlIU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347849, 0, 0, 0, 0, 0, 0, 1296347849),
(68, 1, 0, '', '', '', '', 'test', '', 'v31o4mv5h6GR+WfK8K4Wct/iB6eGpPkUuX5Bkfu4XII=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347871, 0, 0, 0, 0, 0, 0, 1296347871),
(69, 1, 0, '', '', '', '', 'test', '', 'rxiNfsIHqSRzRHB+U1AipZ7JZD7LP1J7egszdzQMXPA=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347888, 0, 0, 0, 0, 0, 0, 1296347888),
(70, 1, 0, '', '', '', '', 'test', '', '0sbdiaIsBcoaIs+mU8BqCiTpgbsro2XnLS/e2ygZVcg=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347908, 0, 0, 0, 0, 0, 0, 1296347908),
(71, 1, 0, '', '', '', '', 'test', '', 'sFxPlM7Dj8kETu1HF9YgO8W0okaNx82nlIYHxDVmbd8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347916, 0, 0, 0, 0, 0, 0, 1296347916),
(72, 1, 0, '', '', '', '', 'test', '', 'zJ4ed0K1//mwuH5C0vQ2GQp3J5FVN/Zlz+ZkpuBg5Ao=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296347943, 0, 0, 0, 0, 0, 0, 1296347943),
(73, 1, 0, '', '', '', '', 'test', '', 'Q49oB5ur3ZBsqQVcq/G3QjuZaAuA0+ZP79/GYdjbA0U=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348622, 0, 0, 0, 0, 0, 0, 1296348622),
(74, 1, 0, '', '', '', '', 'test', '', '85rPjnLDdEsKePuWT8wUWgaH6uVeloVf88aIipx1mHQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348709, 0, 0, 0, 0, 0, 0, 1296348709),
(75, 1, 0, '', '', '', '', 'test', '', '5XUkUGGABJClxV2O++THYXy+IPObc/+k8E1LhbQdfok=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348725, 0, 0, 0, 0, 0, 0, 1296348725),
(76, 1, 0, '', '', '', '', 'test', '', 'YoUp1LDpsPITZV9yPm+lmIaL1riJCpbW18vqeFy03/c=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348737, 0, 0, 0, 0, 0, 0, 1296348737),
(77, 1, 0, '', '', '', '', 'test', '', 'rZO4CnB06CpO9H4Zq1NUEp+TS9ppndhUmopL4VByHvc=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348806, 0, 0, 0, 0, 0, 0, 1296348806),
(78, 1, 0, '', '', '', '', 'test', '', '2tLqUTHFAPKi1BTUjdh6loJ74CT3478L9BAJ3HejEqw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348841, 0, 0, 0, 0, 0, 0, 1296348841),
(79, 1, 0, '', '', '', '', 'test', '', '4YW9NT18HvyPdk3BtT74UnL9xVrIWo2bz2ZjYxjeih4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348923, 0, 0, 0, 0, 0, 0, 1296348923),
(80, 1, 0, '', '', '', '', 'test', '', 'wXV1bn7UPLkbTVbj6C5aQ3n+sSLG3mfR6olae3aHV1w=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348944, 0, 0, 0, 0, 0, 0, 1296348944),
(81, 1, 0, '', '', '', '', 'test', '', 'Dgv+/XMtEnKj7/+U5ZMwQxGIciAZRsZ2YBnzLWJ9I0w=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296348957, 0, 0, 0, 0, 0, 0, 1296348957),
(82, 1, 0, '', '', '', '', 'test', '', 'nnvz8ViZ1XLzeuMzsiZI0THikF0ondtA2Cc+4L6GXpo=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349054, 0, 0, 0, 0, 0, 0, 1296349054),
(83, 1, 0, '', '', '', '', 'test', '', 'QAPFan6tZ12mMZiSnCJfjDeuB8wocxGa/M1mCuuBQjs=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349167, 0, 0, 0, 0, 0, 0, 1296349167),
(84, 1, 0, '', '', '', '', 'test', '', 'lO2uj9UAeOUvBV3sdy1iUN1KiXQhYskvNF+YrUnYxLs=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349249, 0, 0, 0, 0, 0, 0, 1296349249),
(85, 1, 0, '', '', '', '', 'test', '', 'xzGnBl9/DB/Wa8QATIxSGaQyIRdBym3sp+CmL3XKi8c=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349322, 0, 0, 0, 0, 0, 0, 1296349322),
(86, 1, 0, '', '', '', '', 'test', '', 'mrhqRU3FPFPvp+I6fWjvq5315pVBCQJuTnej6cZ/VW0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349390, 0, 0, 0, 0, 0, 0, 1296349390),
(87, 1, 0, '', '', '', '', 'test', '', 'JH2U+gGkdFJXDTZ3mdq+FJdfeU8A5mUdsjpuj73kMoQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349445, 0, 0, 0, 0, 0, 0, 1296349445),
(88, 1, 0, '', '', '', '', 'test', '', 'M5Z/vLt2G2SpbnoqFKh82f/56+nZN0mJ2vV8eS17qOI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349476, 0, 0, 0, 0, 0, 0, 1296349476),
(89, 1, 0, '', '', '', '', 'test', '', 'J50P1UInjzjnhGViqh/8HoIfA/BkTgyGwWiZYq+/1ws=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349504, 0, 0, 0, 0, 0, 0, 1296349504),
(90, 1, 0, '', '', '', '', 'test', '', '9MEKPVbZpy208XpBcAbog9PnQi8SBJvLe1fjNF+dKcQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349539, 0, 0, 0, 0, 0, 0, 1296349539),
(91, 1, 0, '', '', '', '', 'test', '', '8LHhCzVlgTPwHA5pESNixloy+yb/0lPYc0usWpWDHE0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349548, 0, 0, 0, 0, 0, 0, 1296349548),
(92, 1, 0, '', '', '', '', 'test', '', 'y3KKFgNK6Xu1hlbpU108YmDjDsP2xkOxem1hWOi+T98=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349654, 0, 0, 0, 0, 0, 0, 1296349654),
(93, 1, 0, '', '', '', '', 'test', '', 'UaXNWgnVD/XXGo2DvrV0rf5yKtgYhEI8AFOyBBHNjJw=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349677, 0, 0, 0, 0, 0, 0, 1296349677),
(94, 1, 0, '', '', '', '', 'test', '', 'iDqu1erpBWywT6N18ovP1Swy213JJYIY7SGfwVbCLWo=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349701, 0, 0, 0, 0, 0, 0, 1296349701),
(95, 1, 0, '', '', '', '', 'test', '', 'oEaeRbiWmARzqCpwL8ABeOtSd1Tk+Y3q4PazNkKtB5Q=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349863, 0, 0, 0, 0, 0, 0, 1296349863),
(96, 1, 0, '', '', '', '', 'test', '', 'gEmjDk52SkMwZ2nP9tp86/M44YT7IHRD+PsoQ7/OPxg=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296349883, 0, 0, 0, 0, 0, 0, 1296349883),
(97, 1, 0, '', '', '', '', 'test', '', 'jpg76T8/4havGjtQSWhWWRHV+PYZQKouxmUfM8CSpAI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350016, 0, 0, 0, 0, 0, 0, 1296350016),
(98, 1, 0, '', '', '', '', 'test', '', 'wWgf+5z7X9EGvYvcm8zkvSOYRirAT2xtBXL25oGcbV8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350124, 0, 0, 0, 0, 0, 0, 1296350124),
(99, 1, 0, '', '', '', '', 'test', '', 'CQoCrLW3gk5qu7+vRg8U/xGoF8VSv6CEbdoiFRZibyM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350393, 0, 0, 0, 0, 0, 0, 1296350393),
(100, 1, 0, '', '', '', '', 'test', '', 'DyMyf/vPE3SSp/WrUtosPxwnoD0gRw0lKe8sbyPBXNQ=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350427, 0, 0, 0, 0, 0, 0, 1296350427),
(101, 1, 0, '', '', '', '', 'test', '', 'MQQcbAhyBWLWvh2LFAAwi/nUnRxumghlgvrQAtg7oRk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350806, 0, 0, 0, 0, 0, 0, 1296350806),
(102, 1, 0, '', '', '', '', 'test', '', 'JNDtTYgod+1R/BDwLuiDKTgXabmWaEveW4bplayl3w8=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350814, 0, 0, 0, 0, 0, 0, 1296350814),
(103, 1, 0, '', '', '', '', 'test', '', 'NuV7WdO2u35HA6Bwv6BukSRpZksO/N4Ng8WSrtBeWuI=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350825, 0, 0, 0, 0, 0, 0, 1296350825),
(104, 1, 0, '', '', '', '', 'test', '', '8N3JeJPC66v0iH1kNDNHFqElTMtAPkC+QkJ2unBYUJs=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350827, 0, 0, 0, 0, 0, 0, 1296350827),
(105, 1, 0, '', '', '', '', 'test', '', '0sZRHggPZ/8ECdLXKViaIn674/T25RZ/Lp5iTB5I/bk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350861, 0, 0, 0, 0, 0, 0, 1296350861),
(106, 1, 0, '', '', '', '', 'test', '', '7u59LJ9thdrqL61yNRGePMkdb/EAqmhzmQbe0kZS3lU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350939, 0, 0, 0, 0, 0, 0, 1296350939),
(107, 1, 0, '', '', '', '', 'test', '', 'UtfDxAaSMNSKF7xKgd2dE/XlSh9UjYNbU1FErbHw62U=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296350985, 0, 0, 0, 0, 0, 0, 1296350985),
(108, 1, 0, '', '', '', '', 'test', '', '5ezpiQt5cLDkP0zTSvQKxdQ2m7DlOREDN8/aTlNhqPk=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351253, 0, 0, 0, 0, 0, 0, 1296351253),
(109, 1, 0, '', '', '', '', 'test', '', '3lJG60t9Gan8qwIt8hqYZteakCm0AwQspFdyX/fgUtY=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351287, 0, 0, 0, 0, 0, 0, 1296351287),
(110, 1, 0, '', '', '', '', 'test', '', 'UGqFyg2l9MWbcIEILtbeYYJIj+Fv8uU8gNdayieCVj0=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351306, 0, 0, 0, 0, 0, 0, 1296351306),
(111, 1, 0, '', '', '', '', 'test', '', 'HyMLXyTbb7lLXfUClDKt3jOWPWV7xbLV3DEn3CibpJY=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351310, 0, 0, 0, 0, 0, 0, 1296351310),
(112, 1, 0, '', '', '', '', 'test', '', 'kjwV6L9wpZOPGXRywith37yDjYqjFwyu16j5R4EQTi4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351325, 0, 0, 0, 0, 0, 0, 1296351325),
(113, 1, 0, '', '', '', '', 'test', '', 'ZBxhPgZa7PgC9tZ5wVPkEx+JxuUO40CgS8lIq0UXDIU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351435, 0, 0, 0, 0, 0, 0, 1296351435),
(114, 1, 0, '', '', '', '', 'test', '', 'R/Wr/JxitJ+kh4hbuc1Nx5QFtFc3KZ85k64C8n7zKsE=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351463, 0, 0, 0, 0, 0, 0, 1296351463),
(115, 1, 0, '', '', '', '', 'test', '', 'qrGggg5mnQao0Dhu+BzN3AJd37YIvF3vGZzHvBUvmBA=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351508, 0, 0, 0, 0, 0, 0, 1296351508),
(116, 1, 0, '', '', '', '', 'test', '', '6FVRXnG02W2tRNP80tWMHDGvPz3PDNLrgmqx7r5mKA4=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351565, 0, 0, 0, 0, 0, 0, 1296351565),
(117, 1, 0, '', '', '', '', 'test', '', '2N55/xGhhYmGJS+/9I0x+0I9bTDz1QYRD+/2ZAMWYlg=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351592, 0, 0, 0, 0, 0, 0, 1296351592),
(118, 1, 0, '', '', '', '', 'test', '', 'KpaSMvlZfkyovDohwy+L4VP7qTCpMo7pKEN+W+nBJgM=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351716, 0, 0, 0, 0, 0, 0, 1296351716),
(119, 1, 0, '', '', '', '', 'test', '', 'uqIL15cGqDxWur3Ib2aOHk9tudogIltFbDlFSlR57mU=', 'f528764d624db129b32c21fbca0cb8d6', '', 1296351733, 0, 0, 0, 0, 0, 0, 1296351733);

-- --------------------------------------------------------

--
-- Table structure for table `post_files`
--

CREATE TABLE IF NOT EXISTS `post_files` (
  `file_post` int(11) NOT NULL,
  `file_board` smallint(6) NOT NULL,
  `file_name` varchar(50) NOT NULL,
  `file_md5` varchar(32) NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `file_original` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT '0',
  `file_size_formatted` varchar(75) NOT NULL,
  `file_image_width` smallint(6) DEFAULT '0',
  `file_image_height` smallint(6) DEFAULT '0',
  `file_thumb_width` smallint(6) DEFAULT '0',
  `file_thumb_height` smallint(6) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post_files`
--

INSERT INTO `post_files` (`file_post`, `file_board`, `file_name`, `file_md5`, `file_type`, `file_original`, `file_size`, `file_size_formatted`, `file_image_width`, `file_image_height`, `file_thumb_width`, `file_thumb_height`) VALUES
(38, 1, '129634626775', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(39, 1, '129634635274', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(40, 1, '129634636322', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(41, 1, '129634637086', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(42, 1, '129634638266', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(43, 1, '129634642985', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(44, 1, '129634644042', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(45, 1, '129634650752', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(46, 1, '129634651916', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(47, 1, '129634654095', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(48, 1, '129634655222', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(49, 1, '129634657447', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(50, 1, '129634660524', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(51, 1, '12963466187', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(52, 1, '129634663424', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(53, 1, '129634664756', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(54, 1, '129634666191', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(55, 1, '129634667217', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(56, 1, '129634668943', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(57, 1, '129634669664', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(58, 1, '12963467101', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(59, 1, '129634680647', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(60, 1, '129634684922', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(61, 1, '129634687170', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(62, 1, '129634695276', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(63, 1, '129634695712', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(64, 1, '129634698343', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(65, 1, '12963477669', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(66, 1, '129634782754', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(67, 1, '129634784923', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(68, 1, '129634787144', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(69, 1, '129634788827', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(70, 1, '129634790873', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(71, 1, '129634791658', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(72, 1, '129634794344', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(73, 1, '12963486227', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(74, 1, '12963487092', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(75, 1, '129634872512', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(76, 1, '129634873749', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(77, 1, '129634880673', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(78, 1, '12963488412', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(79, 1, '129634892379', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(80, 1, '129634894491', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(81, 1, '129634895776', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(82, 1, '129634905445', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(83, 1, '129634916722', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(84, 1, '129634924929', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(85, 1, '129634932234', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(86, 1, '129634939051', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(87, 1, '129634944412', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(88, 1, '129634947670', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(89, 1, '129634950392', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(90, 1, '12963495397', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(91, 1, '129634954899', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(92, 1, '129634965462', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(93, 1, '129634967793', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(94, 1, '129634970115', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(95, 1, '129634986277', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(96, 1, '129634988328', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(97, 1, '129635001659', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(98, 1, '12963501242', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(99, 1, '129635039262', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(100, 1, '129635042781', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(101, 1, '129635080688', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(102, 1, '12963508146', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(103, 1, '129635082568', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(104, 1, '129635082776', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(105, 1, '129635086018', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(106, 1, '129635093980', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(107, 1, '129635098536', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(108, 1, '129635125270', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(109, 1, '129635128787', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(110, 1, '129635130549', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(111, 1, '129635131072', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(112, 1, '129635132582', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(113, 1, '129635143476', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(114, 1, '129635146385', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(115, 1, '129635150848', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(116, 1, '129635156566', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(117, 1, '129635159167', 'dc3768bcaee2cc4ba81021b482228ce2', '.png', 'vque', 8031, '8031', 640, 480, 200, 150),
(119, 1, '129635173351', 'dc3768bcaee2cc4ba81021b482228ce2', 'png', 'vque', 8031, '8031', 640, 480, 200, 150);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL,
  `cleared` smallint(6) DEFAULT '0',
  `board` varchar(255) NOT NULL,
  `postid` int(11) NOT NULL,
  `when` int(11) NOT NULL,
  `ip` varchar(75) NOT NULL,
  `reason` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `reports`
--


-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  `hidden` smallint(6) DEFAULT '0',
  `name` varchar(255) DEFAULT '0',
  `abbreviation` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sections`
--


-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(3) NOT NULL,
  `type` smallint(6) DEFAULT '0',
  `boards` text,
  `addedon` int(11) NOT NULL,
  `lastactive` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `staff`
--


-- --------------------------------------------------------

--
-- Table structure for table `watchedthreads`
--

CREATE TABLE IF NOT EXISTS `watchedthreads` (
  `id` int(11) NOT NULL,
  `threadid` int(11) NOT NULL,
  `board` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `lastsawreplyid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `watchedthreads`
--


-- --------------------------------------------------------

--
-- Table structure for table `wordfilter`
--

CREATE TABLE IF NOT EXISTS `wordfilter` (
  `id` int(11) NOT NULL,
  `word` varchar(75) NOT NULL,
  `replacedby` varchar(75) NOT NULL,
  `boards` text NOT NULL,
  `time` int(11) NOT NULL,
  `regex` smallint(6) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wordfilter`
--

