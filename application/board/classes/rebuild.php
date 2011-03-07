<?php

class Rebuild {
  protected $environment;
  protected $db;

  public function __construct( kxEnv $environment )
	{
		$this->environment = $environment;
		$this->db = kxDB::getInstance();
    $this->request = kxEnv::$request;
	}

  /**
   * Build the page header
   *
   * @param integer $replythread The ID of the thread the header is being build for.  0 if it is for a board page
   * @param integer $liststart The number which the thread list starts on (text boards only)
   * @param integer $liststooutput The number of list pages which will be generated (text boards only)
   * @return string The built header
   */
  public function pageHeader() {

    $tpl = Array();

    $tpl['htmloptions'] = ((kxEnv::Get('kx:misc:locale') == 'he' && empty($this->board->board_locale)) || $this->board->board_locale == 'he') ? ' dir="rtl"' : '' ;

    $tpl['title'] = '';

    if (kxEnv::Get('kx:pages:dirtitle')) {
      $tpl['title'] .= '/' .  $this->board->board_name . '/ - ';
    }
    $tpl['title'] .= $this->board->board_desc;

    $dwoo_data['title'] = $tpl['title'];
    $dwoo_data['htmloptions'] = $tpl['htmloptions'];
    $dwoo_data['locale'] = $this->board->board_locale;
    $dwoo_data['board'] = $this->board;
    $dwoo_data['topads'] = $this->db->select("ads")
                                          ->fields("ads", array("ad_code"))
                                          ->condition("ad_position", "top")
                                          ->condition("ad_display", 1)
                                          ->execute()
                                          ->fetchField();
    $dwoo_data['boardlist'] = $this->board->boardlist;

    return $dwoo_data;

  }
  public function blotter() {
    if (kxEnv::Get('kx:extras:blotter')) {
        $dwoo_data['blotter'] = kxFunc::getBlotter();
        $dwoo_data['blotter_updated'] = kxFunc::getBlotterLastUpdated();
        return $dwoo_data;
    }
    return array();
  }
  /**
   * Display the page footer
   *
   * @param boolean $noboardlist Force the board list to not be displayed
   * @param string $executiontime The time it took the page to be created
   * @param boolean $hide_extra Hide extra footer information, and display the manage link
   * @return string The generated footer
   */
  public function footer($noboardlist = false, $executiontime = 0, $hide_extra = false) {

    if ($noboardlist || $hide_extra) $this->dwoo_data['boardlist'] = "";
    if ($executiontime) $this->dwoo_data['executiontime'] = round($executiontime, 2);
    
    $dwoo_data['botads'] = $this->db->select("ads")
                                          ->fields("ads", array("ad_code"))
                                          ->condition("ad_position", "bot")
                                          ->condition("ad_display", 1)
                                          ->execute()
                                          ->fetchField();

    return $dwoo_data;
  }
  public function getEmbeds() {
    $this->board->embeds = array();
    $results = $this->db->select("embeds")
                        ->fields("embeds", array("embed_ext"))
                        ->execute()
                        ->fetchAll();
    foreach ($results as $line) {
      $this->board->embeds[] = $line->embed_ext;
    }
    return $this->board->embeds;
  }
  public function calcTotalPages() {
    $numposts = $this->db->select("posts")
                         ->fields("posts")
                         ->condition("post_board", $this->board->board_id)
                         ->condition("post_parent", 0)
                         ->condition("post_deleted", 0)
                         ->countQuery()
                         ->execute()
                         ->fetchField();
    return kxFunc::pageCount($this->board->board_type, ($numposts-1));
  }
  public function markThread($thread) {
    if ($thread->post_delete_time == 0 && $this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
      $this->db->update("posts")
               ->fields(array(
                 'post_delete_time' => time() + 7200,
               ))
               ->condition("post_board", $this->board->board_id)
               ->condition("post_id", $thread->post_id)
               ->execute();
      return true;
    }
    return false;
  }
  
