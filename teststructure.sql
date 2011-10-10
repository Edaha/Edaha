

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `announcements`
--


-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `ban_id` int(11) NOT NULL AUTO_INCREMENT,
  `ban_type` smallint(6) DEFAULT 0,
  `ban_expired` smallint(6) DEFAULT 0,
  `ban_allow_read` smallint(6) DEFAULT 1,
  `ban_ip` varchar(100) NOT NULL,
  `ban_ip_md5` varchar(32) NOT NULL,
  `ban_boards` varchar(255) NOT NULL,
  `ban_by` varchar(75) NOT NULL,
  `ban_created` int(11) NOT NULL,
  `ban_expires` int(11) NOT NULL,
  `ban_reason` text NOT NULL,
  `ban_staff_note` text NOT NULL,
  `ban_appeal_message` text,
  `ban_appeal_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY(`ban_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blotter`
--


-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `board_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `board_order` smallint(6) DEFAULT NULL,
  `board_name` varchar(75) DEFAULT NULL,
  `board_type` smallint(6) DEFAULT '0',
  `board_start` int(11) NOT NULL,
  `board_upload_type` smallint(6) DEFAULT NULL,
  `board_desc` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `board_section` smallint(6) DEFAULT '0',
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
  `newsage` smallint(6) DEFAULT '0',
  PRIMARY KEY (`board_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`board_id`, `board_order`, `board_name`, `board_type`, `board_start`, `board_upload_type`, `board_desc`, `image`, `board_section`, `board_max_upload_size`, `maxpages`, `maxage`, `markpage`, `maxreplies`, `messagelength`, `createdon`, `board_locked`, `includeheader`, `redirecttothread`, `anonymous`, `forcedanon`, `embeds_allowed`, `trial`, `popular`, `defaultstyle`, `locale`, `showid`, `compactlist`, `enablereporting`, `enablecaptcha`, `enablenofile`, `enablearchiving`, `enablecatalog`, `loadbalanceurl`, `loadbalancepassword`, `board_max_files`, `newsage`) VALUES
(1, 1, 's1b1', 0, 0, 2, 'Board 1 (S1)', '', 1, 1024000, 11, 0, 9, 200, 8192, 0, 0, NULL, 0, 'Anonymous', 0, '', 0, 0, '', '', 0, 0, 1, 0, 0, 0, 1, '', '', 1, 0),
(2, 2, 's1b2', 0, 0, 2, 'Board 2 (S1)', '', 1, 1024000, 11, 0, 9, 200, 8192, 0, 0, NULL, 0, 'Anonymous', 0, '', 0, 0, '', '', 0, 0, 1, 0, 0, 0, 1, '', '', 1, 0),
(3, 1, 's2b1', 0, 0, 2, 'Board 1 (S2)', '', 2, 1024000, 11, 0, 9, 200, 8192, 0, 0, NULL, 0, 'Anonymous', 0, '', 0, 0, '', '', 0, 0, 1, 0, 0, 0, 1, '', '', 1, 0),
(4, 2, 's2b2', 0, 0, 2, 'Board 2 (S2)', '', 2, 1024000, 11, 0, 9, 200, 8192, 0, 0, NULL, 0, 'Anonymous', 0, '', 0, 0, '', '', 0, 0, 1, 0, 0, 0, 1, '', '', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `board_filetypes`
--

CREATE TABLE IF NOT EXISTS `board_filetypes` (
  `type_board_id` smallint(6) DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `board_filetypes`
--

INSERT INTO `board_filetypes` (`type_board_id`, `type_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE cache (
    cache_path varchar(255) NOT NULL DEFAULT '',
    cache_value text,
    cache_array tinyint(1) NOT NULL DEFAULT 0,
    cache_updated int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (cache_path)
);

--
-- Dumping data for table `cache`
--
INSERT INTO cache VALUES ('version', '1.0', 0, 1317940926);
INSERT INTO cache VALUES ('addons:app_cache', 'a:0:{}', 1, 1317941331);

--
-- Table structure for table `configuration`
--

CREATE TABLE IF NOT EXISTS `configuration` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(255) NOT NULL,
  `config_description` text,
  `config_type` varchar(255) NOT NULL,
  `config_variable` varchar(255) NOT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`config_id`, `config_name`, `config_description`, `config_type`, `config_variable`) VALUES
(1, 'Default Ban Message', 'The text to add at the end of a post if a ban is placed and "Add ban message" is checked', 'textarea', 'display:banmsg');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `entry_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entry_type` smallint(6) DEFAULT '0',
  `entry_order` smallint(6) DEFAULT '0',
  `entry_subject` varchar(255) NOT NULL,
  `entry_message` text NOT NULL,
  `entry_time` int(11) NOT NULL DEFAULT '0',
  `entry_name` varchar(75) DEFAULT '',
  `entry_email` varchar(255) DEFAULT '',
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `front`
--

