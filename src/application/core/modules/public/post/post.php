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

class public_core_post_post extends kxCmd
{
  protected $_boardClass;
  protected $_postingClass;
  protected $postData = array();

  public function exec(kxEnv $environment)
  {
    // Before we do anything, let's check if we even have any board info
    $board = $this->entityManager->find(\Edaha\Entities\Board::class, $this->request['board_id']);
    if (is_null($board)) {
      kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
    }

    // Module loading time!
    $moduledir = kxFunc::getAppDir("board") . '/modules/public/' . $board->type . '/';
    if (file_exists($moduledir . $board->type . '.php')) {
      require_once $moduledir . $board->type . '.php';
    }
    // Module is not a board type module or is isn't properly configured
    else {
      kxFunc::doRedirect(kxEnv::Get('kx:paths:main:webpath'));
    }
    // Some routine checks...
    $className = "public_board_" . $board->type . "_" . $board->type;

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
    // Include our posting class
    require_once kxFunc::getAppDir('core') . '/classes/posting.php';
    $this->_postingClass = new posting($this->environment);
    $this->environment->set('kx:classes:board:posting:id', $this->_postingClass);

    // Phew, that's over with. Let's now prepare our post for generation.

    //Are we UTF-8?
    $this->_postingClass->checkUTF8();

    // Is post valid according to our board's spec?
    if ($this->_boardClass->validPost()) {
      $this->db->startTransaction();

      // Do we have files?
      $this->postData['files'] = isset($_FILES['imagefile']) ? $_FILES['imagefile']['name'] : '';
      // Backwards compatability hack for dumpers that don't support multifile uploading
      if ($this->postData['files'] && !is_array($this->postData['files'])) {
        foreach ($_FILES['imagefile'] as $key => $value) {
          $_FILES['imagefile'][$key] = array($value);
        }
        $this->postData['files'] = array($_FILES['imagefile']['name'][0]);
      }

      $this->postData['is_reply'] = $this->_postingClass->isReply($this->_boardClass->board->id);

      $this->_postingClass->checkPostingTime($this->postData['is_reply'], $this->_boardClass->board->id);
      $this->_postingClass->checkMessageLength($this->_boardClass->board->max_message_length);
      $this->_postingClass->checkBlacklistedText($this->_boardClass->board->id);
      $this->_postingClass->checkCaptcha($this->_boardClass->board, $this->postData);
      $this->_postingClass->checkBannedHash($this->_boardClass->board);

      //How many replies, is the thread locked, etc
      if ($this->postData['is_reply']) {
        $this->postData['thread_info'] = $this->_postingClass->threadInfo($this->environment->get('kx:classes:board:id')->board_id, $this->request['replythread']);
      } else {
        $this->postData['thread_info'] = array('replies' => 0, 'locked' => 0, 'parent' => 0);
      }
      // Subject, email, etc fields need special processing
      $this->postData['post_fields'] = $this->_postingClass->parseFields();
      $this->postData['post_fields']['postpassword'] = isset($this->request['postpassword']) ? $this->request['postpassword'] : '';

      $nextid = $this->db->select("posts")
        ->fields("posts", array("post_id"))
        ->condition("board_id", $this->_boardClass->board->id)
        ->execute()
        ->fetchField();
      if ($nextid) {
        $this->postData['next_id'] = ($nextid + 1);
      } else {
        $this->postData['next_id'] = 1;
      }

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

        $this->postData['thread_info']['message'] = $this->_boardClass->parseData($this->request['message']);
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
      //kxFunc::checkBadUnicode($this->postData['post_fields']);

      $this->_boardClass->processPost($this->postData);
      $url = kxEnv::Get("kx:paths:boards:path") . '/' . $this->_boardClass->board->board_name;

      if (!$this->postData['is_reply']) {
        $url .= '/' . kxEnv::Get('kx:pages:first');
      } else {
        $url .= '/res/' . intval($this->request['replythread']) . '.html';
      }

      @header('Location: ' . $url);
    } elseif (isset($this->request['reportpost'])) {
      logging::addReport(
        $this->request['board_id'],
        $this->request['post'],
        $this->request['reportreason'],
      );
    }
  }
}
