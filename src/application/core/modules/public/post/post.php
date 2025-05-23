<?php
use Edaha\Interfaces\PostingProcessorInterface;

use Edaha\Entities\Board;
use Edaha\Entities\Post;

if (!defined('KUSABA_RUNNING')) {
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}

/**
 * @file
 * Posting module
 *
 * This module handles the posting of messages to the board.
 *
 * @todo Work out a system to allow modules to hook into the posting module and do their own magic at certain points during posting.
 */
class public_core_post_post extends kxCmd implements PostingProcessorInterface
{
  protected $_boardClass;
  protected $_postingClass;
  protected $postData = array();
  
  protected Board $board;
  protected ?Post $parent_post = null;
  protected Post $post;

  protected bool $sage = false;
  protected bool $noko = false;

  public function exec(kxEnv $environment)
  {
    $this->preValidate();
    $this->validate();

    $this->entityManager->wrapInTransaction(function() {
      $this->preProcess();
      $this->buildPost();
      $this->process();
      $this->postProcess();
    });
    
    $this->postCommit();
    $this->redirectToBoardOrPost();

    { // TODO Move to an image handler
      // Do we have files?
      // $this->postData['files'] = isset($_FILES['imagefile']) ? $_FILES['imagefile']['name'] : '';
      // // Backwards compatability hack for dumpers that don't support multifile uploading
      // if ($this->postData['files'] && !is_array($this->postData['files'])) {
      //   foreach ($_FILES['imagefile'] as $key => $value) {
      //     $_FILES['imagefile'][$key] = array($value);
      //   }
      //   $this->postData['files'] = array($_FILES['imagefile']['name'][0]);
      // }
    }
  }

  public function preValidate(): void
  {
    // TODO Add pre-validation checks
    // $this->checkIfUserIsBannedFromSite();
  }
  
  public function validate(): void
  {
    $this->verifyBoard($this->request['board_id']);

    $this->setUpBoardClass();
    $this->setUpPostingClass();

    // more like valid POST, TODO better name
    $this->_boardClass->validPost();
  }

  private function verifyBoard($board_id): void
  {
    $board = $this->entityManager->find(Board::class, $board_id);
    if (is_null($board)) {
      kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
    }
    $this->board = $board;
  }

  private function setUpBoardClass(): void
  {
    $board_type = $this->board->type;

    $moduledir = kxFunc::getAppDir("board") . '/modules/public/' . $board_type . '/';
    if (file_exists($moduledir . $board_type . '.php')) {
      require_once $moduledir . $board_type . '.php';
    }
    // Module is not a board type module or is isn't properly configured
    else {
      kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
    }
    // Some routine checks...
    $className = "public_board_" . $board_type . "_" . $board_type;

    if (class_exists($className)) {
      $module_class = new ReflectionClass($className);
      if ($module_class->isSubClassOf(new ReflectionClass('kxCmd'))) {
        $this->_boardClass = $module_class->newInstance($this->environment);
        $this->_boardClass->execute($this->environment);
      } else {
        kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
      }
    } else {
      kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
    }
  }

  private function setUpPostingClass(): void
  {
    require_once kxFunc::getAppDir('core') . '/classes/posting.php';
    $this->_postingClass = new posting($this->environment);
    $this->environment->set('kx:classes:board:posting:id', $this->_postingClass);
  }

  public function preProcess(): void
  {
    $this->_postingClass->checkUTF8();
    // TODO shouldn't this be part of the $request object
    //kxFunc::checkBadUnicode($this->postData['post_fields']);
    $this->checkEmailCommands();

    // TODO fix these to work as preprocessors
    // $this->checkModPosting();
    // $this->checkPostingRules();
    // $this->checkIfThreadLocked();
  }

  private function checkEmailCommands(): void
  {
    if (isset($this->request['em'])) {
      $email = strtolower($this->request['em']);
      
      // Check if used Noko/Return
      if ($email == 'noko' || $email == 'return') {
        $this->noko = true;
        $this->request['email'] = '';
      }

      // TODO Support Japanese sage
      // Reference from before:
      //   $ords_email = unistr_to_ords($email);
      //   'sage' = $ords_email != [19979, 12370] 
      //   'age' && $ords_email != [19978, 12370]
      if ($email == 'sage') {
        $this->sage = true;
      }
    }
  }

