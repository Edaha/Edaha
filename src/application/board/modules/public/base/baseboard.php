<?php
use Edaha\Entities\Post;
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
    $this->postClass->checkEmptyReply($post);

    $post->subject = substr($post->subject, 0, 74); // TODO Why? Do I care?
    $post->message = $this->parseData($post->message);
    
    // TODO Move to postProcess
    // $commands = $this->postClass->checkPostCommands($postData);
    // $this->postClass->setCookies($post);


    {
      //Needs 1.0 equivalent
      // $post = hook_process('posting', $post);

      // Trim any threads which have been pushed past the limit, or exceed the maximum age limit
      //kxExec:TrimToPageLimit($board_class->board);
    }
  }

  public function postCommit(\Edaha\Entities\Post $post)
  {
    $regenerator = New $this->board->renderer($this->board, $this->entityManager);
    $regenerator->regenerateAllPages();

    if ($post->is_thread) {
      $regenerator->regenerateThread($post);
    } else {
      $regenerator->regenerateThread($post->parent);
    }
  }

  public function doUpload($postData)
  {
    return $this->postClass->doUpload($postData, $this->board);
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

  public function markThread($thread, $i)
  {
    if ($this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
      // TODO Move to Post class? 
      // $this->db->update("posts")
      //   ->fields([
      //     'deleted_at_timestamp' => time() + 7200,
      //   ])
      //   ->condition("post_board", $this->board->board_id)
      //   ->condition("post_id", $thread->post_id)
      //   ->execute();
      return true;
    }
    return false;
  }
}
