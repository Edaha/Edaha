<?php
class Posting
{
  protected $environment;
  protected $db;
  protected $request;
  protected $entityManager;

  public function __construct(kxEnv $environment)
  {
    $this->environment = $environment;
    $this->db = kxDB::getInstance();
    $this->request = kxEnv::$request;
    $this->entityManager = kxOrm::getEntityManager();
  }
  public function checkUtf8()
  {
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

  /**
   * Retrieves information about a thread, including the number of replies, 
   * whether the thread is locked, and its parent post ID.
   *
   * @param int $boardID The ID of the board where the thread is located.
   * @param int $threadID The ID of the thread to retrieve information for.
   * @return array An associative array containing:
   *               - 'replies' (int): The number of replies in the thread.
   *               - 'locked' (int): Whether the thread is locked (default is 0).
   *               - 'parent' (int): The parent post ID of the thread.
   */
  public function threadInfo($boardID, $threadID)
  {
    $threadData = array('replies' => 0, 'locked' => 0, 'parent' => 0);
    $sql = $this->db->select('posts');
    $sql->addExpression('COUNT(*)');
    $threadData['replies'] = $sql->condition('board_id', $boardID)
      ->condition('parent_post_id', $threadID)
      ->condition('is_deleted', 0)
      ->execute()
      ->fetchField();
    $threadData['parent'] = $threadID;
    return $threadData;
  }

  public function isReply($boardid)
  {
    /* If it appears this is a reply to a thread, and not a new thread... */
    if (isset($this->request['replythread'])) {
      if ((int) $this->request['replythread'] != 0) {
        /* Check if the thread id supplied really exists */
        $thread = $this->entityManager->find(Edaha\Entities\Post::class, $this->request['replythread']);
        if ($thread == null) {
          /* Kill the script, stopping the posting process */
          kxFunc::showError(_('Invalid thread ID.'), _('That thread may have been recently deleted.'));
        } else {
          return true;
        }
      }
    }

    return false;
  }
  public function checkEmbed($postData)
  {
    if (empty($this->request['embed']) && empty($postData['files'][0])) {
      return false;
    }
  }

  /**
   * Checks if the post data is empty.
   *
   * @param array $postData The data of the post being validated.
   * @return bool Always returns true (placeholder for future validation logic).
   */
  public function checkEmpty($postData)
  {
    // TODO Move to Post Validator
    // if (is_array($postData['files']) && empty($postData['files'][0]) && empty($postData['thread_info']['message'])) {
    //   return false;
    // }
    return true;
  }

  /**
   * Checks if the post contains no file (supposedly) but actually if there's no message.
   *
   * @param array $postData The data of the post being validated.
   * @return bool Returns false if the message is empty, otherwise null.
   */
  public function checkNoFile($postData)
  {
    if (empty($postData['message'])) {
      return false;
    }
  }

  /**
   * Forces anonymity for the post if the board requires it.
   *
   * @param array $postData The data of the post being modified.
   * @param object $board The board object containing board settings.
   */
  public function forcedAnon(&$postData, $board)
  {
    if ($board->board_forced_anon == 0) {
      if ($postData['user_authority'] == 0 || $postData['user_authority'] == 3) {
        $postData['post_fields']['name'] = '';
      }
    }
  }

  /**
   * Handles the calculation of the name and tripcode (stubbed) for a post.
   *
   * @param array $postData The data of the post being processed.
   * @return array An array containing the name and tripcode.
   */
  public function handleTripcode($postData)
  {
    $nameandtripcode = ''; //$nameandtripcode = kxFunc::calculateNameAndTripcode($postData['post_fields']['name']);
    if (is_array($nameandtripcode)) {
      $name = $nameandtripcode[0];
      $tripcode = $nameandtripcode[1];
    } else {
      $name = $postData['post_fields']['name'];
      $tripcode = '';
    }
    return array($name, $tripcode);
  }

  /**
   * Checks and processes post commands, including sticky and lock status.
   *
   * @param array $postData The data of the post being processed.
   * @return array An array containing the sticky and lock status.
   */
  public function checkPostCommands(&$postData)
  {
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

  /**
   * Checks if the sticky-on-post or lock-on-post commands were set
   * and then processes the sticky and lock status of a post.
   *
   * @param array $postData The data of the post being processed.
   * @return array An array containing the sticky and lock status.
   */
  public function checkStickyAndLock($postData)
  {
    $result = array('sticky' => 0, 'lock' => 0);
    if ($postData['thread_info']['parent'] == 0) {
      $result = array('sticky' => $postData['sticky_on_post'], 'lock' => $postData['lock_on_post']);
    } else {
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
          ->condition("board_id", $this->board->board_id)
          ->condition("post_id", $postData['thread_info']['parent'])
          ->execute();
      }
    }
    return $result;
  }

  /**
   * Checks if the reply is empty and sets a default message if configured.
   *
   * @param array $postData The data of the post being processed.
   */
  public function checkEmptyReply(&$postData)
  {
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

  /**
   * Checks if the user is posting too quickly and enforces a delay.
   *
   * @param bool $isReply Indicates if the post is a reply.
   * @param int $boardId The ID of the board where the post is being made.
   */
  public function checkPostingTime($isReply, $boardId)
  {
    // Generate the query needed
    $limit = $isReply ? $this->environment->get("kx:limits:replydelay") : kxEnv::Get("kx:limits:threaddelay");
    $cutoff_time = date('Y-m-d H:i:s', time() - $limit);

    $result = $this->db->select("posts")
      ->condition("board_id", $boardId)
      ->condition("ip_md5", md5($_SERVER['REMOTE_ADDR']))
      ->condition("created_at_timestamp", $cutoff_time, ">");
    $result = $isReply ? $result->condition("parent_post_id", 0, "!=") : $result->condition("parent_post_id", 0, "=");
    $result = $result->countQuery()
      ->execute()
      ->fetchField();
    if ($result > 0) {
      kxFunc::showError(_('Please wait a moment before posting again.'), _('You are currently posting faster than the configured minimum post delay allows.'));
    }
  }

  /**
   * Checks the length of the message and enforces a maximum length.
   *
   * @param int $maxMessageLength The maximum allowed message length.
   */
  public function checkMessageLength($maxMessageLength)
  {
    // If the length of the message is greater than the board's maximum message length...
    if (strlen($this->request['message']) > $maxMessageLength) {
      // Kill the script, stopping the posting process
      kxFunc::showError(sprintf(_('Sorry, your message is too long. Message length: %d, maximum allowed length: %d'), strlen($this->request['message']), $maxMessageLength));
    }
  }

  /**
   * Checks if the captcha is valid and verifies it.
   *
   * @param object $board The board object containing board settings.
   * @param array $postData The data of the post being processed.
   */
  public function checkCaptcha($board, $postData)
  {
    //TODO: This NEEDS to be looked to see if it can be fit somewhere better
    // Do we have captcha?
    if ($board->board_captcha == 1) {
      // Use the old captcha for text board replies because reCaptcha doesnt like having more than one captcha
      // on a page (I don't really like checking board types here, but I can't think of a better way to do this)
      // TODO: Replace kusaba captcha with something better (like KCaptcha). The Kusaba captcha is incredibly
      // broken and possibly exploitable!
      if ($board->board_type == 1 && $postData['is_reply']) {
        // Check if they entered the correct code. If not...
        if ($_SESSION['security_code'] != strtolower($this->request['captcha']) || empty($_SESSION['security_code'])) {
          // Kill the script, stopping the posting process
          kxFunc::showError(_('Incorrect captcha entered.'));
        }
        unset($_SESSION['security_code']);
      } else {
        require_once KX_ROOT . '/application/lib/recaptcha/recaptchalib.php';
        $privatekey = "6LdVg8YSAAAAALayugP2r148EEQAogHPfQOSYow-";

        // was there a reCAPTCHA response?
        $resp = recaptcha_check_answer($privatekey,
          $_SERVER["REMOTE_ADDR"],
          $this->request["recaptcha_challenge_field"],
          $this->request["recaptcha_response_field"]
        );
        if (!$resp->is_valid) {
          // Show error and give user opportunity to try again.
          kxFunc::showError(_('Incorrect captcha entered.'));
        }
      }
    }
  }

  /**
   * Checks if the file hash is banned and bans the user if it is.
   *
   * @param object $board The board object containing board settings.
   */
  public function checkBannedHash($board)
  {
    // Banned file hash check
    if (isset($_FILES['imagefile'])) {
      if ($_FILES['imagefile']['name'][0] != '') {
        $results = $this->db->select("bannedhashes")
          ->fields("bannedhashes", array("banduration", "description"))
          ->where("md5 = ?")
          ->range(0, 1)
          ->build();
        for ($i = 0; $i < $board->board_max_files; $i++) {
          if (isset($_FILES['imagefile']['tmp_name'][$i]) && $_FILES['imagefile']['tmp_name'][$i]) {
            $results->execute(array(md5_file($_FILES['imagefile']['tmp_name'][$i])));
            if (count($results->fetchAll()) > 0) {
              kxBans::banUser($_SERVER['REMOTE_ADDR'], 'SERVER', '1', $results[0]->banduration, '', 'Posting a banned file.<br />' . $results[0]->description, '', 0, 0, 1);
              kxBans::banCheck($_SERVER['REMOTE_ADDR'], $board->board_name);
              exit;
            }
          } else {
            // The file didn't get uploaded, or no file after the previous was uploaded.
            // Either way, break the loop, if there's a problem, upload class will take care of it.
            break;
          }
        }
      }
    }
  }

  /**
   * Checks if the post contains blacklisted text and takes appropriate action.
   *
   * @param int $boardId The ID of the board where the post is being made.
   */
  public function checkBlacklistedText($boardId)
  {
    // TODO Revisit filters operation
    /*$filters = $this->db->select("filters")
  ->fields("filters")
  ->condition("filter_type", 2, ">=")
  ->orderBy("filter_type", "DESC")
  ->execute()
  ->fetchAll();

  $reported = 0;
  if (isset($filters) && count($filters) > 0) {
  foreach ($filters as $filter) {
  if ((!$filter->filter_boards || in_array($boardId, unserialize($filter->filter_boards))) && (!$filter->filter_regex && stripos($this->request['message'], $filter->filter_word) !== false) || ($filter->filter_regex && preg_match($filter->filter_word, $this->request['message']))) {
  // They included blacklisted text in their post. What do we do?
  if ($filter->filter_type & 8) {
  // Ban them if they have the ban flag set on this filter
  $punishment = unserialize($filter->filter_punishment);
  kxBans::banUser($_SERVER['REMOTE_ADDR'], 'board.php', 1, $punishment['banlength'], $filter->filter_boards, _('Posting blacklisted text.') . ' (' . $filter . ')', $this->request['message']);
  }
  if ($filter->filter_type & 4) {
  // Stop the post from happening if the delete flag is set
  kxFunc::showError(sprintf(_('Blacklisted text ( %s ) detected.'), $filter));
  }
  if ($filter->filter_type & 2 && !$reported) {
  // Report flag is set, report the post
  $reported = 1;
  // TODO add this later
  }
  }
  }
  }*/
  }

  /**
   * Checks if the oekaki file is valid and returns its path.
   *
   * @return string|bool The path to the oekaki file if valid, false otherwise.
   */
  public function checkOekaki()
  {
    // If oekaki seems to be in the url...
    if (!empty($this->request['oekaki'])) {
      $oekpath = kxEnv::Get('kx:paths:boards:folder') . $this->request['board'] . '/tmp/' . $this->request['oekaki'] . '.png';
      /* See if it checks out and is a valid oekaki id */
      if (is_file($oekpath)) {
        /* Set the variable to tell the script it is handling an oekaki posting, and the oekaki file which will be posted */
        return $oekpath;
      }
    }

    return false;
  }


  public function getPostTag()
  {
    /* Check for and parse tags if one was provided, and they are enabled */
    $tags = unserialize(kxEnv::Get('kx:tags'));
    if (!empty($tags) && !empty($this->request['tag']) && in_array($this->request['tag'], $tags)) {
      return $this->request['tag'];
    }
    return false;
  }

  /**
   * Checks if the post is a modpost and logs it.
   *
   * @param array $post The post data.
   * @param object $board The board object containing board settings.
   */
  public function modPost($post, $board)
  {
    if ($post['user_authority'] > 0 && $post['user_authority'] != 3) {
      $modpost_message = 'Modposted #<a href="' . kxEnv::Get('kx:paths:boards:folder') . $board->board_name . '/res/';
      if ($post['is_reply']) {
        $modpost_message .= $post['thread_info']['parent'];
      } else {
        $modpost_message .= $post['post_id'];
      }
      $modpost_message .= '.html#' . $post['post_id'] . '">' . $post['post_id'] . '</a> in /' . $this->board->board_name . '/ with flags: ' . $post['flags'] . '.';
      management_addlogentry($modpost_message, 1, md5_decrypt($this->request['modpassword'], kxEnv::Get('kx:misc:randomseed')));
    }
  }

  /**
   * Sets cookies for the post data.
   *
   * @param array $post The post data.
   */
  public function setCookies($post)
  {
    if ($post['name_save'] && isset($this->request['name'])) {
      setcookie('name', urldecode($this->request['name']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
    }

    if ($post['email_save']) {
      setcookie('email', urldecode($post['email']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
    }

    setcookie('postpassword', urldecode($this->request['postpassword']), time() + 31556926, '/');
  }

  /**
   * Checks if the post is a sage and handles it accordingly.
   *
   * @param array $postData The data of the post being processed.
   * @param object $board The board object containing board settings.
   */
  public function checkSage($postData, $board)
  {
    // If the user replied to a thread, and they weren't sage-ing it...
    if ($postData['thread_info']['parent'] != 0 && strtolower($this->request['em']) != 'sage' /*&& unistr_to_ords($_POST['em']) != array(19979, 12370)*/) {
      // And if the number of replies already in the thread are less than the maximum thread replies before perma-sage...
      if ($postData['thread_info']['replies'] <= $board->board_max_replies) {
        // Bump the thread
        $thread = $this->entityManager->find(Edaha\Entities\Post::class, $postData['thread_info']['parent']);
        $thread->bump();
        // $this->entityManager->persist($thread);
        // $this->entityManager->flush();
      }
    }
  }

  /**
   * Updates the thread watch status for the user.
   *
   * @param array $postData The data of the post being processed.
   * @param object $board The board object containing board settings.
   */
  public function updateThreadWatch($postData, $board)
  {
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
          ->condition("board_id", $board->board_id)
          ->condition("is_deleted", 0)
          ->condition("parent_post_id", $postData['thread_info']['parent'])
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

  /**
   * Handles the upload of files and images for the post.
   *
   * @param array $postData The data of the post being processed.
   * @param object $board The board object containing board settings.
   * @return array An array of uploaded files.
   */
  public function doUpload(&$postData, $board)
  {
    $uploadClass = $this->environment->get('kx:classes:board:upload:id');

    @mkdir(KX_BOARD . '/' . $board->board_name, 0777, true);
    @mkdir(KX_BOARD . '/' . $board->board_name . '/src/', 0777, true);
    @mkdir(KX_BOARD . '/' . $board->board_name . '/thumb/', 0777, true);
    @mkdir(KX_BOARD . '/' . $board->board_name . '/res/', 0777, true);

    if ((!isset($this->request['nofile']) && $board->board_no_file == 1) || $board->board_no_file == 0) {
      $uploadClass->HandleUpload($postData, $board);
    }
    if (!$uploadClass->isvideo) {
      foreach ($uploadClass->files as $key => $file) {
        if (!file_exists(KX_BOARD . '/' . $board->board_name . '/src/' . $file['file_name'] . $file['file_type']) || !$file['file_is_special'] && !file_exists(KX_BOARD . '/' . $board->board_name . '/thumb/' . $file['file_name'] . 's' . $file['file_type'])) {
          kxFunc::showError(_('Could not copy uploaded image.'));
        }
      }
    }
    if (isset($postData['is_oekaki']) && $postData['is_oekaki']) {
      if (file_exists(KX_BOARD . '/' . $board->board_name . '/src/' . $uploadClass->files[0]['file_name'] . '.pch')) {
        $postData['thread_info']['message'] .= '<br /><small><a href="' . KX_SCRIPT . '/animation.php?board=' . $board->board_name . '&amp;id=' . $uploadClass->files[0]['file_name'] . '">' . _('View animation') . '</a></small>';
      }
    }
    return $uploadClass->files;
  }

  /**
   * Parses the fields from the request data and returns them as an array.
   *
   * @return array An associative array containing the parsed fields: name, email, and subject.
   */
  public function parseFields()
  {
    /* Fetch and process the name, email, and subject fields from the post data */
    $post_name = isset($this->request['name']) ? htmlspecialchars($this->request['name'], ENT_QUOTES) : '';
    $post_email = isset($this->request['em']) ? str_replace('"', '', strip_tags($this->request['em'])) : '';
    /* If the user used a software function, don't store it in the database */
    if ($post_email == 'return' || $post_email == 'noko') {
      $post_email = '';
    }

    $post_subject = isset($this->request['subject']) ? htmlspecialchars($this->request['subject'], ENT_QUOTES) : '';

    return array("name" => $post_name, "email" => $post_email, "subject" => $post_subject);
  }

  /**
   * Checks if the user has mod or admin authority based on the provided password.
   *
   * @return int The authority level of the user (0 = no authority, 1 = admin, 2 = mod).
   */
  public function userAuthority()
  {
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

  /**
   * Creates a new post and saves it to the database.
   *
   * @param array $postData The data of the post being created.
   * @param array $post The post data.
   * @param array $files The files associated with the post.
   * @param string $ip The IP address of the user making the post.
   * @param bool $stickied Indicates if the post should be stickied.
   * @param bool $locked Indicates if the post should be locked.
   * @param object $board The board object containing board settings.
   * @return int The ID of the newly created post.
   */
  public function makePost($postData, $post, $files, $ip, $stickied, $locked, $board)
  {
    $reply_to_post = null;
    if (isset($postData['thread_info']['parent']) and $postData['thread_info']['parent'] != 0) {
      $reply_to_post = $this->entityManager->find(Edaha\Entities\Post::class, $postData['thread_info']['parent']);
    }

    $new_post = New Edaha\Entities\Post($board, $post['message'], $post['subject'], $reply_to_post);

    $new_post->poster->name  = $post['name'];
    $new_post->poster->email = $post['email'];
    $new_post->poster->ip = '192.168.0.1';

    $this->entityManager->persist($new_post);
    $this->entityManager->flush();

    return $new_post->id;
  }
}
