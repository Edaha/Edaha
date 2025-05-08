<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Posting module
 * Last Updated: $Date$

 * @author     $Author$

 * @package    kusaba
 * @subpackage  board

 * @version    $Revision$
 *
 * @todo      Work out a system to allow modules to hook into the posting module and do their own magic at certain points during posting.
 */

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
class public_core_post_post extends kxCmd
{
  protected $_boardClass;
  protected $_postingClass;
  protected $postData = array();
  
  protected \Edaha\Entities\Board $board;
  protected \Edaha\Entities\Post $parent_post;
  protected \Edaha\Entities\Post $post;

  protected bool $sage = false;
  protected bool $noko = false;

  private function verifyBoard($board_id): void
  {
    $board = $this->entityManager->find(\Edaha\Entities\Board::class, $board_id);
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

  private function checkPostingRules(): void
  {
    $this->_postingClass->checkIfPostingTooFast($this->post);
    $this->_postingClass->checkIfMessageTooLong($this->post);
    // $this->_postingClass->checkBlacklistedText($this->_boardClass->board->id);
    // $this->_postingClass->checkCaptcha($this->_boardClass->board, $this->postData);
    // $this->_postingClass->checkBannedHash($this->_boardClass->board);
  }

  private function setThreadInfo(): void
  {
    //How many replies, is the thread locked, etc
    if ($this->postData['is_reply']) {
      $this->postData['thread_info'] = $this->_postingClass->threadInfo($this->environment->get('kx:classes:board:id')->board_id, $this->request['replythread']);
    } else {
      $this->postData['thread_info'] = array('replies' => 0, 'locked' => 0, 'parent' => 0);
    }
  }

  private function setPostFields(): void
  {
      // Subject, email, etc fields need special processing
      $this->postData['post_fields'] = $this->_postingClass->parseFields();
      $this->postData['post_fields']['postpassword'] = isset($this->request['postpassword']) ? $this->request['postpassword'] : '';
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

  private function validateReplyThread(): void
  {
    if ($this->request['replythread'] > 0) {
      $this->parent_post = $this->entityManager->find(Edaha\Entities\Post::class, $this->request['replythread']);
      if ($this->parent_post == null || $this->parent_post->is_reply) {
        /* Kill the script, stopping the posting process */
        kxFunc::showError(_('Invalid thread ID.'), _('That thread may have been recently deleted.'));
      }
    }
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

  private function preValidate(): void
  {
    // TODO Add pre-validation checks
    // $this->checkIfUserIsBannedFromSite();
  }

  private function validate(): void
  {
    // TODO Add validation checks
    $this->verifyBoard($this->request['board_id']);

    $this->setUpBoardClass();
    $this->setUpPostingClass();

    // more like valid POST, TODO better name
    $this->_boardClass->validPost();
  }

  private function preProcess(): void
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

  private function buildPost(): void
  {
    // TODO This is more "Validate the Thread ID" than "Is this a reply"
    $this->validateReplyThread();

    $subject = isset($this->request['subject']) ? $this->request['subject'] : '';

    $this->post = New \Edaha\Entities\Post($this->board, $this->request['message'], $subject, $this->parent_post);
    $this->post->poster->email = $this->request['em'];
    $this->post->poster->name = $this->request['name'];
    $this->post->deletion_password = $this->request['postpassword'];
    
    $this->entityManager->persist($this->post);
  }

  private function process(): void
  {
    $this->_boardClass->checkFields($this->post);
    $this->_boardClass->processPost($this->post);
  }

  private function postProcess(): void
  {
    if (isset($this->parent_post) and !$this->sage and count($this->parent_post->replies) <= !$this->board->max_replies) {
      $this->parent_post->bump();
    }

    // TODO Set Cookies
    // TODO Modlog entries after modposting
    // TODO ThreadWatch
  }
  
  private function redirectToBoardOrPost(): void
  {
    $url = kxEnv::Get("kx:paths:boards:path") . '/' . $this->board->directory;

    if (!$this->noko) {
      $url .= '/' . kxEnv::Get('kx:pages:first');
    } else {
      $url .= '/res/' . ($this->post->is_reply) ? $this->parent_post->id : $this->post->id . '.html';
    }

    @header('Location: ' . $url);
  }

  public function exec(kxEnv $environment)
  {
    // TODO Add Doctrine transaction support
    $this->preValidate();
    $this->validate();
    
    $this->preProcess();
    
    $this->buildPost();
    $this->process();
    
    $this->postProcess();

    $this->entityManager->flush();

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


    // TODO Readd Reporting
    // elseif (isset($this->request['reportpost'])) {
    //   $this->reportPost();
    // }
  }

  private function reportPost(): void
  {
    logging::addReport(
      $this->request['board_id'],
      $this->request['post'],
      $this->request['reportreason'],
    );
  }
}
