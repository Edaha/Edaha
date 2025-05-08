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
 * Base board (that other board types should extend)
 * Last Updated: $Date: 2011-08-14 18:09:42 -0400 (Sun, 14 Aug 2011) $
 *
 * @author      $Author: Sazpaimon $
 *
 * @package     kusaba
 * @subpackage  board
 *
 * @version     $Revision: 334 $
 *
 */

if (!defined('KUSABA_RUNNING')) {
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}

abstract class public_board_base_baseboard extends kxCmd
{
  /**
   * Board data
   *
   * @access  public
   * @var     object  Edaha\Entities\Board
   */
  public $board;

  public $archive_dir;

  // public $embeds; // TODO Embeds will be a type of PostAttachment

  /**
   * Arguments eventually being sent to twig
   *
   * @var Array()
   */
  protected $twigData;

  protected $postClass;

  protected $buildPageResults;

  protected $preparedThreads;

  /**
   * Run requested method
   *
   * @access  public
   * @param  object    Registry object
   * @param  object    Board object
   * @param  int       Thread ID
   * @param  string    Method to run
   * @return  void
   */
  public function exec(kxEnv $environment)
  {
    $this->board = $this->entityManager->find(\Edaha\Entities\Board::class, $this->request['board_id']);    

    $this->environment->set('kx:classes:board:id', $this->board);

    require_once kxFunc::getAppDir('board') . '/classes/upload.php';
    $this->environment->set('kx:classes:board:upload:id', new upload($environment));

    require_once kxFunc::getAppDir('core') . '/classes/parse.php';
    $this->environment->set('kx:classes:board:parse:id', new parse($environment));
  }

  public function parseData($message)
  {

    $message = trim($message);
    //$this->parser->cutWord($message, (kxEnv::get('kx:limits:linelength') / 15));
    //var_dump($message);
    //$message = htmlspecialchars($message, ENT_QUOTES, kxEnv::get('kx:charset'));
    if (kxEnv::Get('kx:posts:makelinks')) {
      // $this->environment->get('kx:classes:board:parse:id')->makeClickable($message);
    }
    // $this->environment->get('kx:classes:board:parse:id')->clickableQuote($message);
    // $this->environment->get('kx:classes:board:parse:id')->coloredQuote($message);
    // $this->environment->get('kx:classes:board:parse:id')->bbCode($message);
    $this->environment->get('kx:classes:board:parse:id')->wordFilter($message);
    $this->environment->get('kx:classes:board:parse:id')->checkNotEmpty($message);
    return $message;
  }

  public function processPost(\Edaha\Entities\Post $post)
  {
    if (empty($this->postClass)) {
      $this->postClass = $this->environment->get('kx:classes:board:posting:id');
    }

    $this->postClass->forcedAnon($post);
    $this->postClass->handleTripcode($post);
    // TODO Move to postProcess
    // $commands = $this->postClass->checkPostCommands($postData);
    $this->postClass->checkEmptyReply($post);

    $post->subject = substr($post->subject, 0, 74); // TODO Why? Do I care?
    $post->message = $this->parseData($post->message);
    
    // TODO Move to postProcess
    // $this->postClass->setCookies($post);

    // Regenerate board pages
    $this->regeneratePages();

    // Regenerate thread pages
    if ($post->is_thread) {
      $this->regenerateThreads($post->id);
    } else {
      $this->regenerateThreads($post->parent->id);
    }

    {
      // TODO Readd once PostAttachment is implemented
      // $files = $this->doUpload($postData);

      // if (isset($postData['thread_info']['tag'])) {
      //   $post['tag'] = $postData['thread_info']['tag'];
      // }

      //Needs 1.0 equivalent
      // $post = hook_process('posting', $post);

      // Trim any threads which have been pushed past the limit, or exceed the maximum age limit
      //kxExec:TrimToPageLimit($board_class->board);
    }
  }

  public function doUpload($postData)
  {
    return $this->postClass->doUpload($postData, $this->board);
  }

  public function getEmbeds()
  {
    $this->embeds = [];
    $results = $this->db->select("embeds")
      ->fields("embeds", ["embed_ext"])
      ->execute()
      ->fetchAll();
    foreach ($results as $line) {
      $this->embeds[] = $line->embed_ext;
    }
    return $this->embeds;
  }