  public function buildPageThread($thread) {
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
      if (empty($this->board->buildPageResults) || !($this->board->buildPageResults instanceof kxDBStatementInterface)) { 
        $this->board->buildPageResults = $this->db->select("posts")
                ->fields("posts")
                ->where("post_board = ? AND post_parent = ? AND post_deleted = 0")
                ->orderBy("post_id", "DESC")
                ->range(0, kxEnv::Get('kx:display:replies'))
                ->build();
      }
      $this->board->buildPageResults->execute(array($this->board->board_id, $thread->post_id));
      $posts = $this->board->buildPageResults->fetchAll();
    }
    foreach ($posts as &$post) {
      $omitids[] = $post->post_id;
      $post = $this->buildPost($post, true);
    }
    return array($posts, $omitids);
  }
  public function buildThread($id) {
    $dwoo_data = array();
    //---------------------------------------------------------------------------------------------------
    // Okay, this may seem confusing, but we're caching this so we can use it as a prepared statement
    // intead of executing it every time. This is only really useful if we're regenerating all threads,
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
    $this->board->preparedThreads->execute(Array($id, $id));
    $thread = $this->board->preparedThreads->fetchAll();
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
      $dwoo_data['lastid'] = $post->post_id;
    }
    // Now we can get rid of it
    unset($post);
    return array($thread, $dwoo_data);
  }
  public function getOmittedPosts(&$thread, $omitids = array(), $images = true) {
    if ($images) {
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
        $thread->images   = $replycount[0]->files;
    }
    else {
      $replycount = $this->db->select("posts", "p");
      $replycount->addExpression("COUNT(post_id)", "replies");
      $replycount->condition("post_board", $this->board->board_id)
                 ->condition("post_parent", $thread->post_id);
      if ($this->board->board_type != 1) {
        $replycount->condition("post_deleted", 0);
      }
      $replycount = $replycount->execute()
                               ->fetchAll();
    }
    $thread->replies  = $replycount[0]->replies;
  }
	public function buildPost($post, $page) {
    $post_files = $this->db->select("post_files")
                           ->fields("post_files")
                           ->condition("file_board", $this->board->board_id)
                           ->condition("file_post", $post->post_id)
                           ->execute()
                           ->fetchAll();
    foreach($post_files as $line) {
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
		if (!empty($post->file_type)){
			foreach ($post->file_type as $key=>$type) {
				if (isset($this->board->embeds) && in_array($type, $this->board->embeds)) {
					$post->videobox = $this->embeddedVideoBox($post);
				}

				if ($type == 'mp3' /*&& $this->board['loadbalanceurl'] == ''*/) {
					//Grab the ID3 info. TODO: Make this work for load-balanced boards.
					// include getID3() library
					require_once(KX_ROOT . '/lib/getid3/getid3.php');

					// Initialize getID3 engine
					$getID3 = new getID3;

					$post->id3[$key] = $getID3->analyze(KX_BOARD . '/'.$this->board->board_name.'/src/'.$post->file_name[$key].'.mp3');
					getid3_lib::CopyTagsToComments($post->id3[$key]);
				}
				if ($type!='jpg'&&$type!='gif'&&$type!='png'&&$type!=''&&!in_array($type, $this->board->embeds)) {
					if(!isset($filetype_info[$type])) $filetype_info[$type] = kxFunc::getFileTypeInfo($type);
					$post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/public/filetypes/' . $filetype_info[$type][0];
					if($post->post_thumb_width != 0 && $post->post_thumb_height != 0 ) {
						if(file_exists(KX_BOARD . '/'.$this->board->board_name.'/thumb/'.$post->file_name[$key].'s.jpg'))
							$post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' .$this->board->board_name.'/thumb/'.$post->file_name[$key].'s.jpg';
						elseif(file_exists(KX_BOARD . '/'.$this->board['name'].'/thumb/'.$post['file'].'s.png'))
							$post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' .$this->board->board_name.'/thumb/'.$post->file_name[$key].'s.png';
						elseif(file_exists(KX_BOARD . '/'.$this->board['name'].'/thumb/'.$post['file'].'s.gif'))
							$post->nonstandard_file[$key] = kxEnv::Get('kx:paths:main:path') . '/' .$this->board->board_name.'/thumb/'.$post->file_name[$key].'s.gif';
						else {
							$post->post_thumb_width[$key] = $filetype_info[$type][1];
							$post->post_thumb_height[$key] = $filetype_info[$type][2];
						}
					}
					else {
						$post->post_thumb_width[$key] = $filetype_info[$type][1];
						$post->post_thumb_height[$key] = $filetype_info[$type][2];
					}
				}
			}
		}
		return $post;
	}

  public function formatPost($post, $page) {
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
  public function formatLongMessage($message, $board, $threadid, $page) {
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
      $message_shortened = closeOpenTags($message_shortened);

      if (strrpos($message_shortened,"<") > strrpos($message_shortened,">")) {
        //We have a partially opened tag we need to get rid of.
        $message_shortened = substr($message_shortened, 0, strrpos($message_shortened,"<"));
      }
      
      $output = $message_shortened . '<div class="abbrev">' . "\n" .
      '	' . sprintf(_gettext('Message too long. Click %shere%s to view the full text.'), '<a href="' . kxEnv::Get('kx:paths:boards:folder') . $board . '/res/' . $threadid . '.html">', '</a>') . "\n" .
      '</div>' . "\n";
    } else {
      $output .= $message . "\n";
    }

    return $output;
  }

  /**
   * Format the provided input into a reflink, which follows the Japanese locale if it is set.
   */
  public function formatReflink($post_board, $post_thread_start_id, $post_id, $locale = 'en') {
    $return = '	';

    $reflink_noquote = '<a href="' . kxEnv::Get('kx:paths:boards:folder') . $post_board . '/res/' . $post_thread_start_id . '.html#' . $post_id . '" onclick="return highlight(\'' . $post_id . '\');">';

    $reflink_quote = '<a href="' . kxEnv::Get('kx:paths:boards:folder') . $post_board . '/res/' . $post_thread_start_id . '.html#i' . $post_id . '" onclick="return insert(\'>>' . $post_id . '\\n\');">';

    if ($locale == 'ja') {
      $return .= $reflink_quote . kxFunc::formatJapaneseNumbers($post_id) . '</a>' . $reflink_noquote . '?</a>';
    } else {
      $return .= $reflink_noquote . 'No.&nbsp;' . '</a>' . $reflink_quote . $post_id . '</a>';
    }

    return $return . "\n";
  }
}
?>