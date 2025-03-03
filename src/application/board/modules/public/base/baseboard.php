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

class public_board_base_baseboard extends kxCmd
{
  /**
   * Board data
   *
   * @access  public
   * @var     object  stdClass
   */
  public $board;

  public $archive_dir;

  /**
   * Arguments eventually being sent to twig
   *
   * @var Array()
   */
  protected $twigData;

  protected $postClass;

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
      ->fields("f", ["type_ext"]);
    $result->innerJoin("board_filetypes", "bf", "bf.type_id = f.type_id");
    $result->innerJoin("boards", "b", "b.board_id = bf.board_id");
    $this->board->board_filetypes_allowed = $result->condition("bf.board_id", $this->board->board_id)
      ->orderBy("type_ext")
      ->execute()
      ->fetchCol();

    $this->board->boardlist = kxFunc::visibleBoardList();
    $this->environment->set('kx:classes:board:id', $this->board);

    require_once kxFunc::getAppDir('board') . '/classes/upload.php';
    $this->environment->set('kx:classes:board:upload:id', new upload($environment));
    require_once kxFunc::getAppDir('core') . '/classes/parse.php';
    $this->environment->set('kx:classes:board:parse:id', new parse($environment));
  }

  public function parseData(&$message)
  {

    $message = trim($message);
    //$this->cutWord($message, (kxEnv::get('kx:limits:linelength') / 15));
    //var_dump($message);
    //$message = htmlspecialchars($message, ENT_QUOTES, kxEnv::get('kx:charset'));
    if (kxEnv::Get('kx:posts:makelinks')) {
      $this->makeClickable($message);
    }
    $this->clickableQuote($message);
    $this->coloredQuote($message);
    $this->bbCode($message);
    $this->wordFilter($message);
    $this->checkNotEmpty($message);
    return $message;
  }

  public function cutWord(&$message, $where)
  {
    $this->environment->get('kx:classes:board:parse:id')->cutWord($message, $where);
  }

  public function makeClickable(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->makeClickable($message);
  }

  public function clickableQuote(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->clickableQuote($message);
  }

  public function coloredQuote(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->coloredQuote($message);
  }

  public function bbCode(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->bbCode($message);
  }

  public function wordFilter(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->wordFilter($message);
  }

  public function checkNotEmpty(&$message)
  {
    $this->environment->get('kx:classes:board:parse:id')->checkNotEmpty($message);
  }

  public function processPost($postData)
  {

    if (empty($this->postClass)) {
      $this->postClass = $this->environment->get('kx:classes:board:posting:id');
    }

    $this->checkFields($postData);

    if ($this->board->board_locked == 1 && ($postData['user_authority'] != 1 && $postData['user_authority'] != 2)) {
      kxFunc::showError(_('Sorry, this board is locked and can not be posted in.'));
    } else {
      $files = $this->doUpload($postData);
      $this->postClass->forcedAnon($postData, $this->board);
      $nameAndTrip = $this->postClass->handleTripcode($postData);
      $post_passwordmd5 = ($postData['post_fields']['postpassword'] == '') ? '' : md5($postData['post_fields']['postpassword']);
      $commands = $this->postClass->checkPostCommands($postData);
      $this->postClass->checkEmptyReply($postData);

      $post = [];
      $post['board'] = $this->board->board_name;
      $post['name'] = substr($nameAndTrip[0], 0, 74);
      $post['name_save'] = true;
      $post['tripcode'] = $nameAndTrip[1];
      $post['email'] = substr($postData['post_fields']['email'], 0, 74);
      // First array is the converted form of the japanese characters meaning sage, second meaning age
      // Needs converting
      //$ords_email = unistr_to_ords($post_email);
      $ords_email = [];
      if (strtolower($this->request['em']) != 'sage' && $ords_email != [19979, 12370] && strtolower($this->request['em']) != 'age' && $ords_email != [19978, 12370] && $this->request['em'] != 'return' && $this->request['em'] != 'noko') {
        $post['email_save'] = true;
      } else {
        $post['email_save'] = false;
      }
      $post['subject'] = substr($postData['post_fields']['subject'], 0, 74);
      $post['message'] = $postData['thread_info']['message'];
      if (isset($postData['thread_info']['tag'])) {
        $post['tag'] = $postData['thread_info']['tag'];
      }

      //Needs 1.0 equivalent
      // $post = hook_process('posting', $post);

      $post['post_id'] = $this->postClass->makePost($postData, $post, $files, $_SERVER['REMOTE_ADDR'], $commands['sticky'], $commands['lock'], $this->board);

      $this->postClass->modPost(array_merge($postData, $post), $this->board);
      $this->postClass->setCookies($post);
      $this->postClass->checkSage($postData, $this->board);
      $this->postClass->updateThreadWatch($postData, $this->board);

      // Trim any threads which have been pushed past the limit, or exceed the maximum age limit
      //kxExec:TrimToPageLimit($board_class->board);

      // Regenerate board pages
      $this->regeneratePages();
      if ($postData['thread_info']['parent'] == 0) {
        // Regenerate the thread
        $this->regenerateThreads($post['post_id']);
      } else {
        // Regenerate the thread
        $this->regenerateThreads($postData['thread_info']['parent']);
      }
    }
  }

  public function doUpload($postData)
  {
    return $this->postClass->doUpload($postData, $this->board);
  }

  public function getEmbeds()
  {
    $this->board->embeds = [];
    $results = $this->db->select("embeds")
      ->fields("embeds", ["embed_ext"])
      ->execute()
      ->fetchAll();
    foreach ($results as $line) {
      $this->board->embeds[] = $line->embed_ext;
    }
    return $this->board->embeds;
  }

  public function regeneratePages()
  {
    //-----------------------------------------
    // Setup
    //-----------------------------------------
    $executiontime_start_page = microtime(true);
    $this->twigData['filetypes'] = $this->getEmbeds();

    $i = 0;

    if (!isset($postsperpage)) {
      $postsperpage = $this->environment->get('kx:display:imgthreads');
    }

    $numposts = $this->db->select("posts")
      ->fields("posts")
      ->condition("post_board", $this->board->board_id)
      ->condition("post_parent", 0)
      ->condition("post_deleted", 0)
      ->countQuery()
      ->execute()
      ->fetchField();
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
      $outThread = [];
      foreach ($threads as &$thread) {

        //------------------------------------------------------------------------------------------
        // If the thread is on the page set to mark, and hasn't been marked yet, mark it
        //------------------------------------------------------------------------------------------
        if ($this->markThread($thread, $i)) {
          $this->RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->twigData['replythread'] = 0;
        }
        $thread = $this->buildPost($thread, true);
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
      $this->twigData['file_path'] = KX_BOARD . '/' . $this->board->board_name;

      // Make required folders
      @mkdir($this->twigData['file_path'], 0777, true);
      @mkdir($this->twigData['file_path'] . '/src/', 0777, true);
      @mkdir($this->twigData['file_path'] . '/thumb/', 0777, true);
      @mkdir($this->twigData['file_path'] . '/res/', 0777, true);

      $this->twigData['board'] = $this->board;

      $this->footer(false, (microtime(true) - $executiontime_start_page));
      $this->pageHeader(0);
      $this->postBox(0);

      $content = kxTemplate::get('board/' . $this->boardType . '/board_page', $this->twigData, true);

      if ($i == 0) {
        $page = KX_BOARD . '/' . $this->board->board_name . '/' . kxEnv::Get('kx:pages:first');
      } else {
        $page = KX_BOARD . '/' . $this->board->board_name . '/' . $i . '.html';
      }
      //echo "<br />$page";
      //die($content);
      kxFunc::outputToFile($page, $content, $this->board->board_name);
      $i++;
    }
  }

  public function buildThread($thread)
  {
    $tempPosts = $this->buildPageThread($thread, true);
    $thread->replies = $this->getOmittedPosts($thread, $tempPosts[1]);
    $thread->images = $this->getOmittedFiles($thread, $tempPosts[1]);
    $posts = array_reverse($tempPosts[0]);
    array_unshift($posts, $thread);
    return $posts;
  }

  public function buildPageThread($thread)
  {
    $omitids = [];

    //-----------------------------------------------------------------------------------------------------------------------------------
    // Process stickies without using prepared statements (because they have a different range than regular threads).
    // Since there's usually very few to no stickies we can get away with this with minimal performance impact.
    //-----------------------------------------------------------------------------------------------------------------------------------
    if ($thread->post_stickied == 1) {
      $posts = $this->db->select("posts")
        ->condition("post_board", $this->board->board_id)
        ->condition("post_parent", $thread->post_id)
        ->condition("post_deleted", 0)
        ->orderBy("post_id", "DESC")
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
          ->where("post_board = ? AND post_parent = ? AND post_deleted = 0")
          ->orderBy("post_id", "DESC")
          ->range(0, kxEnv::Get('kx:display:replies'))
          ->build();
      }
      $this->board->buildPageResults->execute([$this->board->board_id, $thread->post_id]);
      $posts = $this->board->buildPageResults->fetchAll();
    }
    foreach ($posts as &$post) {
      $omitids[] = $post->post_id;
      $post = $this->buildPost($post, true);
    }
    return [$posts, $omitids];
  }

  public function getOmittedPosts(&$thread, $omitids = [])
  {

    $replycount = $this->db->select("posts");
    $replycount->condition("post_board", $this->board->board_id)
      ->condition("post_parent", $thread->post_id);
    if ($this->board->board_type != 1) {
      $replycount->condition("post_deleted", 0);
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
    $replycount->innerJoin("post_files", "f", "post_id = file_post AND file_board = post_board");
    $replycount->condition("post_board", $this->board->board_id)
      ->condition("post_parent", $thread->post_id)
      ->condition("post_deleted", 0)
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
    $post_files = $this->db->select("post_files")
      ->fields("post_files")
      ->condition("file_board", $this->board->board_id)
      ->condition("file_post", $post->post_id)
      ->execute()
      ->fetchAll();
    foreach ($post_files as $line) {
      $post->file_name[] = $line->file_name;
      $post->file_type[] = $line->file_type;
      $post->file_original[] = $line->file_original;
      $post->file_size[] = $line->file_size;
      $post->file_size_formatted[] = $line->file_size_formatted;
      $post->file_image_width[] = $line->file_image_width;
      $post->file_image_height[] = $line->file_image_height;
      $post->file_thumb_width[] = $line->file_thumb_width;
      $post->file_thumb_height[] = $line->file_thumb_height;
    }
    $post = $this->formatPost($post, $page);
    if (!empty($post->file_type)) {
      foreach ($post->file_type as $key => $type) {
        if (isset($this->board->embeds) && in_array($type, $this->board->embeds)) {
          $post->videobox = $this->embeddedVideoBox($post);
        }

        if ($type == 'mp3' /*&& $this->board['loadbalanceurl'] == ''*/) {
          //Grab the ID3 info. TODO: Make this work for load-balanced boards.
          // include getID3() library
          require_once KX_ROOT . '/lib/getid3/getid3.php';

          // Initialize getID3 engine
          $getID3 = new getID3;

          $post->id3[$key] = $getID3->analyze(KX_BOARD . '/' . $this->board->board_name . '/src/' . $post->file_name[$key] . '.mp3');
          getid3_lib::CopyTagsToComments($post->id3[$key]);
        }
        if (!in_array($type, ['jpg', 'gif', 'png', 'webp', '']) && !in_array($type, $this->board->embeds)) {
          if (!isset($filetype_info[$type])) {
            $filetype_info[$type] = kxFunc::getFileTypeInfo($type);
          }

          $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/public/filetypes/' . $filetype_info[$type][0];
          if ($post->post_thumb_width != 0 && $post->post_thumb_height != 0) {
            if (file_exists(KX_BOARD . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.jpg')) {
              $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.jpg';
            } elseif (file_exists(KX_BOARD . '/' . $this->board['name'] . '/thumb/' . $post['file'] . 's.png')) {
              $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.png';
            } elseif (file_exists(KX_BOARD . '/' . $this->board['name'] . '/thumb/' . $post['file'] . 's.gif')) {
              $post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' . $this->board->board_name . '/thumb/' . $post->file_name[$key] . 's.gif';
            } else {
              $post->post_thumb_width[$key] = $filetype_info[$type][1];
              $post->post_thumb_height[$key] = $filetype_info[$type][2];
            }
          } else {
            $post->post_thumb_width[$key] = $filetype_info[$type][1];
            $post->post_thumb_height[$key] = $filetype_info[$type][2];
          }
        }
      }
    }
    return $post;
  }

  public function formatPost($post, $page)
  {
    $dateEmail = (empty($this->board->board_default_name)) ? $post->post_email : 0;
    $post->post_message = stripslashes($this->formatLongMessage($post->post_message, $this->board->board_name, (($post->post_parent == 0) ? ($post->post_id) : ($post->post_parent)), $page));
    $post->timestamp_formatted = kxFunc::formatDate($post->post_timestamp, 'post', $this->environment->get('kx:language:currentlocale'), $dateEmail);
    $post->reflink = $this->formatReflink($this->board->board_name, (($post->post_parent == 0) ? ($post->post_id) : ($post->post_parent)), $post->post_id, $this->environment->get('kx:language:currentlocale'));
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

  /**
   * Regenerate each thread's corresponding html file, starting with the most recently bumped
   */
  public function regenerateThreads($id = 0)
  {

    $numimages = 0;
    $embeds = $this->db->select("embeds")
      ->fields("embeds")
      ->execute()
      ->fetchAll();
    $this->twigData['embeds'] = $embeds;
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
        foreach ($threads as $thread) {
          $this->regenerateThreads($thread->post_id);
        }
      }
    } else {
      for ($i = 0; $i < 3; $i++) {
        if ((!$i > 0 && kxEnv::Get('kx:extras:firstlast')) || ($i == 1 && $replycount < 50) || ($i == 2 && $replycount < 100)) {
          break;
        }
        if ($i == 0) {
          $lastBit = "";
          $executiontime_start_thread = microtime(true);

          //---------------------------------------------------------------------------------------------------
          // Okay, this may seem confusing, but we're caching this so we can use it as a prepared statement
          // instead of executing it every time. This is only really useful if we're regenerating all threads,
          // but the perfomance impact otherwise is minimal.
          //----------------------------------------------------------------------------------------------------
          if (!isset($this->board->preparedThreads)) {
            $this->board->preparedThreads = $this->db->select("posts")
              ->fields("posts")
              ->where("post_board = " . $this->board->board_id . " AND (post_id = ? OR post_parent = ?) AND post_deleted = 0")
              ->orderBy("post_id")
              ->build();
          }
          // Since we prepared the statement earlier, we just need to execute it.
          $this->board->preparedThreads->execute([$id, $id]);
          $thread = $this->board->preparedThreads->fetchAll();
          foreach ($thread as &$post) {
            $post = $this->buildPost($post, false);
            if (!empty($post->file_type)) {
              foreach ($post->file_type as $type) {
                if (in_array($type, ['jpg', 'gif', 'png', '.webp', '.webp'])) {
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
            $twigData['lastid'] = $post->post_id;
          }
          // Now we can get rid of it
          unset($post);

          $this->board->header = $this->pageHeader($id);
          $this->board->postbox = $this->postBox($id);
          //-----------
          // Dwoo-hoo
          //-----------
          $this->twigData['numimages'] = $numimages;
          $this->twigData['replythread'] = $id;
          $this->twigData['threadid'] = $thread[0]->post_id;
          $this->twigData['posts'] = $thread;
          $replycount = (count($thread) - 1);
          $this->twigData['replycount'] = $replycount;
          if (!isset($this->board->footer)) {
            $this->board->footer = $this->footer(false, (microtime(true) - $executiontime_start_thread));
          }

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
        $content = kxTemplate::get('board/' . $this->boardType . '/thread', $this->twigData, true);
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
  public function pageHeader($replythread = 0)
  {
    $tpl = [];

    $tpl['htmloptions'] = ((kxEnv::Get('kx:misc:locale') == 'he' && empty($this->board->board_locale)) || $this->board->board_locale == 'he') ? ' dir="rtl"' : '';

    $tpl['title'] = '';

    if (kxEnv::Get('kx:pages:dirtitle')) {
      $tpl['title'] .= '/' . $this->board->board_name . '/ - ';
    }
    $tpl['title'] .= $this->board->board_desc;

    $this->twigData['title'] = $tpl['title'];
    $this->twigData['htmloptions'] = $tpl['htmloptions'];
    $this->twigData['locale'] = $this->board->board_locale;
    $this->twigData['board'] = $this->board;
    // TODO: Fix ads
    /*$twigData['topads'] = $this->db->select("ads")
    ->fields("ads", array("ad_code"))
    ->condition("ad_position", "top")
    ->condition("ad_display", 1)
    ->execute()
    ->fetchField();*/
    $this->twigData['boardlist'] = $this->board->boardlist;
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

    // TODO: Fix ads
    /*$this->twigData['botads'] = $this->db->select("ads")
  ->fields("ads", array("ad_code"))
  ->condition("ad_position", "bot")
  ->condition("ad_display", 1)
  ->execute()
  ->fetchField();*/
  }

  public function markThread($thread, $i)
  {
    if ($thread->post_delete_time == 0 && $this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
      $this->db->update("posts")
        ->fields([
          'post_delete_time' => time() + 7200,
        ])
        ->condition("post_board", $this->board->board_id)
        ->condition("post_id", $thread->post_id)
        ->execute();
      return true;
    }
    return false;
  }
}