  public function regeneratePages()
  {
    //-----------------------------------------
    // Setup
    //-----------------------------------------
    $executiontime_start_page = microtime(true);
    // TODO This only applies to certain types of boards and should move to them
    // $this->twigData['filetypes'] = $this->getEmbeds();

    $i = 0;

    if (!isset($postsperpage)) {
      $postsperpage = $this->environment->get('kx:display:imgthreads');
    }

    $numposts = count($this->board->posts);
    $totalpages = kxFunc::pageCount($this->board->board_type, ($numposts - 1)) - 1;

    // If no posts, $totalpages==-2, which causes the board to not regen.
    if ($totalpages < 0) {
      $totalpages = 0;
    }

    $this->twigData['numpages'] = $totalpages;

    //-----------------------------------------
    // Run through each page
    //-----------------------------------------

    while ($i <= $totalpages) {
      $this->twigData['thispage'] = $i;

      //--------------------------------------------------------------------------------------------------
      // Grab our threads, stickies go first, then follow by bump time, then run through them
      //--------------------------------------------------------------------------------------------------
      $threads = $this->entityManager->getRepository(\Edaha\Entities\Post::class)
        ->getBoardPaginatedThreads($this->board->id, $i, $postsperpage);

      $outThread = [];
      foreach ($threads as $thread) {

        //------------------------------------------------------------------------------------------
        // If the thread is on the page set to mark, and hasn't been marked yet, mark it
        //------------------------------------------------------------------------------------------
        if ($this->markThread($thread, $i)) {
          $this->RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->twigData['replythread'] = 0;
        }
        // $thread = $this->buildPost($thread, true);
        $outThread[] = $this->buildThread($thread);

      }
      if (!isset($embeds)) {
        $embeds = $this->db->select("embeds")
          ->fields("embeds")
          ->execute()
          ->fetchAll();
        $this->twigData['embeds'] = $embeds;
      }

      $this->twigData['posts'] = $outThread;

      //print_r($this->board);
      $this->twigData['file_path'] = KX_BOARD . '/' . $this->board->directory;

      // Make required folders
      @mkdir($this->twigData['file_path'], 0777, true);
      @mkdir($this->twigData['file_path'] . '/src/', 0777, true);
      @mkdir($this->twigData['file_path'] . '/thumb/', 0777, true);
      @mkdir($this->twigData['file_path'] . '/res/', 0777, true);

      $this->twigData['board'] = $this->board;
      $board_options = $this->entityManager->getRepository(\Edaha\Entities\BoardOption::class)
        ->getOptionsByBoard($this->board->id);
      foreach ($board_options as $option) {
        $this->twigData['board_options'][$option['name']] = $option['value'];
      }

      $this->footer(false, (microtime(true) - $executiontime_start_page));
      $this->pageHeader(0);
      $this->postBox(0);

      $content = kxTemplate::get('board/' . $this->boardType . '/board_page', $this->twigData, true);

      if ($i == 0) {
        $page = KX_BOARD . '/' . $this->board->directory . '/' . kxEnv::Get('kx:pages:first');
      } else {
        $page = KX_BOARD . '/' . $this->board->directory . '/' . $i . '.html';
      }
      //echo "<br />$page";
      //die($content);
      kxFunc::outputToFile($page, $content, $this->board->directory);
      $i++;
    }
  }

  public function buildThread($thread)
  {
    $this->twigData['threads'][] = $thread;
    // $thread->replies = $this->getOmittedPosts($thread, $tempPosts[1]);
    // $thread->images = $this->getOmittedFiles($thread, $tempPosts[1]);
    // $posts = array_reverse($tempPosts[0]);
    // array_unshift($posts, $thread);
    return $thread;
  }

  public function getThreadRepliesToDisplay($thread)
  {
    if ($thread->is_stickied == 1) {
      $thread_replies = $thread->getLastNReplies(kxEnv::Get('kx:display:stickyreplies'));
    } else {
      $thread_replies = $thread->getLastNReplies(kxEnv::Get('kx:display:replies'));
    }
    return $thread_replies;
  }

  public function getOmittedPosts(&$thread, $omitids = [])
  {

    $replycount = $this->db->select("posts");
    $replycount->condition("board_id", $this->board->board_id)
      ->condition("parent_post_id", $thread->post_id);
    if ($this->board->board_type != 1) {
      $replycount->condition("is_deleted", 0);
    }
    if (!empty($omitids)) {
      $replycount->condition("post_id", $omitids, "NOT IN");
    }
    $replycount = $replycount->countQuery()
      ->execute()
      ->fetchField();
    return $replycount;
  }