  private function handleModPosting(): void
  {
    // Are we modposting?
    $this->postData['user_authority'] = $this->_postingClass->userAuthority();
    if (isset($this->request['displaystaffstatus'])) {
      $this->postData['flags'] .= 'D';
    }

    if (isset($this->request['lockonpost'])) {
      $this->postData['flags'] .= 'L';
    }

    if (isset($this->request['stickyonpost'])) {
      $this->postData['flags'] .= 'S';
    }

    if (isset($this->request['rawhtml'])) {
      $this->postData['flags'] .= 'RH';
    }

    if (isset($this->request['usestaffname'])) {
      $this->postData['flags'] .= 'N';
    }

    $this->postData['display_status'] = 0;
    $this->postData['lock_on_post'] = 0;
    $this->postData['sticky_on_post'] = 0;

    // If they are just a normal user, or vip...
    if ($this->postData['user_authority'] == 0 || $this->postData['user_authority'] > 2) {
      // If the thread is locked
      if ($this->postData['thread_info']['locked'] == 1) {
        // Don't let the user post
        kxFunc::showError(_('Sorry, this thread is locked and can not be replied to.'));
      }

      // Or, if they are a moderator/administrator...
    } else {
      // If they checked the D checkbox, set the variable to tell the script to display their staff status (Admin/Mod) on the post during insertion
      if (isset($this->request['displaystaffstatus'])) {
        $this->postData['display_status'] = true;
      }

      // If they checked the RH checkbox, set the variable to tell the script to insert the post as-is...
      if (isset($this->request['rawhtml'])) {
        $this->postData['thread_info']['message'] = $this->request['message'];
        // Otherwise, parse it as usual...
      } else {
        $this->postData['thread_info']['message'] = $this->_boardClass->parseData($this->request['message']);
      }

      // If they checked the L checkbox, set the variable to tell the script to lock the post after insertion
      if (isset($this->request['lockonpost'])) {
        $this->postData['lock_on_post'] = true;
      }

      // If they checked the S checkbox, set the variable to tell the script to sticky the post after insertion
      if (isset($this->request['stickyonpost'])) {
        $this->postData['sticky_on_post'] = true;
      }
      if (isset($this->request['usestaffname'])) {
        $_POST['name'] = kxFunc::md5_decrypt($this->request['modpassword'], kxEnv::Get('kx:misc:randomseed'));
        $post_name = kxFunc::md5_decrypt($this->request['modpassword'], kxEnv::Get('kx:misc:randomseed'));
      }
    }
  }

  private function checkPostingRules(): void
  {
    $this->_postingClass->checkIfPostingTooFast($this->post);
    $this->_postingClass->checkIfMessageTooLong($this->post);
    // $this->_postingClass->checkBlacklistedText($this->_boardClass->board->id);
    // $this->_postingClass->checkCaptcha($this->_boardClass->board, $this->postData);
    // $this->_postingClass->checkBannedHash($this->_boardClass->board);
  }

  public function buildPost(): void
  {
    // TODO This is more "Validate the Thread ID" than "Is this a reply"
    $this->validateReplyThread();

    $subject = isset($this->request['subject']) ? $this->request['subject'] : '';

    $this->post = New Post($this->board, $this->request['message'], $subject, $this->parent_post);
    $this->post->poster->email = $this->request['em'];
    $this->post->poster->name = $this->request['name'];
    $this->post->deletion_password = $this->request['postpassword'];
    
    $this->entityManager->persist($this->post);
  }
  
  private function validateReplyThread(): void
  {
    if ($this->request['replythread'] > 0) {
      $this->parent_post = $this->entityManager->find(Post::class, $this->request['replythread']);
      if ($this->parent_post == null || $this->parent_post->is_reply) {
        /* Kill the script, stopping the posting process */
        kxFunc::showError(_('Invalid thread ID.'), _('That thread may have been recently deleted.'));
      }
    }
  }

  public function process(): void
  {
    $this->_boardClass->checkFields($this->post);
    $this->_boardClass->processPost($this->post);
  }

  public function postProcess(): void
  {
    if (isset($this->parent_post) and !$this->sage and count($this->parent_post->replies) <= !$this->board->max_replies) {
      $this->parent_post->bump();
    }
    // TODO Set Cookies
    // TODO Modlog entries after modposting
    // TODO ThreadWatch
  }

  public function postCommit(): void
  {
    $this->_boardClass->postCommit($this->post);
  }
  
  private function redirectToBoardOrPost(): void
  {
    $url = kxEnv::Get("kx:paths:boards:path") . '/' . $this->board->directory;

    if (!$this->noko) {
      $url .= '/' . kxEnv::Get('kx:pages:first');
    } else {
      $url .= '/res/';
      $url .=  ($this->post->is_reply) ? $this->parent_post->id : $this->post->id;
      $url .=  '.html';
    }

    @header('Location: ' . $url);
  }

  // TODO Move to a report.php
  private function reportPost(): void
  {
    logging::addReport(
      $this->request['board_id'],
      $this->request['post'],
      $this->request['reportreason'],
    );
  }
}
