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

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `name` varchar(255) NOT NULL,
  `at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE IF NOT EXISTS `loginattempts` (
  `attempt_name` varchar(255) NOT NULL,
  `attempt_ip` varchar(20) NOT NULL,
  `attempt_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `manage_sessions`
--

CREATE TABLE IF NOT EXISTS `manage_sessions` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `session_ip` varchar(32) NOT NULL DEFAULT '',
  `session_staff_id` mediumint(8) NOT NULL DEFAULT '0',
  `session_location` varchar(64) NOT NULL DEFAULT '',
  `session_log_in_time` int(10) NOT NULL DEFAULT '0',
  `session_last_action` int(10) NOT NULL DEFAULT '0',
  `session_url` text,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE IF NOT EXISTS `maps` (
  `mapname` varchar(255) DEFAULT NULL,
  `mapval` varchar(5) DEFAULT NULL,
  `ip` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_name`, `module_application`, `module_file`, `module_description`, `module_position`, `module_manage`) VALUES
(2, 'Image Board', 'board', 'image', 'Generator for an image-type board', 0, 0),
(3, 'Oekaki Board', 'board', 'oekaki', 'Generator for an oekaki-type board', 0, 0),
(4, 'Upload Board', 'board', 'upload', 'Generator for an upload-type board', 0, 0),
(5, 'Text Board', 'board', 'text', 'Generator for a text board', 0, 0),
(6, 'Index', 'core', 'index', 'Hanles the manage page index', 0, 1),
(7, 'Index', 'core', 'index', 'Handles the front page features (news, faq, etc)', 0, 0);

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
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_salt` varchar(3) NOT NULL,
  `user_type` smallint(6) DEFAULT '0',
  `user_boards` text,
  `user_add_time` int(11) NOT NULL,
  `user_last_active` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
