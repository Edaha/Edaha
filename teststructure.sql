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

-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) DEFAULT '0',
  `expired` smallint(6) DEFAULT '0',
  `allow_read` smallint(6) DEFAULT '1',
  `ip` varchar(100) NOT NULL,
  `ipmd5` varchar(32) NOT NULL,
  `boards` varchar(255) NOT NULL,
  `by` varchar(75) NOT NULL,
  `created` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `reason` text NOT NULL,
  `staff_note` text NOT NULL,
  `appeal_message` text,
  `appeal_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bannedhashes`
--

CREATE TABLE IF NOT EXISTS `bannedhashes` (
  `id` int(11) NOT NULL,
  `md5` varchar(255) NOT NULL,
  `banduration` int(11) DEFAULT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `board_header_image` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `board_section` smallint(6) DEFAULT '0',
  `board_max_upload_size` int(11) NOT NULL DEFAULT '1024000',
  `board_max_pages` int(11) NOT NULL DEFAULT '11',
  `board_max_age` int(11) NOT NULL DEFAULT '0',
  `board_mark_page` smallint(6) DEFAULT '9',
  `board_max_replies` int(11) NOT NULL DEFAULT '200',
  `board_max_message_length` int(11) NOT NULL DEFAULT '8192',
  `board_created_on` int(11) NOT NULL,
  `board_locked` tinyint(1) DEFAULT '0',
  `board_include_header` text,
  `board_redirect_to_thread` tinyint(1) DEFAULT '0',
  `board_anonymous` varchar(255) DEFAULT 'Anonymous',
  `board_forced_anon` tinyint(1) DEFAULT '0',
  `board_allowed_embeds` varchar(255) DEFAULT NULL,
  `board_trial` tinyint(1) DEFAULT '0',
  `board_popular` tinyint(1) DEFAULT '0',
  `board_default_style` varchar(50) DEFAULT NULL,
  `board_locale` varchar(30) DEFAULT NULL,
  `board_show_id` tinyint(1) DEFAULT '0',
  `board_compact_list` tinyint(1) DEFAULT '0',
  `board_reporting` tinyint(1) DEFAULT '1',
  `board_captcha` tinyint(1) DEFAULT '0',
  `board_no_file` tinyint(1) DEFAULT '0',
  `board_archiving` tinyint(1) DEFAULT '0',
  `board_catalog` tinyint(1) DEFAULT '1',
  `board_max_files` smallint(6) DEFAULT '1',
  PRIMARY KEY (`board_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`board_id`, `board_order`, `board_name`, `board_type`, `board_start`, `board_upload_type`, `board_desc`, `board_header_image`, `board_section`, `board_max_upload_size`, `board_max_pages`, `board_max_age`, `board_mark_page`, `board_max_replies`, `board_max_message_length`, `board_created_on`, `board_locked`, `board_include_header`, `board_redirect_to_thread`, `board_anonymous`, `board_forced_anon`, `board_allowed_embeds`, `board_trial`, `board_popular`, `board_default_style`, `board_locale`, `board_show_id`, `board_compact_list`, `board_reporting`, `board_captcha`, `board_no_file`, `board_archiving`, `board_catalog`, `board_max_files`) VALUES
(1, 1, 's1b1', 0, 0, 0, 'Board 1 (S1)', '', 1, 1024000, 11, 0, 9, 200, 8192, 0, 0, '', 0, 'Anonymous', 0, '', 0, 0, 'edaha', '', 0, 0, 1, 0, 0, 0, 1, 1),
(2, 2, 's1b2', 0, 0, 2, 'Board 2 (S1)', '', 1, 1024000, 11, 0, 9, 200, 8192, 0, 0, '', 0, 'Anonymous', 0, '', 0, 0, 'edaha', '', 0, 0, 1, 0, 0, 0, 1, 1),
(3, 1, 's2b1', 0, 0, 2, 'Board 1 (S2)', '', 2, 1024000, 11, 0, 9, 200, 8192, 0, 0, '', 0, 'Anonymous', 0, '', 0, 0, 'edaha', '', 0, 0, 1, 0, 0, 0, 1, 1),
(4, 2, 's2b2', 0, 0, 2, 'Board 2 (S2)', '', 2, 1024000, 11, 0, 9, 200, 8192, 0, 0, '', 0, 'Anonymous', 0, '', 0, 0, 'edaha', '', 0, 0, 1, 0, 0, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `board_filetypes`
--

CREATE TABLE IF NOT EXISTS `board_filetypes` (
  `board_id` smallint(6) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0',
  KEY `board_id_type_id` (`board_id`,`type_id`),
  KEY `type_id_board_id` (`type_id`,`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `board_filetypes`
--

INSERT INTO `board_filetypes` (`board_id`, `type_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(3, 1),
(3, 2),
(3, 3),
(4, 1),
(4, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `board_filters`
--

CREATE TABLE IF NOT EXISTS `board_filters` (
  `board_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  UNIQUE KEY `filter_id_board_id` (`filter_id`,`board_id`),
  UNIQUE KEY `board_id_filter_id` (`board_id`,`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `board_filters`
--

INSERT INTO `board_filters` (`board_id`, `filter_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(1, 5),
(2, 5),
(3, 5),
(4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `cache_path` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `cache_value` text CHARACTER SET latin1,
  `cache_array` tinyint(1) NOT NULL DEFAULT '0',
  `cache_updated` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cache_path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`cache_path`, `cache_value`, `cache_array`, `cache_updated`) VALUES
('addons:app_cache', 'a:0:{}', 1, 1317941331),
('version', '1.0', 0, 1317940926),
('boardopts:s1b1', 'a:1:{i:0;O:8:"stdClass":35:{s:8:"board_id";s:1:"1";s:11:"board_order";s:1:"1";s:10:"board_name";s:4:"s1b1";s:10:"board_type";s:1:"0";s:11:"board_start";s:1:"0";s:17:"board_upload_type";s:1:"0";s:10:"board_desc";s:12:"Board 1 (S1)";s:18:"board_header_image";s:0:"";s:13:"board_section";s:1:"1";s:21:"board_max_upload_size";s:7:"1024000";s:15:"board_max_pages";s:2:"11";s:13:"board_max_age";s:1:"0";s:15:"board_mark_page";s:1:"9";s:17:"board_max_replies";s:3:"200";s:24:"board_max_message_length";s:4:"8192";s:16:"board_created_on";s:1:"0";s:12:"board_locked";s:1:"0";s:20:"board_include_header";s:0:"";s:24:"board_redirect_to_thread";s:1:"0";s:15:"board_anonymous";s:9:"Anonymous";s:17:"board_forced_anon";s:1:"0";s:20:"board_allowed_embeds";s:0:"";s:11:"board_trial";s:1:"0";s:13:"board_popular";s:1:"0";s:19:"board_default_style";s:5:"edaha";s:12:"board_locale";s:0:"";s:13:"board_show_id";s:1:"0";s:18:"board_compact_list";s:1:"0";s:15:"board_reporting";s:1:"1";s:13:"board_captcha";s:1:"0";s:13:"board_no_file";s:1:"0";s:15:"board_archiving";s:1:"0";s:13:"board_catalog";s:1:"1";s:15:"board_max_files";s:1:"1";s:15:"board_filetypes";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}}', 1, 1324341088),
('attachments:filetypes', 'a:4:{i:0;O:8:"stdClass":7:{s:7:"type_id";s:1:"1";s:8:"type_ext";s:3:"jpg";s:9:"type_mime";s:9:"image/jpg";s:10:"type_image";s:0:"";s:16:"type_image_width";s:1:"0";s:17:"type_image_height";s:1:"0";s:16:"type_force_thumb";s:1:"0";}i:1;O:8:"stdClass":7:{s:7:"type_id";s:1:"2";s:8:"type_ext";s:3:"png";s:9:"type_mime";s:9:"image/png";s:10:"type_image";s:0:"";s:16:"type_image_width";s:1:"0";s:17:"type_image_height";s:1:"0";s:16:"type_force_thumb";s:1:"0";}i:2;O:8:"stdClass":7:{s:7:"type_id";s:1:"3";s:8:"type_ext";s:3:"gif";s:9:"type_mime";s:9:"image/gif";s:10:"type_image";s:0:"";s:16:"type_image_width";s:1:"0";s:17:"type_image_height";s:1:"0";s:16:"type_force_thumb";s:1:"0";}i:3;O:8:"stdClass":7:{s:7:"type_id";s:1:"6";s:8:"type_ext";s:4:"test";s:9:"type_mime";s:4:"test";s:10:"type_image";s:0:"";s:16:"type_image_width";s:1:"0";s:17:"type_image_height";s:1:"0";s:16:"type_force_thumb";s:1:"1";}}', 1, 1324507593),
('boardopts:s1b2', 'a:1:{i:0;O:8:"stdClass":35:{s:8:"board_id";s:1:"2";s:11:"board_order";s:1:"2";s:10:"board_name";s:4:"s1b2";s:10:"board_type";s:1:"0";s:11:"board_start";s:1:"0";s:17:"board_upload_type";s:1:"2";s:10:"board_desc";s:12:"Board 2 (S1)";s:18:"board_header_image";s:0:"";s:13:"board_section";s:1:"1";s:21:"board_max_upload_size";s:7:"1024000";s:15:"board_max_pages";s:2:"11";s:13:"board_max_age";s:1:"0";s:15:"board_mark_page";s:1:"9";s:17:"board_max_replies";s:3:"200";s:24:"board_max_message_length";s:4:"8192";s:16:"board_created_on";s:1:"0";s:12:"board_locked";s:1:"0";s:20:"board_include_header";s:0:"";s:24:"board_redirect_to_thread";s:1:"0";s:15:"board_anonymous";s:9:"Anonymous";s:17:"board_forced_anon";s:1:"0";s:20:"board_allowed_embeds";s:0:"";s:11:"board_trial";s:1:"0";s:13:"board_popular";s:1:"0";s:19:"board_default_style";s:5:"edaha";s:12:"board_locale";s:0:"";s:13:"board_show_id";s:1:"0";s:18:"board_compact_list";s:1:"0";s:15:"board_reporting";s:1:"1";s:13:"board_captcha";s:1:"0";s:13:"board_no_file";s:1:"0";s:15:"board_archiving";s:1:"0";s:13:"board_catalog";s:1:"1";s:15:"board_max_files";s:1:"1";s:15:"board_filetypes";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}}', 1, 1324341075),
('boardopts:s2b1', 'a:1:{i:0;O:8:"stdClass":35:{s:8:"board_id";s:1:"3";s:11:"board_order";s:1:"1";s:10:"board_name";s:4:"s2b1";s:10:"board_type";s:1:"0";s:11:"board_start";s:1:"0";s:17:"board_upload_type";s:1:"2";s:10:"board_desc";s:12:"Board 1 (S2)";s:18:"board_header_image";s:0:"";s:13:"board_section";s:1:"2";s:21:"board_max_upload_size";s:7:"1024000";s:15:"board_max_pages";s:2:"11";s:13:"board_max_age";s:1:"0";s:15:"board_mark_page";s:1:"9";s:17:"board_max_replies";s:3:"200";s:24:"board_max_message_length";s:4:"8192";s:16:"board_created_on";s:1:"0";s:12:"board_locked";s:1:"0";s:20:"board_include_header";s:0:"";s:24:"board_redirect_to_thread";s:1:"0";s:15:"board_anonymous";s:9:"Anonymous";s:17:"board_forced_anon";s:1:"0";s:20:"board_allowed_embeds";s:0:"";s:11:"board_trial";s:1:"0";s:13:"board_popular";s:1:"0";s:19:"board_default_style";s:5:"edaha";s:12:"board_locale";s:0:"";s:13:"board_show_id";s:1:"0";s:18:"board_compact_list";s:1:"0";s:15:"board_reporting";s:1:"1";s:13:"board_captcha";s:1:"0";s:13:"board_no_file";s:1:"0";s:15:"board_archiving";s:1:"0";s:13:"board_catalog";s:1:"1";s:15:"board_max_files";s:1:"1";s:15:"board_filetypes";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}}', 1, 1324341100),
('boardopts:s2b2', 'a:1:{i:0;O:8:"stdClass":35:{s:8:"board_id";s:1:"4";s:11:"board_order";s:1:"2";s:10:"board_name";s:4:"s2b2";s:10:"board_type";s:1:"0";s:11:"board_start";s:1:"0";s:17:"board_upload_type";s:1:"2";s:10:"board_desc";s:12:"Board 2 (S2)";s:18:"board_header_image";s:0:"";s:13:"board_section";s:1:"2";s:21:"board_max_upload_size";s:7:"1024000";s:15:"board_max_pages";s:2:"11";s:13:"board_max_age";s:1:"0";s:15:"board_mark_page";s:1:"9";s:17:"board_max_replies";s:3:"200";s:24:"board_max_message_length";s:4:"8192";s:16:"board_created_on";s:1:"0";s:12:"board_locked";s:1:"0";s:20:"board_include_header";s:0:"";s:24:"board_redirect_to_thread";s:1:"0";s:15:"board_anonymous";s:9:"Anonymous";s:17:"board_forced_anon";s:1:"0";s:20:"board_allowed_embeds";s:0:"";s:11:"board_trial";s:1:"0";s:13:"board_popular";s:1:"0";s:19:"board_default_style";s:5:"edaha";s:12:"board_locale";s:0:"";s:13:"board_show_id";s:1:"0";s:18:"board_compact_list";s:1:"0";s:15:"board_reporting";s:1:"1";s:13:"board_captcha";s:1:"0";s:13:"board_no_file";s:1:"0";s:15:"board_archiving";s:1:"0";s:13:"board_catalog";s:1:"1";s:15:"board_max_files";s:1:"1";s:15:"board_filetypes";a:3:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";}}}', 1, 1324341110);

-- --------------------------------------------------------

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

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

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `name` varchar(255) NOT NULL,
  `at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `filetypes`
--

CREATE TABLE IF NOT EXISTS `filetypes` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_ext` varchar(255) NOT NULL,
  `type_mime` varchar(255) DEFAULT NULL,
  `type_image` varchar(255) DEFAULT NULL,
  `type_image_width` int(11) NOT NULL DEFAULT '0',
  `type_image_height` int(11) NOT NULL DEFAULT '0',
  `type_force_thumb` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_ext` (`type_ext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `filetypes`
--

INSERT INTO `filetypes` (`type_id`, `type_ext`, `type_mime`, `type_image`, `type_image_width`, `type_image_height`, `type_force_thumb`) VALUES
(1, 'jpg', 'image/jpg', '', 0, 0, 0),
(2, 'png', 'image/png', '', 0, 0, 0),
(3, 'gif', 'image/gif', '', 0, 0, 0),
(6, 'test', 'test', '', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `filters`
--

CREATE TABLE IF NOT EXISTS `filters` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_word` varchar(75) NOT NULL,
  `filter_type` tinyint(1) NOT NULL,
  `filter_added` int(11) NOT NULL,
  `filter_regex` tinyint(1) DEFAULT '0',
  `filter_replacement` text,
  `filter_ban_duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `filters`
--

INSERT INTO `filters` (`filter_id`, `filter_word`, `filter_type`, `filter_added`, `filter_regex`, `filter_replacement`, `filter_ban_duration`) VALUES
(1, 'test', 3, 1324506346, 0, 'testicle', NULL),
(5, 'cialis', 12, 1324509219, 0, '', NULL);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `front`
--

INSERT INTO `front` (`entry_id`, `entry_type`, `entry_order`, `entry_subject`, `entry_message`, `entry_time`, `entry_name`, `entry_email`) VALUES
(3, 0, 0, 'Kusaba 1.0', 'Kusaba X 1.0 front works. :)', 1309157915, 'admin', ''),
(4, 1, 0, 'FAQ', 'Works', 0, '', ''),
(5, 2, 0, 'Rules', 'Works', 0, '', ''),
(6, 0, 0, 'Example News Post', 'An example news post', 1323642137, 'admin', 'admin@localhost'),
(7, 0, 0, 'Another Example News Post', 'Yet another example news post', 1323640000, 'admin', '');

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

-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE IF NOT EXISTS `loginattempts` (
  `attempt_name` varchar(255) NOT NULL,
  `attempt_ip` varchar(20) NOT NULL,
  `attempt_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

--
-- Dumping data for table `manage_sessions`
--

INSERT INTO `manage_sessions` (`session_id`, `session_ip`, `session_staff_id`, `session_location`, `session_name`, `session_log_in_time`, `session_last_action`, `session_url`) VALUES
('ce26b291bc17b2a5dcc442382589759b', '127.0.0.1', 1, 'index', '', 1324529593, 1324529593, '');

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE IF NOT EXISTS `maps` (
  `mapname` varchar(255) DEFAULT NULL,
  `mapval` varchar(5) DEFAULT NULL,
  `ip` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

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
(9, 'Bans', 'core', 'bans', 'Provides functionality for adding and editing bans.', 0, 1),
(10, 'Board', 'board', 'board', 'Allows setting of board options', 0, 1),
(11, 'Filters', 'board', 'filter', 'Provides filtering options', 0, 1),
(12, 'Attachment Options', 'board', 'attachments', 'Provides tools for adding, editing, and removing available post attachments.', 0, 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL,
  `section_order` smallint(6) DEFAULT NULL,
  `section_hidden` smallint(6) DEFAULT '0',
  `section_name` varchar(255) DEFAULT '0',
  `section_abbreviation` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_order`, `section_hidden`, `section_name`, `section_abbreviation`) VALUES
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`user_id`, `user_name`, `user_password`, `user_salt`, `user_type`, `user_boards`, `user_add_time`, `user_last_active`) VALUES
(1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', '', 1, NULL, 0, 0),
(3, 'test', '2fd789c9968b5e2074181b62c98d218e', 'ae48f', 1, NULL, 1323880531, 0);

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
