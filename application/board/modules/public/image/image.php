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
 * Section for building an image-type imageboard
 * Last Updated: $Date$
 
 * @author 		$Author$

 * @package		kusaba
 * @subpackage	board

 * @version		$Revision$
 *
 */
 
if (!defined('KUSABA_RUNNING'))
{
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}

class public_board_image_image extends kxCmd {
  /**
   * Board data
   *
   * @access	public
   * @var		object	stdClass
   */
  public $board;
  
  protected $thread = 0;
  
  protected $method = "";
  
  /**
   * Run requested method
   *
   * @access	public
   * @param	object		Registry object
   * @param	object		Board object
   * @param	int			Thread ID
   * @param	string		Method to run
   * @return	void
   */
  public function exec( kxEnv $environment ) 
  {
    //-----------------------------------------
    // Setup...
    //-----------------------------------------
    
    $this->board = $this->db->select("boards")
                            ->fields("boards")
                            ->condition("board_name", $this->request['board'])
                            ->execute()
                            ->fetchAll();
    $this->board = $this->board[0];

    require_once( kxFunc::getAppDir('board') .'/classes/rebuild.php' );
    $this->environment->set('kx:classes:board:rebuild:id', new rebuild( $environment ) );
    $this->environment->get('kx:classes:board:rebuild:id')->board = $this->board;
    require_once( kxFunc::getAppDir('board') .'/classes/upload.php' );
    $this->environment->set('kx:classes:board:upload:id', new upload( $environment ) );

  }
  
  public function validPost() {
    if (
      ( /* A message is set, or an image was provided */
        isset($this->request['message']) ||
        isset($_FILES['imagefile'])
      ) || (
        ( /* It has embedding allowed */
            $this->board->board_upload_type == 1 ||
            $this->board->board_upload_type == 2
        ) && ( /* An embed ID was provided, or no file was checked and no ID was supplied */
            isset($this->request['embed']) ||
            (
              $this->board->board_upload_type == 2 &&
              !isset($_FILES['imagefile']) &&
              isset($this->request['nofile']) &&
              $this->board->enable_no_file == true
            )
        )
      )
    ) {
      return true;
    } else {
      return false;
    }
  }

  public function parseData($message, $postData) {
   // Stub
   return $message;
  }
  