  public function getOmittedFiles(&$thread, $omitids = [])
  {
    //---------------------------------------------------------------------------------------------------
    // Get the number of file-replies for this thread, minus the ones that are already being shown.
    //----------------------------------------------------------------------------------------------------
    $replycount = $this->db->select("posts");
    $replycount->innerJoin("post_files", "f", "post_id = file_post AND file_board = board_id");
    $replycount->condition("board_id", $this->board->board_id)
      ->condition("parent_post_id", $thread->post_id)
      ->condition("is_deleted", 0)
      ->condition("file_md5", "", "!=");
    if (!empty($omitids)) {
      $replycount->condition("file_post", $omitids, "NOT IN");
    }
    $replycount = $replycount->countQuery()
      ->execute()
      ->fetchField();
    return $replycount;
  }

  public function buildPost($post, $page)
  {
    // TODO: This probably belongs elsewhere. Also deserves to be an array of attachment objects vs. this set of arrays.
    // $post_files = $this->db->select("post_files")
    //   ->fields("post_files")
    //   ->condition("file_board", $this->board->board_id)
    //   ->condition("file_post", $post->post_id)
    //   ->execute()
    //   ->fetchAll();
    // foreach ($post_files as $line) {
    //   $post->file_name[] = $line->file_name;
    //   $post->file_type[] = $line->file_type;
    //   $post->file_original[] = $line->file_original;
    //   $post->file_size[] = $line->file_size;
    //   $post->file_size_formatted[] = $line->file_size_formatted;
    //   $post->file_image_width[] = $line->file_image_width;
    //   $post->file_image_height[] = $line->file_image_height;
    //   $post->file_thumb_width[] = $line->file_thumb_width;
    //   $post->file_thumb_height[] = $line->file_thumb_height;
    // }
    $post = $this->formatPost($post, $page);
    // if (!empty($post->file_type)) {
    //   foreach ($post->file_type as $key => $type) {
    //     if (isset($this->embeds) && in_array($type, $this->embeds)) {
    //       $post->videobox = $this->embeddedVideoBox($post);
    //     }

    //     if ($type == 'mp3' /*&& $this->board['loadbalanceurl'] == ''*/) {

    //       // Initialize getID3 engine
    //       $getID3 = new getID3;

    //       $post->id3[$key] = $getID3->analyze(KX_BOARD . '/' . $this->board->board_name . '/src/' . $post->file_name[$key] . '.mp3');
    //       getid3_lib::CopyTagsToComments($post->id3[$key]);
    //     }
    //     if (!in_array($type, ['jpg', 'gif', 'png', 'webp', '']) && !in_array($type, $this->embeds)) {
    //       if (!isset($filetype_info[$type])) {
    //         $filetype_info[$type] = kxFunc::getFileTypeInfo($type);
    //       }

    //       $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/public/filetypes/' . $filetype_info[$type][0];
    //       if ($post->file_thumb_width[$key] != 0 && $post->file_thumb_height[$key] != 0) {
    //         if (file_exists(KX_BOARD . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.jpg')) {
    //           $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.jpg';
    //         } elseif (file_exists(KX_BOARD . '/' . $this->board['name'] . '/thumb/' . $post['file'] . 's.png')) {
    //           $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.png';
    //         } elseif (file_exists(KX_BOARD . '/' . $this->board['name'] . '/thumb/' . $post['file'] . 's.gif')) {
    //           $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.gif';
    //         } else {
    //           $post->file_thumb_width[$key] = $filetype_info[$type][1];
    //           $post->file_thumb_height[$key] = $filetype_info[$type][2];
    //         }
    //       } else {
    //         $post->file_thumb_width[$key] = $filetype_info[$type][1];
    //         $post->file_thumb_height[$key] = $filetype_info[$type][2];
    //       }
    //     }
    //   }
    // }
    return $post;
  }

