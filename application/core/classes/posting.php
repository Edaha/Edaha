<?php 
class Posting {
    protected $environment;
    protected $db;
    
    public function __construct( kxEnv $environment ) {
        $this->environment = $environment;
        $this->db = kxDB::getInstance();
        $this->request = kxEnv::$request;
    }
    public function checkUTF8() {
        if (function_exists('mb_convert_encoding') && function_exists('mb_check_encoding')) {
            if (isset($_POST['name']) && !mb_check_encoding($_POST['name'], 'UTF-8')) {
                $_POST['name'] = mb_convert_encoding($_POST['name'], 'UTF-8');
            }
            if (isset($_POST['em']) && !mb_check_encoding($_POST['em'], 'UTF-8')) {
                $_POST['em'] = mb_convert_encoding($_POST['em'], 'UTF-8');
            }
            if (isset($_POST['subject']) && !mb_check_encoding($_POST['subject'], 'UTF-8')) {
                $_POST['subject'] = mb_convert_encoding($_POST['subject'], 'UTF-8');
            }
            if (isset($_POST['message']) && !mb_check_encoding($_POST['message'], 'UTF-8')) {
                $_POST['message'] = mb_convert_encoding($_POST['message'], 'UTF-8');
            }
        }
    }
    public function isReply($boardid) {
        /* If it appears this is a reply to a thread, and not a new thread... */
        if (isset($this->request['replythread'])) {
            if ($this->request['replythread'] != 0) {
                /* Check if the thread id supplied really exists */
                $results = $this->db->select("posts")
                    ->fields("posts")
                    ->countQuery()
                    ->condition("post_board", $boardid)
                    ->condition("post_id", $this->request['replythread'])
                    ->condition("post_parent", 0)
                    ->condition("post_deleted", 0)
                    ->execute
                    ->fetchField();
                /* If it does... */
                if ($results > 0) {
                    return true;
                    /* If it doesn't... */
                } else {
                    /* Kill the script, stopping the posting process */
                    kxFunc::showError(_gettext('Invalid thread ID.'), _gettext('That thread may have been recently deleted.'));
                }
            }
        }
        
        return false;
    }
    public function checkEmbed($postData) {
        if(empty($this->request['embed']) && empty($postData['files'][0])) {
            return false;
        }
    }
    public function checkEmpty($postData) {
        if (is_array($postData['files']) && empty($postData['files'][0]) && empty($postData['message'])) {
            return false;
        }
    }
    public function checkNoFile($postData) {
        if (empty($postData['message'])) {
            return false;
        }
    }
    public function forcedAnon(&$postData, $board) {
        if ($board->board_forced_anonynous == 0) {
            if ($postData['user_authority'] == 0 || $postData['user_authority'] == 3) {
                $postData['post_fields']['name'] = '';
            }
        }
    }
    public function handleTripcode($postData) {
        //$nameandtripcode = kxFunc::calculateNameAndTripcode($postData['post_fields']['name']);
        if (is_array($nameandtripcode)) {
            $name = $nameandtripcode[0];
            $tripcode = $nameandtripcode[1];
        } else {
            $name = $postData['post_fields']['name'];
            $tripcode = '';
        }
        return array($name, $tripcode);
    }
    public function checkPostCommands(&$postData) {
        $commands = $this->checkStickyAndLock($postData);
        
        if (!$postData['display_status'] && $postData['user_authority'] > 0 && $postData['user_authority'] != 3) {
            $postData['user_authority_display'] = 0;
        } elseif ($postData['user_authority'] > 0) {
            $postData['user_authority_display'] = $postData['user_authority'];
        } else {
            $postData['user_authority_display'] = 0;
        }
        return $commands;
    }
    public function checkStickyAndLock($postData) {
        $result = Array('sticky' => 0, 'lock' => 0);
        if ($postData['thread_info']['parent'] == 0) {
            $result = Array('sticky' => $postData['sticky_on_post'], 'lock' => $postData['lock_on_post']);
        }
        else {
            $fields = array();
            if ($postData['sticky_on_post']) {
                $fields['stickied'] = 1;
            }
            if ($postData['lock_on_post']) {
                $fields['locked'] = 1;
            }
            if ($fields) {
                $this->db->update("posts")
                ->fields($fields)
                ->condition("post_board", $this->board->board_id)
                ->condition("post_id", $postData['thread_info']['parent'])
                ->execute();
            }
        }
        return $result;
    }
    public function checkEmptyReply(&$postData) {
        if ($postData['thread_info']['parent'] != 0) {
            if ($postData['thread_info']['message'] == '' && kxEnv::Get('kx:posts:emptyreply') != '') {
                $postData['thread_info']['message'] = kxEnv::Get('kx:posts:emptyreply');
            }
        } else {
            if ($postData['thread_info']['message'] == '' && kxEnv::Get('kx:posts:emptythread') != '') {
                $postData['thread_info']['message'] = kxEnv::Get('kx:posts:emptythread');
            }
        }
    }
    public function checkOekaki() {
        // If oekaki seems to be in the url...
        if (!empty($this->request['oekaki'])) {
            $oekpath = kxEnv::Get('kx:paths:boards:folder') . $this->request['board'] . '/tmp/' . $this->request['oekaki'] . '.png';
            /* See if it checks out and is a valid oekaki id */
            if (is_file($oekpath) ) {
                /* Set the variable to tell the script it is handling an oekaki posting, and the oekaki file which will be posted */
                return $oekpath;
            }
        }
        
        return false;
    }
    public function getPostTag() {
        /* Check for and parse tags if one was provided, and they are enabled */
        $tags = unserialize(kxEnv::Get('kx:tags'));
        if (!empty($tags) && !empty($this->request['tag']) && in_array($this->request['tag'], $tags) ) {
            return $this->request['tag'];
        }
        return false;
    }
    public function modPost($post, $board) {
        if ($postData['user_authority'] > 0 && $postData['user_authority'] != 3) {
            $modpost_message = 'Modposted #<a href="' . kxEnv::Get('kx:paths:boards:folder') . $board->board_name . '/res/';
            if ($postData['is_reply']) {
                $modpost_message .= $postData['thread_info']['parent'];
            } else {
                $modpost_message .= $post['post_id'];
            }
            $modpost_message .= '.html#' . $post['post_id'] . '">' . $post['post_id'] . '</a> in /'. $this->board->board_name .'/ with flags: ' . $postData['flags'] . '.';
            management_addlogentry($modpost_message, 1, md5_decrypt($this->request['modpassword'], kxEnv::Get('kx:misc:randomseed')));
        }
    }
    public function setCookies($post) {
        if ($post['name_save'] && isset($this->request['name'])) {
            setcookie('name', urldecode($this->request['name']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
        }
        
        if ($post['email_save']) {
            setcookie('email', urldecode($post['email']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
        }
        
        setcookie('postpassword', urldecode($this->request['postpassword']), time() + 31556926, '/');
    }
    public function checkSage($postData, $board) {
        // If the user replied to a thread, and they weren't sage-ing it...
        if ($postData['thread_info']['parent'] != 0 && strtolower($this->request['em']) != 'sage' /*&& unistr_to_ords($_POST['em']) != array(19979, 12370)*/) {
            // And if the number of replies already in the thread are less than the maximum thread replies before perma-sage...
            if ($postData['thread_info']['replies'] <= $board->board_maxreplies) {
                // Bump the thread
                $this->db->update("posts")
                ->fields(array("post_bumped" => time()))
                ->condition("post_board", $boardboard_id)
                ->condition("id", $postData['thread_info']['parent'])
                ->execute();
            }
        }
    }
    public function updateThreadWatch($postData, $board) {
        // If the user replied to a thread he is watching, update it so it doesn't count his reply as unread
        if (kxEnv::Get('ku:extras:watchthreads') && $postData['thread_info']['parent'] != 0) {
            $viewing_thread_is_watched = $this->db->select("watchedthreads")
                ->fields(array("watchedthreads"))
                ->countQuery()
                ->condition("watch_ip", $_SERVER['REMOTE_ADDR'])
                ->condition("watch_board", $board->board_name)
                ->condition("watch_thread", $postData['thread_info']['parent'] != 0)
                ->execute()
                ->fetchField();
            if ($viewing_thread_is_watched[0] > 0) {
                $newestreplyid = $this->db->select("posts")
                    ->fields("posts", array("post_id"))
                    ->condition("post_board", $board->board_id)
                    ->condition("post_deleted", 0)
                    ->condition("post_parent", $postData['thread_info']['parent']) 
                    ->orderBy("post_id", "DESC")
                    ->range(0, 1)
                    ->execute()
                    ->fetchField();
                
                $this->db->update("watchedthreads")
                ->fields(array("watch_last_id_seen" => $newestreplyid))
                ->condition("watch_ip", $_SERVER['REMOTE_ADDR'])
                ->condition("watch_board", $board->board_name)
                ->condition("watch_thread", $postData['thread_info']['parent'])
                ->execute();
            }
        }
    }
    public function doUpload(&$postData, $board) {
        $uploadClass = $this->environment->get('kx:classes:board:upload:id');
        
        if ((!isset($this->request['nofile']) && $board->board_enable_no_file == 1) || $board->board_enable_no_file == 0) {
            $uploadClass->HandleUpload($postData, $board);
        }
        if (!$uploadClass->isvideo) {
            foreach ($uploadClass->files as $key=>$file){
                if (!file_exists(KX_BOARD . '/' . $board->board_name . '/src/' . $file['file_name'] . $file['file_type']) || !$file['file_is_special'] && !file_exists(KX_BOARD . '/' . $board->board_name . '/thumb/' . $file['file_name'] . 's' . $file['file_type'])) {
                    exitWithErrorPage(_gettext('Could not copy uploaded image.'));
                }
            }
        }
        if (isset($postData['is_oekaki']) && $postData['is_oekaki']) {
            if (file_exists(KX_BOARD . '/' . $board->board_name . '/src/' . $uploadClass->files[0]['file_name'] . '.pch')) {
                $postData['thread_info']['message'] .= '<br /><small><a href="' . KX_SCRIPT . '/animation.php?board=' . $board->board_name . '&amp;id=' . $uploadClass->files[0]['file_name'] . '">' . _gettext('View animation') . '</a></small>';
            }
        }
        return $uploadClass->files;
    }
    public function parseFields() {
        /* Fetch and process the name, email, and subject fields from the post data */
        $post_name = isset($this->request['name']) ? htmlspecialchars($this->request['name'], ENT_QUOTES) : '';
        $post_email = isset($this->request['em']) ? str_replace('"', '', strip_tags($this->request['em'])) : '';
        /* If the user used a software function, don't store it in the database */
        if ($post_email == 'return' || $post_email == 'noko') $post_email = '';
        $post_subject = isset($this->request['subject']) ? htmlspecialchars($this->request['subject'], ENT_QUOTES) : '';
        
        return array("name" => $post_name, "email" => $post_email, "subject" => $post_subject);
    }
    public function userAuthority() {
        $user_authority = 0;
        
        if (isset($this->request['modpassword'])) {
            
            $results = $kx_db->GetAll("SELECT `type`, `boards` FROM `" . kxEnv::Get('kx:db:prefix') . "staff` WHERE `username` = '" . md5_decrypt($_POST['modpassword'], kxEnv::Get('kx:misc:randomseed')) . "' LIMIT 1");
            
            if (count($results) > 0) {
                if ($results[0][0] == 1) {
                    $user_authority = 1; // admin
                } elseif ($results[0][0] == 2 && in_array($board_class->board['name'], explode('|', $results[0][1]))) {
                    $user_authority = 2; // mod
                } elseif ($results[0][0] == 2 && $results[0][1] == 'allboards') {
                    $user_authority = 2;
                }
            }
        }
        
        return $user_authority;
    }
    public function makePost($postData, $post, $files, $ip, $stickied, $locked, $board) {
        
        $timeStamp = time();
        $id = $this->db->insert("posts")
            ->fields(array(
                'post_parent'    => $postData['thread_info']['parent'],
                'post_board'     => $board->board_id,
                'post_name'      => $post['name'],
                'post_tripcode'  => $post['tripcode'],
                'post_email'     => $post['email'],
                'post_subject'   => $post['subject'],
                'post_message'   => $post['message'],
                'post_password'  => $postData['post_fields']['postpassword'],
                'post_timestamp' => $timeStamp,
                'post_bumped'    => $timeStamp,
                'post_ip'        => kxFunc::encryptMD5($ip, kxEnv::Get('kx:misc:randomseed')),
                'post_ip_md5'    => md5($ip),
                'post_authority' => $postData['user_authority_display'],
                'post_tag'       => isset($post['tag']) ? $post['tag'] : '',
                'post_stickied'  => $stickied,
                'post_locked'    => $locked
            ))
            ->execute();

            if(!$id || kxEnv::Get('kx:db:type') == 'sqlite') {
              // Non-mysql installs don't return the insert ID after insertion, we need to manually get it.
              $id = $this->db->select("posts")
                             ->fields("posts", array("post_id"))
                             ->condition("post_board", $board->board_id )
                             ->condition("post_timestamp", $timeStamp)
                             ->condition("post_ip_md5", md5($ip))
                             ->range(0,1)
                             ->execute()
                             ->fetchField();
            }
            
            if ($id == 1 && $board->board_start > 1) {
                $this->db->update("posts")
                ->fields(array("id" => $board->board_start))
                ->condition("post_board", $board->board_id)
                ->execute();
              $id = $board->board_start;
            }
        
        if (!empty($files)) {
            foreach ($files as $file) {
                $this->db->insert("post_files")
                ->fields(array(
                    'file_post'           => $id,
                    'file_board'          => $board->board_id,
                    'file_md5'            => $file['file_md5'],
                    'file_name'           => $file['file_name'],
                    'file_type'           => substr($file['file_type'], 1),
                    'file_original'       => $file['original_file_name'],
                    'file_size'           => $file['file_size'],
                    'file_size_formatted' => /*kxFunc::convertBytes($file['file_size'])*/$file['file_size'],
                    'file_image_width'    => $file['image_w'],
                    'file_image_height'   => $file['image_h'],
                    'file_thumb_width'    => $file['thumb_w'],
                    'file_thumb_height'   => $file['thumb_h']
                ))
                ->execute();
            }
        }
        else {
            $this->db->insert("post_files")
            ->fields(array(
                'file_post'           => $id,
                'file_board'          => $boardid,
                'file_md5'            => '',
                'file_name'           => $file['file_name'],
                'file_type'           => '',
                'file_original'       => '',
                'file_size'           => 0,
                'file_size_formatted' => '',
                'file_image_width'    => 0,
                'file_image_height'   => 0,
                'file_thumb_width'    => 0,
                'file_thumb_height'   => 0
            ))
            ->execute();
        }
        return $id;
    }
}
?>