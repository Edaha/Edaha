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
 * Section for building an text-type imageboard
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

class public_board_text_text extends kxCmd {
  /**
   * Board data
   *
   * @access	public
   * @var		object	stdClass
   */
  public $board;
    
  
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
                            ->fetch();

    //-----------------------------------------
    // Get the unique posts for this board
    //-----------------------------------------
    $result = $this->db->select("posts");
    $result->addExpression("COUNT(DISTINCT post_ip_md5)");
    $this->board->board_uniqueposts = $result->condition("post_board", $this->board->board_id)
                                               ->condition("post_deleted", 0)
                                               ->execute()
                                               ->fetchField();

    //-----------------------------------------------
    // Get the the allowed filetypes for this board
    //-----------------------------------------------
    $result = $this->db->select("filetypes", "f")
                       ->fields("f", array("type_ext"));
    $result->innerJoin("board_filetypes", "bf", "bf.type_id = f.type_id");
    $result->innerJoin("boards", "b", "b.board_id = bf.type_board_id");
    $this->board->board_filetypes_allowed = $result->condition("type_board_id", $this->board->board_id)
                                                     ->orderBy("type_ext")
                                                     ->execute()
                                                     ->fetchAssoc();

    require_once( kxFunc::getAppDir('board') .'/classes/rebuild.php' );
    $this->environment->set('kx:classes:board:rebuild:id', new rebuild( $environment ) );
    $this->rebuild = $this->environment->get('kx:classes:board:rebuild:id');
    $this->rebuild->board = $this->board;
    require_once( kxFunc::getAppDir('board') .'/classes/upload.php' );
    $this->environment->set('kx:classes:board:upload:id', new upload( $environment ) );
  }
  
  public function validPost() {
    if (
      ( /* A message is set */
        isset($this->request['message'])
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
    
    if ($postData['is_reply']) {
      if (!$postClass->checkEmpty($postData)) {
        kxFunc::showError(_gettext('A message is required for a reply.'));
      }
    }
    else {
      $result = $this->db->select("posts")
                         ->countQuery()
                         ->condition("post_board", $this->board->board_id)
                         ->condition("post_deleted",0)
                         ->condition("post_subject", substr($postData['subject'], 0, 74))
                         ->condition("post_parent", 0)
                         ->execute()
                         ->fetchField();
      if ($result > 0) {
        kxFunc::showError(_gettext('Duplicate thread subject'), _gettext('Text boards may have only one thread with a unique subject. Please pick another.'));
      }
    }
    if ($this->board->board_locked == 1 && ($postData['user_authority'] != 1 && $postData['user_authority'] != 2)) {
      kxFunc::showError(_gettext('Sorry, this board is locked and can not be posted in.'));
    }
    else {
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
      
      $post['post_id'] = $postClass->makePost($postData, $post, array(), $_SERVER['REMOTE_ADDR'], $commands['sticky'], $commands['lock'], $this->board->board_id);

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
        //$this->regenerateThreads($post['post_id']);
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
    $i = 0;

    $postsperpage =	$this->environment->get('kx:display:txtthreads');
    $totalpages = $this->rebuild->calcTotalPages()-1;
    
    $this->dwoo_data['numpages'] = $totalpages;


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
        if ($this->rebuild->markThread($thread)) {
          $this->RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->dwoo_data['replythread'] = 0;
        }
        $thread = $this->rebuild->formatPost($thread, true);
        $omitids = array();
        
        //-----------------------------------------------------------------------------------------------------------------------------------
        // Process stickies without using prepared statements (because they have a different range than regular threads).
        // Since there's usually very few to no stickies we can get away with this with minimal performance impact.
        //-----------------------------------------------------------------------------------------------------------------------------------
        if ($thread->post_stickied == 1) {
          $posts = $this->db->select("posts")
                            ->condition("post_board", $this->board->board_id)
                            ->condition("post_parent", $thread->post_id)
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
          if (empty($this->board->buildPageResults) || !($this->board->buildPageResults instanceof kxDBStatementInterface)) { 
            $this->board->buildPageResults = $this->db->select("posts")
                    ->fields("posts")
                    ->where("post_board = ? AND post_parent = ?")
                    ->orderBy("post_id", "DESC")
                    ->range(0, kxEnv::Get('kx:display:replies'))
                    ->build();
          }
          $this->board->buildPageResults->execute(array($this->board->board_id, $thread->post_id));
          $posts = $this->board->buildPageResults->fetchAll();
        }
        foreach ($posts as &$post) {
          $post->post_name = '';
          $post->post_email = '';
          $post->post_tripcode = _gettext('Deleted');
          $post->post_message = '<span style="font-color:gray">'._gettext('This post has been deleted.').'</span>';
          $post = $this->formatPost($post, true);
        }
        $this->rebuild->getOmittedPosts($thread, array(), false);
        $posts = array_reverse($posts);
        array_unshift($posts, $thread);
        $outPosts[] = $posts;

      }

      $this->dwoo_data['posts'] = $outThread;
      
      $this->dwoo_data['file_path'] = kxEnv::Get('kx:paths:boards:path') . '/' . $this->board->board_name;
      $this->footer(false, (microtime(true) - kxEnv::Get('kx:executiontime:start')));
      $this->pageHeader(0);
      $content = kxTemplate::get('board/text/board_page', $this->dwoo_data);

      if ($i == 0) {
        $page = KX_BOARD . '/'.$this->board->board_name.'/'.kxEnv::Get('kx:pages:first');
      } else {
        $page = KX_BOARD . '/'.$this->board->board_name.'/'.$i.'.html';
      }
      kxFunc::outputToFile($page, $content, $this->board->board_name);
      $i++;
    }
    // Generate lists
    $numpostsleft = $this->db->select("posts")
                             ->countQuery()
                             ->condition("post_board", $this->board->board_id)
                             ->condition("post_deleted", 0)
                             ->condition("post_parent", 0)
                             ->execute()
                             ->fetchField();
    $liststooutput = floor(($numpostsleft-1) / 40);
    $this->dwoo_data['numpages'] = $liststooutput+1;
    $listpage = 0;
    $currentpostwave = 0;
    while ($numpostsleft>0) {
      $this->dwoo_data['thispage'] = ($listpage+1);
      $executiontime_start_list = microtime(true);
      $page = $this->pageHeader(0, $currentpostwave, $liststooutput);
      $this->footer(false, (microtime(true)-$executiontime_start_list), true);
      if ($listpage==0) {
        kxFunc::outputToFile(KX_BOARD . '/'.$this->board->board_name.'/list.html', $page, $this->board->board_name);
      } else {
        kxFunc::outputToFile(KX_BOARD . '/'.$this->board->board_name.'/list'.($listpage+1).'.html', $page, $this->board->board_name);
      }
      $currentpostwave += 40;
      $numpostsleft -= 40;
      $listpage++;
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

          $temp = $this->rebuild->buildThread($id);
          $dwoo_data = array();
          //---------------------------------------------------------------------------------------------------
          // Okay, this may seem confusing, but we're caching this so we can use it as a prepared statement
          // intead of executing it every time. This is only really useful if we're regenerating all threads,
          // but the perfomance impact otherwise is minimal.
          //----------------------------------------------------------------------------------------------------
          if (!isset($this->board->preparedThreads)) {
            $this->board->preparedThreads = $this->db->select("posts")
                                                     ->fields("posts")
                                                     ->where("post_board = " . $this->board->board_id . " AND (post_id = ? OR post_parent = ?)")
                                                     ->orderBy("post_id")
                                                     ->build();
          }
          // Since we prepared the statement earlier, we just need to execute it.
          $this->board->preparedThreads->execute(Array($id, $id));
          $thread = $this->board->preparedThreads->fetchAll();
          if ($thread[0]->post_deleted == 1) {
            return;
          }
          foreach ($thread as &$post) {
            $post->post_name = '';
            $post->post_email = '';
            $post->post_tripcode = _gettext('Deleted');
            $post->post_message = '<span style="font-color:gray">'._gettext('This post has been deleted.').'</span>';
            $post = $this->rebuild->formatPost($post, false);
          }
          
         
          $this->board->header = $this->pageHeader($id);
          $this->board->postbox = $this->postBox($id);
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
          //$replyHeader = kxTemplate::get('img_reply_header', $this->dwoo_data);
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
        $content = kxTemplate::get('txt_thread', $this->dwoo_data);
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
  public function pageHeader($replythread = 0, $liststart = 0, $liststooutput = -1) {
    $this->dwoo_data += $this->rebuild->pageHeader();
    $this->dwoo_data['replythread'] = $replythread;
    $this->dwoo_data['ku_styles'] = explode(':', kxEnv::Get('kx:css:txtstyles'));
    $this->dwoo_data['ku_defaultstyle'] = (!empty($this->board->board_style_default) ? ($this->board->board_style_default) : (kxEnv::Get('kx:css:txtdefault')));
    if ($liststooutput == -1) {
      $this->dwoo_data['isindex'] = true;
    } else {
      $this->dwoo_data['isindex'] = false;
    }
    if ($replythread != 0) $this->dwoo_data->assign('isthread', true);
    $header = kxTemplate::get('txt_header', $this->dwoo_data);

    if ($replythread == 0) {
      $startrecord = ($liststooutput >= 0 || $this->board->board_compact_list) ? 40 : kxEnv::Get('kx:display:txtthreads');
      $threads = $this->db->select("posts")
                          ->fields("posts")
                          ->condition("post_board", $this->board->board_id)
                          ->condition("post_parent", 0)
                          ->condition("post_deleted", 0)
                          ->orderBy("post_stickied", "DESC")
                          ->orderBy("post_bumped", "DESC")
                          ->range($liststart, $startrecord)
                          ->execute()
                          ->fetchAll();
      $results = $this->db->select("posts")
                          ->countQuery()
                          ->where("post_board = ? AND post_parent = ?")
                          ->build();
      foreach($threads AS &$thread) {
        $results->execute(array($this->board->board_id, $thread->post_id));
        $replycount = $results->fetchField();
        $thread['replies'] = $replycount;
      }
      unset($thread);
      $this->dwoo_data['threads'] = $threads;
      $header .= kxTemplate::get('txt_threadlist', $this->dwoo_data);
    }
    //return kxTemplate::get('global_board_header', $this->dwoo_data).kxTemplate::get('img_header', $this->dwoo_data);
  }

  /**
   * Display the page footer
   *
   * @param boolean $noboardlist Force the board list to not be displayed
   * @param string $executiontime The time it took the page to be created
   * @return string The generated footer
   */
  public function footer($noboardlist = false, $executiontime = 0) {
    $this->dwoo_data += $this->rebuild->footer($noboardlist, $executiontime);
    //return kxTemplate::get('img_footer', $this->dwoo_data).kxTemplate::get('global_board_footer', $this->dwoo_data);
  }
}