  // TODO: This should be done in the templates
  public function formatPost($post, $page)
  {
    // $dateEmail = (empty($this->board->board_default_name)) ? $post->email : 0;
    // $post->message = stripslashes($this->formatLongMessage($post->message, $this->board->board_name, (($post->parent_post_id == 0) ? ($post->post_id) : ($post->parent_post_id)), $page));
    // $post->timestamp_formatted = kxFunc::formatDate($post->created_at_timestamp, 'post', $this->environment->get('kx:language:currentlocale'), $dateEmail);
    // $post->reflink = $this->formatReflink($this->board->board_name, (($post->parent_post_id == 0) ? ($post->post_id) : ($post->parent_post_id)), $post->post_id, $this->environment->get('kx:language:currentlocale'));
    return $post;
  }

  /**
   * Format a long message to be shortened if it exceeds the allowed length on a page
   *
   * @param string $message Post message
   * @param string $board Board directory
   * @param integer $threadid Thread ID
   * @param boolean $page Is rendering for a page
   * @return string The formatted message
   */
  public function formatLongMessage($message, $board, $threadid, $page)
  {
    $output = '';
    if ((strlen($message) > kxEnv::Get('kx:limits:linelength') || count(explode('<br />', $message)) > 15) && $page) {
      $message_exploded = explode('<br />', $message);
      $message_shortened = '';
      for ($i = 0; $i <= 14; $i++) {
        if (isset($message_exploded[$i])) {
          $message_shortened .= $message_exploded[$i] . '<br />';
        }
      }
      if (strlen($message_shortened) > kxEnv::Get('kx:limits:linelength')) {
        $message_shortened = substr($message_shortened, 0, kxEnv::Get('kx:limits:linelength'));
      }

      //TODO need to add this
      //$message_shortened = closeOpenTags($message_shortened);

      if (strrpos($message_shortened, "<") > strrpos($message_shortened, ">")) {
        //We have a partially opened tag we need to get rid of.
        $message_shortened = substr($message_shortened, 0, strrpos($message_shortened, "<"));
      }

      $output = $message_shortened . '<div class="abbrev">' . "\n" .
      '  ' . sprintf(_('Message too long. Click %shere%s to view the full text.'), '<a href="' . kxEnv::Get('kx:paths:boards:folder') . $board . '/res/' . $threadid . '.html">', '</a>') . "\n" .
        '</div>' . "\n";
    } else {
      $output .= $message . "\n";
    }

    return $output;
  }

  /**
   * Format the provided input into a reflink, which follows the Japanese locale if it is set.
   */
  public function formatReflink($post_board, $post_thread_start_id, $post_id, $locale = 'en')
  {
    $return = '  ';

    $reflink_noquote = '<a href="' . kxEnv::Get('kx:paths:boards:folder') . '/' . $post_board . '/res/' . $post_thread_start_id . '.html#' . $post_id . '" onclick="return kusaba.highlight(\'' . $post_id . '\');">';

    $reflink_quote   = '<a href="' . kxEnv::Get('kx:paths:boards:folder') . '/' . $post_board . '/res/' . $post_thread_start_id . '.html#i' . $post_id . '" onclick="return kusaba.insert(\'>>' . $post_id . '\\n\');">';

    if ($locale == 'ja') {
      $return .= $reflink_quote . kxFunc::formatJapaneseNumbers($post_id) . '</a>' . $reflink_noquote . '?</a>';
    } else {
      $return .= $reflink_noquote . 'No.&nbsp;' . '</a>' . $reflink_quote . $post_id . '</a>';
    }

    return $return . "\n";
  }

