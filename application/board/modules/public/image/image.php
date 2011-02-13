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
    $postClass = $this->environment->get('kx:classes:board:posting:id');
    
    if (!$postData['is_reply']) {
      if (($this->board->board_upload_type == 1 || $this->board->board_upload_type == 2) && !empty($this->board->board_embeds_allowed)) {
        if ($postClass->checkEmbed($postData)) {
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
      if (!$postClass->checkEmpty($postData)) {
        kxFunc::showError(_gettext('An image, or message, is required for a reply.'));
      }
    }
    if (isset($this->request['nofile']) && $this->board->board_enable_no_file == 1) {
      if (!$postClass->checkNoFile) {
        kxFunc::showError('A message is required to post without a file.');
      }
    }
    if ($this->board->board_locked == 1 && ($postData['user_authority'] != 1 && $postData['user_authority'] != 2)) {
      kxFunc::showError(_gettext('Sorry, this board is locked and can not be posted in.'));
    }
    else {
      $files = $postClass->doUpload($postData, $this->board);
      $postClass->forcedAnon($postData, $this->board);
      $nameAndTrip = $postClass->handleTripcode($postData);
      $post_passwordmd5 = ($postData['post_fields']['postpassword'] == '') ? '' : md5($postData['post_fields']['postpassword']);
      $commands = $postClass->checkPostCommands($postData);
      $postClass->checkEmptyReply($postData);

      $post = array();
      $post['board'] = $this->board->board_name;
      $post['name'] = substr($nameAndTrip[0], 0, 74);
      $post['name_save'] = true;
      $post['tripcode'] = $nameAndTrip[1];
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
      
      $post['post_id'] = $postClass->makePost($postData, $post, $files, $_SERVER['REMOTE_ADDR'], $commands['sticky'], $commands['lock'], $this->board->board_id);

      $postClass->modPost(array_merge($postData, $post), $this->board);
      $postClass->setCookies($post);
      $postClass->checkSage($postData, $board);
      $postClass->updateThreadWatch($postData, $board);

      // Trim any threads which have been pushed past the limit, or exceed the maximum age limit
      //kxExec:TrimToPageLimit($board_class->board);

      // Regenerate board pages
      $this->regeneratePages();
      if ($postData['thread_info']['parent'] == 0) {
        // Regenerate the thread
        $this->regenerateThreads($post['post_id']);
      } else {
        // Regenerate the thread
        $board_class->regenerateThreads($postData['thread_info']['parent']);
      }
    }
  }
  
  public function regeneratePages() {
    //-----------------------------------------
    // Setup
    //-----------------------------------------


    $this->dwoo_data['filetypes'] = $this->environment->get('kx:classes:board:rebuild:id')->getEmbeds();
    
    $i = 0;
    
    $postsperpage =	$this->environment->get('kx:display:imgthreads');
    $totalpages = $this->environment->get('kx:classes:board:rebuild:id')->calcTotalPages();


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
        if ($this->environment->get('kx:classes:board:rebuild:id')->markThread($thread)) {
          $this->RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->dwoo_data['replythread'] = 0;
        }
        $thread = $this->environment->get('kx:classes:board:rebuild:id')->buildPost($thread, true);
        $tempPosts = $this->environment->get('kx:classes:board:rebuild:id')->buildPageThread($thread, true);
        $this->environment->get('kx:classes:board:rebuild:id')->getOmittedPosts($thread, $tempPosts[1], true);

        $posts = array_reverse($tempPosts[0]);
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
      for ($i = 0; $i < 3; $i++) {
        if ((!$i > 0 && kxEnv::Get('kx:extras:firstlast')) || ($i == 1 && $replycount < 50) || ($i == 2 && $replycount < 100)) {
          break;
        }
        if ($i == 0) {
          $lastBit = "";
          $executiontime_start_thread = microtime(true);

          $temp = $this->environment->get('kx:classes:board:rebuild:id')->buildThread($id);
          $thread = $temp[0];
          $this->dwoo_data['lastid'] = $temp[1];
          
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
          $this->dwoo_data['modifier'] = "last50";

          // Grab the last 50 replies
          $this->dwoo_data['posts'] = array_slice($thread, -50, 50);
          // Add the thread to the top of this, since it wont be included in the result
          array_unshift($this->dwoo_data['posts'], $thread[0]); 

        }
        elseif ($i == 2) {
          $lastBit = "-100";
          $this->dwoo_data['modifier'] = "first100";
          
          // Grab the first 100 posts
          $this->dwoo_data['posts'] = array_slice($thread, 0, 100);
        }
        $content = kxTemplate::get('img_thread', $this->dwoo_data);
        $content = $this->board->header.$this->board->postbox.$replyHeader.$content.$footer;
        kxFunc::outputToFile(KX_BOARD . '/' . $this->board->board_name . $this->archive_dir . '/res/' . $id . $lastBit . '.html', $content, $this->board->board_name);
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
    $this->dwoo_data += $this->environment->get('kx:classes:board:rebuild:id')->pageHeader();
    $this->dwoo_data['replythread'] = $replythread;
    $this->dwoo_data['ku_styles'] = explode(':', kxEnv::Get('kx:css:imgstyles'));
    $this->dwoo_data['ku_defaultstyle'] = (!empty($this->board->board_style_default) ? ($this->board->board_style_default) : (kxEnv::Get('kx:css:imgdefault')));
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

    $this->dwoo_data += $this->environment->get('kx:classes:board:rebuild:id')->blotter();
    return kxTemplate::get('img_post_box', $this->dwoo_data);
    
  }

  /**
   * Display the page footer
   *
   * @param boolean $noboardlist Force the board list to not be displayed
   * @param string $executiontime The time it took the page to be created
   * @return string The generated footer
   */
  public function footer($noboardlist = false, $executiontime = 0) {
    $this->dwoo_data += $this->environment->get('kx:classes:board:rebuild:id')->footer($noboardlist, $executiontime);
    return kxTemplate::get('img_footer', $this->dwoo_data).kxTemplate::get('global_board_footer', $this->dwoo_data);
  }
}