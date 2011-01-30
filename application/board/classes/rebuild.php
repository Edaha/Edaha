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
		$dateEmail = (empty($this->board->board_default_name)) ? $post->post_email : 0;
		$post->post_message = stripslashes($this->formatLongMessage($post->post_message, $this->board->board_name, (($post->post_parent == 0) ? ($post->post_id) : ($post->post_parent)), $page));
		$post->timestamp_formatted = kxFunc::formatDate($post->post_timestamp, 'post', $this->environment->get('kx:language:currentlocale'), $dateEmail);
		$post->reflink = $this->formatReflink($this->board->board_name, (($post->post_parent == 0) ? ($post->post_id) : ($post->post_parent)), $post->post_parent, $this->environment->get('kx:language:currentlocale'));
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