  public function regenerateThread(\Edaha\Entities\Post $thread)
  {
    for ($i = 0; $i < 3; $i++) {
      if ((!$i > 0 && kxEnv::Get('kx:extras:firstlast')) || ($i == 1 && count($thread->replies) < 50) || ($i == 2 && count($thread->replies) < 100)) {
        break;
      }
      if ($i == 0) {
        $lastBit = "";
        $executiontime_start_thread = microtime(true);

        $post = $this->buildPost($thread, false);

        //-----------------------------------------------------------------------
        // When using a pointer in a foreach, the $value variable persists
        // as the last index of an array, we can use this to our advantage here.
        //-----------------------------------------------------------------------
        // TODO Readd Post Spy
        // if (kxEnv::Get('kx:extras:postspy')) {
        //   $twigData['lastid'] = $post->post_id;
        // }
        // // Now we can get rid of it
        // unset($post);

        $this->pageHeader($thread->id);
        $this->postBox($thread->id);
        //-----------
        // Dwoo-hoo
        //-----------
        $this->twigData['replythread'] = $thread->id;
        $this->twigData['threadid'] = $thread->id;
        $this->twigData['thread'] = $thread;
        $this->twigData['replycount'] = count($this->twigData['posts']) - 1;
        $this->footer(false, (microtime(true) - $executiontime_start_thread));

      } else if ($i == 1) {
        $lastBit = "+50";
        $this->twigData['modifier'] = "last50";

        // Grab the last 50 replies
        $this->twigData['posts'] = array_slice($thread, -50, 50);
        // Add the thread to the top of this, since it wont be included in the result
        array_unshift($this->twigData['posts'], $thread[0]);

      } elseif ($i == 2) {
        $lastBit = "-100";
        $this->twigData['modifier'] = "first100";

        // Grab the first 100 posts
        $this->twigData['posts'] = array_slice($thread, 0, 100);
      }
      $this->twigData['board'] = $this->board;
      //print_r($this->twigData);
      $content = kxTemplate::get('board/' . $this->board->type . '/thread', $this->twigData, true);
      kxFunc::outputToFile(KX_BOARD . '/' . $this->board->directory . $this->archive_dir . '/res/' . $thread->id . $lastBit . '.html', $content, $this->board->directory);
    }
    
  }
  /**
   * Regenerate each thread's corresponding html file, starting with the most recently bumped
   */
  public function regenerateThreads($id = 0)
  {
    // TODO Replaced by Attachments concept
    // $embeds = $this->db->select("embeds")
    //   ->fields("embeds")
    //   ->execute()
    //   ->fetchAll();
    // $this->twigData['embeds'] = $embeds;
    // No ID? Get every thread.
    if ($id == 0) {

      // Okay let's do this!
      $threads = $this->board->getAllThreads();

      if (count($threads) > 0) {
        foreach ($threads as $thread) {
          $this->regenerateThread($thread);
        }
      }
    } else { 
      $this->regenerateThread($this->entityManager->find(\Edaha\Entities\Post::class, $id));
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
  public function pageHeader($replythread = 0)
  {
    $this->twigData['title'] = '';

    if (kxEnv::Get('kx:pages:dirtitle')) {
      $this->twigData['title'] .= '/' . $this->board->directory . '/ - ';
    }
    $this->twigData['title'] .= $this->board->name;

    $this->twigData['htmloptions'] = ((kxEnv::Get('kx:misc:locale') == 'he' && empty($this->board->locale)) || $this->board->locale == 'he') ? ' dir="rtl"' : '';
    $this->twigData['locale'] = $this->board->locale;
    $this->twigData['board'] = $this->board;

    $this->twigData['boardlist'] = kxFunc::visibleBoardList();
    $this->twigData['replythread'] = $replythread;
    $this->twigData['ku_styles'] = explode(':', kxEnv::Get('kx:css:imgstyles'));
    $this->twigData['ku_defaultstyle'] = (!empty($this->board->board_style_default) ? ($this->board->board_style_default) : (kxEnv::Get('kx:css:imgdefault')));
  }

  /**
   * Generate the postbox area
   *
   * @param integer $replythread The ID of the thread being replied to.  0 if not replying
   * @param string $postboxnotice The postbox notice
   * @return string The generated postbox
   */
  public function postBox($replythread = 0)
  {
    if (kxEnv::Get('kx:extras:blotter')) {
      $this->twigData['blotter'] = kxFunc::getBlotter();
      $this->twigData['blotter_updated'] = kxFunc::getBlotterLastUpdated();
    }
  }

  /**
   * Display the page footer
   *
   * @param boolean $noboardlist Force the board list to not be displayed
   * @param string $executiontime The time it took the page to be created
   * @param boolean $hide_extra Hide extra footer information, and display the manage link
   */
  public function footer($noboardlist = false, $executiontime = 0, $hide_extra = false)
  {
    if ($noboardlist || $hide_extra) {
      $this->twigData['boardlist'] = "";
    }

    if ($executiontime) {
      $this->twigData['executiontime'] = round($executiontime, 2);
    }
  }

  public function markThread($thread, $i)
  {
    if ($this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
      $this->db->update("posts")
        ->fields([
          'deleted_at_timestamp' => time() + 7200,
        ])
        ->condition("post_board", $this->board->board_id)
        ->condition("post_id", $thread->post_id)
        ->execute();
      return true;
    }
    return false;
  }
}