  public function processPost($postData) {
    if (!$postData['is_reply']) {
      if (($this->board->board_upload_type == 1 || $this->board->board_upload_type == 2) && !empty($this->board->board_embeds_allowed)) {
        if (isset($this->request['embed'])) {
          if(empty($this->request['embed']) && (( $this->board->board_upload_type == 1 && empty($postData['files'][0]) ) || $this->board->board_upload_type == 2 )) {
            kxFunc::showError(_gettext('Please enter an embed ID.'));
          }
        } 
        else {
          kxFunc::showError(_gettext('Please enter an embed ID.'));
        }
      }
      if (empty($postData['files'][0]) && ( ( !isset($this->request['nofile']) && $this->board->board_enable_no_file == 1 ) || $this->board->board_enable_no_file ) ) {
        if (!empty($this->request['embed']) && $this->board->board_upload_type != 1) {
          kxFunc::showError(_gettext('A file is required for a new thread. If embedding is allowed, either a file or embed ID is required.'));
        }
      }
    }
    else {
      if (is_array($postData['files']) && empty($postData['files'][0]) && empty($postData['message'])) {
        kxFunc::showError(_gettext('An image, or message, is required for a reply.'));
      }
    }
    if (isset($this->request['nofile']) && $this->board->board_enable_no_file == 1) {
      if (empty($postData['message'])) {
        kxFunc::showError('A message is required to post without a file.');
      }
    }
    if ($this->board->board_locked == 1 && ($postData['user_authority'] != 1 && $postData['user_authority'] != 2)) {
      kxFunc::showError(_gettext('Sorry, this board is locked and can not be posted in.'));
    }
    else {
      $this->_uploadClass = $this->environment->get('kx:classes:board:upload:id');

      if ((!isset($this->request['nofile']) && $this->board->board_enable_no_file == 1) || $this->board->board_enable_no_file == 0) {
        $this->_uploadClass->HandleUpload($postData, $this->board);
      }

      if ($this->board->board_forced_anonynous == 0) {
        if ($postData['user_authority'] == 0 || $postData['user_authority'] == 3) {
          $postData['post_fields']['name'] = '';
          $postData['post_fields']['subject'] = ''; // Do we still need this?
        }
      }

      //$nameandtripcode = kxFunc::calculateNameAndTripcode($postData['post_fields']['name']);
      if (is_array($nameandtripcode)) {
        $name = $nameandtripcode[0];
        $tripcode = $nameandtripcode[1];
      } else {
        $name = $postData['post_fields']['name'];
        $tripcode = '';
      }

      $post_passwordmd5 = ($postData['post_fields']['postpassword'] == '') ? '' : md5($postData['post_fields']['postpassword']);

      if ($postData['sticky_on_post'] == true) {
        if ($postData['thread_info']['parent'] == 0) {
          $sticky = 1;
        } else {
          $this->db->update("posts")
                   ->fields(array("stickied" => 1))
                   ->condition("post_board", $this->board->board_id)
                   ->condition("post_id", $postData['thread_info']['parent'])
                   ->execute();
          $sticky = 0;
        }
      } else {
        $sticky = 0;
      }

      if ($postData['lock_on_post'] == true) {
        if ($postData['thread_info']['parent'] == 0) {
          $lock = 1;
        } else {
          $this->db->update("posts")
                   ->fields(array("locked" => 1))
                   ->condition("post_board", $this->board->board_id)
                   ->condition("post_id", $postData['thread_info']['parent'])
                   ->execute();
          $lock = 0;
        }
      } else {
        $lock = 0;
      }

      if (!$postData['display_status'] && $postData['user_authority'] > 0 && $postData['user_authority'] != 3) {
        $postData['user_authority_display'] = 0;
      } elseif ($postData['user_authority'] > 0) {
        $postData['user_authority_display'] = $postData['user_authority'];
      } else {
        $postData['user_authority_display'] = 0;
      }
      if (!$this->_uploadClass->isvideo) {
        foreach ($this->_uploadClass->files as $key=>$file){
          if (!file_exists(KX_BOARD . '/' . $this->board->board_name . '/src/' . $file['file_name'] . $file['file_type']) || !$file['file_is_special'] && !file_exists(KX_BOARD . '/' . $this->board->board_name . '/thumb/' . $file['file_name'] . 's' . $file['file_type'])) {
            exitWithErrorPage(_gettext('Could not copy uploaded image.'));
          }
        }
      }
     $post = array();

      $post['board'] = $this->board->board_name;
      $post['name'] = substr($name, 0, 74);
      $post['name_save'] = true;
      $post['tripcode'] = $tripcode;
      $post['email'] = substr($postData['email'], 0, 74);
      // First array is the converted form of the japanese characters meaning sage, second meaning age
      // Needs converting
      //$ords_email = unistr_to_ords($post_email);
      $ords_email = array();
      if (strtolower($this->request['em']) != 'sage' && $ords_email != array(19979, 12370) && strtolower($this->request['em']) != 'age' && $ords_email != array(19978, 12370) && $this->request['em'] != 'return' && $this->request['em'] != 'noko') {
        $post['email_save'] = true;
      } else {
        $post['email_save'] = false;
      }
      $post['subject'] = substr($postData['subject'], 0, 74);
      $post['message'] = $postData['thread_info']['message'];

      //Needs 1.0 equivalent
      // $post = hook_process('posting', $post);

      if ($postData['thread_info']['parent'] != 0) {
        if ($post['message'] == '' && kxEnv::Get('kx:posts:emptyreply') != '') {
          $post['message'] = kxEnv::Get('kx:posts:emptyreply');
        }
      } else {
        if ($post['message'] == '' && kxEnv::Get('kx:posts:emptythread') != '') {
          $post['message'] = kxEnv::Get('kx:posts:emptythread');
        }
      }

      $postClass = $this->environment->get('kx:classes:board:posting:id');
      
      $post_id = $postClass->makePost($postData, $post, $this->_uploadClass->files, $_SERVER['REMOTE_ADDR'], $sticky, $lock, $this->board->board_id);

      if ($postData['user_authority'] > 0 && $postData['user_authority'] != 3) {
        $modpost_message = 'Modposted #<a href="' . kxEnv::Get('kx:paths:boards:folder') . $this->board->board_name . '/res/';
        if ($postData['is_reply']) {
          $modpost_message .= $postData['thread_info']['parent'];
        } else {
          $modpost_message .= $post_id;
        }
        $modpost_message .= '.html#' . $post_id . '">' . $post_id . '</a> in /'. $this->board->board_name .'/ with flags: ' . $postData['flags'] . '.';
        management_addlogentry($modpost_message, 1, md5_decrypt($this->request['modpassword'], kxEnv::Get('kx:misc:randomseed')));
      }

      if ($post['name_save'] && isset($this->request['name'])) {
        setcookie('name', urldecode($this->request['name']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
      }

      if ($post['email_save']) {
        setcookie('email', urldecode($post['email']), time() + 31556926, '/', kxEnv::Get('kx:paths:main:domain'));
      }

      setcookie('postpassword', urldecode($this->request['postpassword']), time() + 31556926, '/');
 
      // If the user replied to a thread, and they weren't sage-ing it...
      if ($postData['thread_info']['parent'] != 0 && strtolower($this->request['em']) != 'sage' /*&& unistr_to_ords($_POST['em']) != array(19979, 12370)*/) {
        // And if the number of replies already in the thread are less than the maximum thread replies before perma-sage...
        if ($thread_replies <= $this->board->board_maxreplies) {
          // Bump the thread
          $this->db->update("posts")
                   ->fields(array("post_bumped" => time()))
                   ->condition("post_board", $this->board->board_id)
                   ->condition("id", $postData['thread_info']['parent'])
                   ->execute();
        }
      }

      // If the user replied to a thread he is watching, update it so it doesn't count his reply as unread
      if (kxEnv::Get('ku:extras:watchthreads') && $postData['thread_info']['parent'] != 0) {
        $viewing_thread_is_watched = $this->db->select("watchedthreads")
                                              ->fields(array("watchedthreads"))
                                              ->countQuery()
                                              ->condition("watch_ip", $_SERVER['REMOTE_ADDR'])
                                              ->condition("watch_board", $this->board->board_name)
                                              ->condition("watch_thread", $postData['thread_info']['parent'] != 0)
                                              ->execute()
                                              ->fetchCol();
        if ($viewing_thread_is_watched[0] > 0) {
          $newestreplyid = $this->db->select("posts")
                                    ->fields("posts", array("post_id"))
                                    ->condition("post_board", $this->board->board_id)
                                    ->condition("post_deleted", 0)
                                    ->condition("post_parent", $postData['thread_info']['parent']) 
                                    ->orderBy("post_id", "DESC")
                                    ->range(0, 1)
                                    ->execute()
                                    ->fetchCol();

          $this->db->update("watchedthreads")
                   ->fields(array("watch_last_id_seen" => $newestreplyid))
                   ->condition("watch_ip", $_SERVER['REMOTE_ADDR'])
                   ->condition("watch_board", $this->board->board_name)
                   ->condition("watch_thread", $postData['thread_info']['parent'])
                   ->execute();
        }
      }

      // Trim any threads which have been pushed past the limit, or exceed the maximum age limit
      //kxExec:TrimToPageLimit($board_class->board);

      // Regenerate board pages
      $this->regeneratePages();
      if ($postData['thread_info']['parent'] == 0) {
        // Regenerate the thread
       // $this->regenerateThreads($post_id);
      } else {
        // Regenerate the thread
        //$board_class->regenerateThreads($postData['thread_info']['parent']);
      }
    }
  }
  
  public function regeneratePages() {
    //-----------------------------------------
    // Setup
    //-----------------------------------------
    $this->board->embeds = array();
    $results = $this->db->select("embeds")
                        ->fields("embeds", array("embed_ext"))
                        ->execute()
                        ->fetchAll();
    foreach ($results as $line) {
      $this->board->embeds[] = $line->embed_ext;
    }

    $this->dwoo_data['filetypes'] = $this->board->embeds;

    $postsperpage =	$this->environment->get('kx:display:imgthreads');
    $i = 0;
    $maxpages = $this->board->maxpages;
    $numposts = $this->db->select("posts")
                         ->fields("posts")
                         ->condition("post_board", $this->board->board_id)
                         ->condition("post_parent", 0)
                         ->condition("post_deleted", 0)
                         ->countQuery()
                         ->execute()
                         ->fetchField();
    $totalpages = kxFunc::pageCount($this->board_type, ($numposts-1));
    // Saznote: move to rebuild.php
    $results = $this->db->select("embeds")
                        ->fields("embeds", array('embed_ext'))
                        ->execute()
                        ->fetchAll();
    foreach ($results as $line) {
      $this->board->filetypes[] = $line->embed_ext;
    }

    //-----------------------------------------
    // Run through each page
    //-----------------------------------------    

    while ($i <= $totalpages) {
      $this->dwoo_data['thispage'] = $i;
      
      //--------------------------------------------------------------------------------------------------
      // Grab our threads, stickies go first, then follow by bump time, then run through them
      //--------------------------------------------------------------------------------------------------
      $threads = $this->db->select("posts")
                          ->fields("posts")
                          ->condition("post_board", $this->board->board_id)
                          ->condition("post_parent", 0)
                          ->condition("post_deleted", 0)
                          ->orderBy("post_stickied", "DESC")
                          ->orderBy("post_bumped", "DESC")
                          ->range($postsperpage * $i, $postsperpage)
                          ->execute()
                          ->fetchAll();
      foreach ($threads as &$thread) {

        //------------------------------------------------------------------------------------------
        // If the thread is on the page set to mark, and hasn't been marked yet, mark it
        //------------------------------------------------------------------------------------------
        if ($thread->post_delete_time == 0 && $this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
          // Saznote: move to rebuild.php
          $this->db->update("posts")
                   ->fields(array(
                     'post_delete_time' => time() + 7200,
                   ))
                   ->condition("post_board", $this->board->board_id)
                   ->condition("post_id", $thread->post_id)
                   ->execute();
          $this->RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->dwoo_data['replythread'] = 0;
        }
        $thread = $this->environment->get('kx:classes:board:rebuild:id')->buildPost($thread, true);
        $omitids = array();
        
        //-----------------------------------------------------------------------------------------------------------------------------------
        // Process stickies without using prepared statements (because they have a different range than regular threads).
        // Since there's usually very few to no stickies we can get away with this with minimal performance impact.
        //-----------------------------------------------------------------------------------------------------------------------------------
        if ($thread->post_stickied == 1) {
          $posts = $this->db->select("posts")
                            ->condition("post_board", $this->board->board_id)
                            ->condition("post_parent", $thread->post_id)
                            ->condition("post_deleted", 0)
                            ->orderBy("id", "DESC")
                            ->range(0, kxEnv::Get('kx:display:stickyreplies'))
                            ->execute()
                            ->fetchAll();
        }
        
        //---------------------------------------------------------------------------------------------------
        // For non-stickies, we check if the $results object exists, or if it isn't an instance of
        // kxDBStatementInterface (like if it was overwritten somwehere else). If is isn't, the
        // query is prepared, otherwise, we used the prepared statement already given to us
        //----------------------------------------------------------------------------------------------------
        else {
          if (empty($results) || !($results instanceof kxDBStatementInterface)) { 
            $results = $this->db->select("posts")
                    ->fields("posts")
                    ->where("post_board = ? AND post_parent = ? AND post_deleted = 0")
                    ->orderBy("post_id", "DESC")
                    ->range(0, kxEnv::Get('kx:display:replies'))
                    ->build();
          }
          $results->execute(array($this->board->board_id, $thread->post_id));
          $posts = $results->fetchAll();
        }
        foreach ($posts as &$post) {
          $omitids[] = $post['id'];
          $post = $this->environment->get('kx:classes:board:rebuild:id')->buildPost($post, true);
        }
        //---------------------------------------------------------------------------------------------------
        // Get the number of replies and image-replies for this thread, minus the ones that are
        // already being shown.
        //----------------------------------------------------------------------------------------------------
        $replycount = $this->db->select("posts", "p");
        $replycount->addExpression('COUNT(DISTINCT(file_post))', 'replies');
        $replycount->addExpression('SUM(CASE WHEN file_md5 = \'\' THEN 0 ELSE 1 END)', 'files');
        $replycount->innerJoin("post_files", "f", "post_id = file_post AND file_board = post_board");
        $replycount->condition("post_board", $this->board->board_id)
                   ->condition("post_parent", $thread->post_id)
                   ->condition("post_deleted", 0);
        if (!empty($omitids)) {
          $replycount->condition("file_post", $omitids, "NOT IN");
        }
        $replycount = $replycount->execute()
                                 ->fetchAll();
        $thread->replies  = $replycount[0]->replies;
        $thread->images   = $replycount[0]->files;

        $posts = array_reverse($posts);
        array_unshift($posts, $thread);
        $outThread[] = $posts;
      }
      if (!isset($embeds)) {
        $embeds = $this->db->select("embeds")
                           ->fields("embeds")
                           ->execute()
                           ->fetchAll();
        $this->dwoo_data['embeds'] = $embeds;
      }
      if (!isset($header)){
        $header = $this->pageHeader();
        $header = str_replace("<!sm_threadid>", 0, $header);
      }
      if (!isset($postbox)) {
        $postbox = $this->postBox();
        $postbox = str_replace("<!sm_threadid>", 0, $postbox);
      }

      $this->dwoo_data['posts'] = $outThread;
      
      $this->dwoo_data['file_path'] = kxEnv::Get('kx:paths:boards:path') . '/' . $this->board->board_name;
      $content = kxTemplate::get('img_board_page', $this->dwoo_data);
      $footer = $this->footer(false, (microtime(true) - kxEnv::Get('kx:executiontime:start')));
      $content = $header.$postbox.$content.$footer;

      $content = str_replace("\t", '',$content);
      $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

      if ($i == 0) {
        $page = KX_BOARD . '/'.$this->board->board_name.'/'.kxEnv::Get('kx:pages:first');
      } else {
        $page = KX_BOARD . '/'.$this->board->board_name.'/'.$i.'.html';
      }
      kxFunc::outputToFile($page, $content, $this->board->board_name);
      $i++;
    }
  }

  /**
   * Regenerate each thread's corresponding html file, starting with the most recently bumped
   */
  public function regenerateThreads($id = 0) {

    $numimages = 0;
    $embeds = $this->db->select("embeds")
                       ->fields("embeds")
                       ->execute()
                       ->fetchAll();
    $this->dwoo_data['embeds'] = $embeds;
    // No ID? Get every thread.
    if ($id == 0) {
      //-----------------------------------------------------
      // Cache the page header and post box to save resources
      //-----------------------------------------------------
      $this->board->header = $this->pageHeader(1);
      $this->board->postbox = $this->postbox(1);
      
      // Okay let's do this!
      $threads = $this->db->select("posts")
                          ->fields("posts")
                          ->condition("post_board", $this->board->board_id)
                          ->condition("post_parent", 0)
                          ->condition("post_deleted", 0)
                          ->orderBy("post_id", "DESC")
                          ->execute()
                          ->fetchAll();
      if (count($threads) > 0) {
        foreach($threads as $thread) {
          $this->regenerateThreads($thread->post_id);
        }
      }
    } 
    else {
      for ($i = 0; $i < 3, $i++) {
        if ((!$i > 0 && kxEnv::Get('kx:extras:firstlast')) || ($i == 1 && $replycount < 50) || ($i == 2 && $replycount < 100)) {
          break;
        }
        if ($i == 0) {
          $lastBit = "";
          $executiontime_start_thread = microtime(true);
          //---------------------------------------------------------------------------------------------------
          // Okay, this may seem confusing, but we're caching this so we can use it as a prepared statement
          // intead of executing it every time. This is only really useful if we're regenerating all threads,
          // but the perfomance impact otherwise is minimal.
          //----------------------------------------------------------------------------------------------------
          if (!isset($this->board->preparedThreads)) {
            $this->board->preparedThreads = $this->db->select("posts")
                                                     ->fields("posts")
                                                     ->condition("boardid", $this->board->board_id)
                                                     ->where("post_id = ? OR post_parent = ?")
                                                     ->condition("post_deleted", 0)
                                                     ->orderBy("post_id")
                                                     ->build();
          }
          // Since we prepared the statement earlier, we just need to execute it.
          $thread = $this->board->preparedThreads->execute(Array($id, $id));
          foreach ($thread as &$post) {
            $post = $this->environment->get('kx:classes:board:rebuild:id')->buildPost($post, false);
            if (!empty($post->file_type)){
              foreach($post->file_type as $type) {
                if (($type == 'jpg' || $type == 'gif' || $type == 'png')) {
                  $numimages++;
                }
              }
            }
          }

          //-----------------------------------------------------------------------
          // When using a pointer in a foreach, the $value variable persists 
          // as the last index of an array, we can use this to our advantage here.
          //-----------------------------------------------------------------------
          if (kxEnv::Get('kx:extras:postspy')) {
            $this->dwoo_data['lastid'] = $post->post_id;
          }
          // Now we can get rid of it
          unset($post);
          
          //-----------------------------------------------------------------------
          // If we're regenerating all threads, we already cached these earlier
          //-----------------------------------------------------------------------
          if ( !isset($this->board->header) || !isset($this->board->postbox)) {
            $this->board->header = $this->pageHeader($id);
            $this->board->postbox = $this->postBox($id);
          }
          
          //----------------------------------------------------
          // There's still some placeholders we need to replace 
          // though, regardless. This is actually significantly
          // faster than reprocessing the entire template.
          //----------------------------------------------------
          $this->board->header  = str_replace("<!sm_threadid>", $id, $this->board->header );
          $this->board->postbox = str_replace("<!sm_threadid>", $id, $this->board->postbox);


          //-----------
          // Dwoo-hoo
          //-----------
          $this->dwoo_data['numimages']   = $numimages;
          $this->dwoo_data['replythread'] = $id;
          $this->dwoo_data['threadid']    = $thread[0]->post_id;
          $this->dwoo_data['posts']       = $thread;
          //$this->dwoo_data->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));
          $replycount = (count($thread)-1);
          $this->dwoo_data['replycount']  = $replycount;
          $replyHeader = kxTemplate::get('img_reply_header', $this->dwoo_data);
          if (!isset($this->board->footer)) $this->board->footer = $this->footer(false, (microtime(true) - $executiontime_start_thread));
        }
        else if ($i == 1) {
          $lastBit = "+50";
          $this->dwoo_data->assign('modifier', "last50");

          // Grab the last 50 replies
          $posts50 = array_slice($thread, -50, 50);
          // Add the thread to the top of this, since it wont be included in the result
          array_unshift($posts50, $thread[0]); 

          $this->dwoo_data['posts'] = $posts50;
          unset($posts50);
        }
        elseif ($i == 2) {
          $lastBit = "-100";
          $this->dwoo_data->assign('modifier', "first100");
          
          // Grab the first 100 posts
          $this->dwoo_data['posts'] = array_slice($thread, 0, 100);
        }
        $content = kxTemplate::get('img_thread', $this->dwoo_data);
        $content = $this->board->header.$this->board->postbox.$replyHeader.$content.$footer;
        kxFunc::outputToFile(KX_BOARD . '/' . $this->board['name'] . $this->archive_dir . '/res/' . $id . $lastBit . '.html', $content, $this->board->board_name);
      }
    }
  }
  
  /**
   * Build the page header
   *
   * @param integer $replythread The ID of the thread the header is being build for.  0 if it is for a board page
   * @param integer $liststart The number which the thread list starts on (text boards only)
   * @param integer $liststooutput The number of list pages which will be generated (text boards only)
   * @return string The built header
   */
  public function pageHeader($replythread = 0) {

    $tpl = Array();

    $tpl['htmloptions'] = ((kxEnv::Get('kx:misc:locale') == 'he' && empty($this->board->board_locale)) || $this->board->board_locale == 'he') ? ' dir="rtl"' : '' ;

    $tpl['title'] = '';

    if (kxEnv::Get('kx:pages:dirtitle')) {
      $tpl['title'] .= '/' .  $this->board->board_name . '/ - ';
    }
    $tpl['title'] .= $this->board->board_desc;

    $this->dwoo_data['title'] = $tpl['title'];
    $this->dwoo_data['htmloptions'] = $tpl['htmloptions'];
    $this->dwoo_data['locale'] = $this->board->board_locale;
    $this->dwoo_data['board'] = $this->board;
    $this->dwoo_data['replythread'] = $replythread;
    $this->dwoo_data['topads'] = $this->db->select("ads")
                                          ->fields("ads", array("ad_code"))
                                          ->condition("ad_position", "top")
                                          ->condition("ad_display", 1)
                                          ->execute()
                                          ->fetchField();
    $this->dwoo_data['ku_styles'] = explode(':', kxEnv::Get('kx:css:imgstyles'));
    $this->dwoo_data['ku_defaultstyle'] = (!empty($this->board->board_style_default) ? ($this->board->board_style_default) : (kxEnv::Get('kx:css:imgdefault')));
    $this->dwoo_data['boardlist'] = $this->board->boardlist;

    return kxTemplate::get('global_board_header', $this->dwoo_data).kxTemplate::get('img_header', $this->dwoo_data);
  }
  
  /**
   * Generate the postbox area
   *
   * @param integer $replythread The ID of the thread being replied to.  0 if not replying
   * @param string $postboxnotice The postbox notice
   * @return string The generated postbox
   */
  public function postBox($replythread = 0) {

    if (kxEnv::Get('kx:extras:blotter')) {
        $this->dwoo_data['blotter'] = kxFunc::getBlotter();
        $this->dwoo_data['blotter_updated'] = kxFunc::getBlotterLastUpdated();
    }
    return kxTemplate::get('img_post_box', $this->dwoo_data);
    
  }


  /**
   * Display the page footer
   *
   * @param boolean $noboardlist Force the board list to not be displayed
   * @param string $executiontime The time it took the page to be created
   * @param boolean $hide_extra Hide extra footer information, and display the manage link
   * @return string The generated footer
   */
  public function footer($noboardlist = false, $executiontime = 0) {

    if ($noboardlist) $this->dwoo_data['boardlist'] = "";
    if ($executiontime) $this->dwoo_data['executiontime'] = round($executiontime, 2);
    
    $this->dwoo_data['botads'] = $this->db->select("ads")
                                          ->fields("ads", array("ad_code"))
                                          ->condition("ad_position", "bot")
                                          ->condition("ad_display", 1)
                                          ->execute()
                                          ->fetchField();

    return kxTemplate::get('img_footer', $this->dwoo_data).kxTemplate::get('global_board_footer', $this->dwoo_data);
  }
}