INSERT INTO `front` (`entry_id`, `entry_type`, `entry_order`, `entry_subject`, `entry_message`, `entry_time`, `entry_name`, `entry_email`) VALUES
(3, 0, 0, 'Kusaba 1.0', 'Kusaba X 1.0 front works. :)', 1309157915, '', ''),
(4, 1, 0, 'FAQ', 'Works', 0, '', ''),
(5, 2, 0, 'Rules', 'Works', 0, '', '');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `irc`
--


-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE IF NOT EXISTS `loginattempts` (
  `attempt_name` varchar(255) NOT NULL,
  `attempt_ip` varchar(20) NOT NULL,
  `attempt_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `loginattempts`
--


-- --------------------------------------------------------

--
-- Table structure for table `manage_sessions`
--

CREATE TABLE IF NOT EXISTS `manage_sessions` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `session_ip` varchar(100) NOT NULL DEFAULT '',
  `session_staff_id` mediumint(8) NOT NULL DEFAULT '0',
  `session_location` varchar(64) NOT NULL DEFAULT '',
  `session_name` varchar(64) NOT NULL DEFAULT '',
  `session_log_in_time` int(10) NOT NULL DEFAULT '0',
  `session_last_action` int(10) NOT NULL DEFAULT '0',
  `session_url` text,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE IF NOT EXISTS `maps` (
  `mapname` varchar(255) DEFAULT NULL,
  `mapval` varchar(5) DEFAULT NULL,
  `ip` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `modlog`
--


-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `module_id` mediumint(4) unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(32) NOT NULL DEFAULT '',
  `module_application` varchar(32) NOT NULL DEFAULT '',
  `module_file` varchar(32) NOT NULL DEFAULT '',
  `module_description` varchar(100) NOT NULL DEFAULT '',
  `module_position` int(5) NOT NULL DEFAULT '0',
  `module_manage` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_id`),
  KEY `module_application` (`module_application`),
  KEY `module_file` (`module_file`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_name`, `module_application`, `module_file`, `module_description`, `module_position`, `module_manage`) VALUES
(8, 'Staff', 'core', 'staff', 'Staff Configuration', 1, 1),
(2, 'Image Board', 'board', 'image', 'Generator for an image-type board', 0, 0),
(3, 'Oekaki Board', 'board', 'oekaki', 'Generator for an oekaki-type board', 0, 0),
(4, 'Upload Board', 'board', 'upload', 'Generator for an upload-type board', 0, 0),
(5, 'Text Board', 'board', 'text', 'Generator for a text board', 0, 0),
(6, 'Site', 'core', 'site', 'Manage the Site Configuration', 0, 1),
(7, 'Index', 'core', 'index', 'Handles the front page features (news, faq, etc)', 0, 0),
(9, 'Bans', 'core', 'bans', 'Provides functionality for adding and editing bans.', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `module_settings`
--

CREATE TABLE IF NOT EXISTS `module_settings` (
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(255) DEFAULT 'string'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `post_ip` varchar(100) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=120 ;

--
-- Dumping data for table `posts`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `post_files`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `reports`
--


-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL,
  `section_order` smallint(6) DEFAULT NULL,
  `hidden` smallint(6) DEFAULT '0',
  `name` varchar(255) DEFAULT '0',
  `abbreviation` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_order`, `hidden`, `name`, `abbreviation`) VALUES
(1, 1, 0, 'Section 1', 'sec1'),
(2, 2, 0, 'Section 2', 'sec2');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `user_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_salt` varchar(10) NOT NULL,
  `user_type` smallint(6) DEFAULT '0',
  `user_boards` text,
  `user_add_time` int(11) NOT NULL,
  `user_last_active` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`user_id`, `user_name`, `user_password`, `user_salt`, `user_type`, `user_boards`, `user_add_time`, `user_last_active`) VALUES ('1', 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', '', '1', NULL, '0', '0');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `watchedthreads`
--


-- --------------------------------------------------------

--
-- Table structure for table `filter`
--

CREATE TABLE IF NOT EXISTS `filter` (
  `filter_id` int(11) NOT NULL,
  `filter_word` varchar(75) NOT NULL,
  `filter_type` tinyint(1) NOT NULL,
  `filter_punishment` varchar(75) NOT NULL,
  `filter_boards` text NOT NULL,
  `filter_added` int(11) NOT NULL,
  `filter_regex` boolean DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `wordfilter`
--

