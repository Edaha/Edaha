<?php 
class Posting {
  protected $environment;
  protected $db;

  public function __construct( kxEnv $environment )
	{
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
                            ->fetchCol();
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
  public function makePost($postData, $post, $files, $ip, $stickied, $locked, $boardid) {

      $id = $this->db->insert("posts")
                     ->fields(array(
                       'post_parent'    => $postData['thread_info']['parent'],
                       'post_board'     => $boardid,
                       'post_name'      => $post['name'],
                       'post_tripcode'  => $post['tripcode'],
                       'post_email'     => $post['email'],
                       'post_subject'   => $post['subject'],
                       'post_message'   => $post['message'],
                       'post_password'  => $postData['post_fields']['postpassword'],
                       'post_timestamp' => time(),
                       'post_bumped'    => time(),
                       'post_ip'        => kxFunc::encryptMD5($ip, kxEnv::Get('kx:misc:randomseed')),
                       'post_ip_md5'    => md5($ip),
                       'post_authority' => $postData['user_authority_display'],
                       'post_tag'       => isset($post['tag']) ? $post['tag'] : '',
                       'post_stickied'  => $stickied,
                       'post_locked'    => $locked
                     ))
                     ->execute();
/*      if(!$id || kxEnv::Get('kx:db:type') == 'sqlite') {
        // Non-mysql installs don't return the insert ID after insertion, we need to manually get it.
        $id = $kx_db->GetOne("SELECT `id` FROM `".kxEnv::Get('kx:db:prefix')."posts` WHERE `boardid` = ".$kx_db->qstr($boardid)." AND timestamp = ".$kx_db->qstr($timestamp)." AND `ipmd5` = '".md5($ip)."' LIMIT 1");
      }
      if ($id == 1 && $this->board['start'] > 1) {
        $kx_db->Execute("UPDATE `".kxEnv::Get('kx:db:prefix')."posts` SET `id` = '".$this->board['start']."' WHERE `boardid` = ".$boardid);
        $id = $this->board['start'];
      }
      */
      
      if (!empty($files)) {
        foreach ($files as $file) {
          $this->db->insert("post_files")
                   ->fields(array(
                     'file_post'           => $id,
                     'file_board'          => $boardid,
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