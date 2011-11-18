<?php
class Parse {
  protected $environment;
  protected $db;
    
  public function makeClickable(&$txt) {
    $txt = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $txt);
    $txt = ' ' . $txt;
    $txt = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $txt);
    $txt = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $txt);
    $txt = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $txt);
    $txt = substr($txt, 1);
  }
  
  public function bbcode(&$string) {
    $patterns = array(
      '`\[b\](.+?)\[/b\]`is', 
      '`\[i\](.+?)\[/i\]`is', 
      '`\[u\](.+?)\[/u\]`is', 
      '`\[s\](.+?)\[/s\]`is', 
      '`\[aa\](.+?)\[/aa\]`is', 
      '`\[spoiler\](.+?)\[/spoiler\]`is', 
      );
    $replaces =  array(
      '<strong>\\1</strong>', 
      '<em>\\1</em>', 
      '<span style="text-decoration: underline;">\\1</span>', 
      '<del>\\1</del>', 
      '<div style="font-family: "MS PGothic", Osaka, "MS Gothic", Gothic, sans-serif !important;">\\1</div>', 
      '<span class="spoiler">\\1</span>', 
      );
    $string = preg_replace($patterns, $replaces , $string);
    $string = preg_replace_callback('`\[code\](.+?)\[/code\]`is', array(&$this, 'codeCallback'), $string);
  }
  
  public function codeCallback($matches) {
    $return = '<div style="white-space: pre !important;font-family: monospace !important;">'
    . str_replace('<br />', '', $matches[1]) .
    '</div>';
    
    return $return;
  }
  
  public function coloredQuote(&$buffer) {
    // Add a \n to keep regular expressions happy 
    if (substr($buffer, -1, 1)!="\n") {
      $buffer .= "\n";
    }
  
    $buffer = preg_replace('/^(&gt;[^>](.*))\n/m', '<span class="quote">\\1</span>', $buffer);
  }
  
  public function clickableQuote(&$buffer) {
  
    // Add html for links to posts in the board the post was made
    $buffer = preg_replace_callback('/&gt;&gt;([r]?[l]?[f]?[q]?[0-9,\-,\,]+)/', array(&$this, 'interthreadQuoteCheck'), $buffer);
    
    // Add html for links to posts made in a different board
    $buffer = preg_replace_callback('/&gt;&gt;\/([a-z]+)\/([0-9]+)/', array(&$this, 'interboardQuoteCheck'), $buffer);
  }
  
  public function interthreadQuoteCheck($matches) {

    $lastchar = '';
    // If the quote ends with a , or -, cut it off.
    if(substr($matches[0], -1) == "," || substr($matches[0], -1) == "-") {
      $lastchar = substr($matches[0], -1);
      $matches[1] = substr($matches[1], 0, -1);
      $matches[0] = substr($matches[0], 0, -1);
    }
    if (is_numeric($matches[1])) {
      return $this->doStaticPostLink($matches);
    } else {
      return $this->doDynamicPostLink($matches);
    }
  }
  
  public function doStaticPostLink($matches) {
    $result = $this->db->select("posts")
                       ->fields("posts", array("post_parent"))
                       ->condition("post_board", $this->board->board_id)
                       ->condition("post_id", $matches[1])
                       ->execute()
                       ->fetchField();

    if ($result === 0) {
      $realID = $matches[1];
    }
    elseif(empty($result)) {
      return $matches[0];
    }
    else {
      $realID = $result;
    }
    
    return '<a href="'.kxEnv::get('kx:paths:boards:folder').$this->board->board_name.'/res/'.$realID.'.html#'.$matches[1].'" id="ref">'.$matches[0].'</a>'.$lastchar;
  }
  
  public function doDynamicPostLink($matches) {
    $return = $matches[0];
    
    $postids = kxFunc::getQuoteIds($matches[1]);
    if (count($postids) > 0) {
      $realid = $this->parentid;
      if ($realid === 0) {
        if ($this->id > 0) {
          $realid = $this->id;
        }
      }
      if ($realid !== '') {
        $return = '<a href="' . kxEnv::Get('kx:paths:boards:folder') . 'read.php';
        if (kxEnv::Get('kx:display:traditionalread')) {
          $return .= '/' . $thread_board_return . '/' . $realid.'/' . $matches[1];
        } else {
          $return .= '?b=' . $thread_board_return . '&t=' . $realid.'&p=' . $matches[1];
        }
        $return .= '">' . $matches[0] . '</a>';
      }
    }
    
    return $return;
  }
  public function interboardQuoteCheck($matches) {
    $board = $this->db->select("boards")
                      ->fields("boards", array("board_id", "board_type"))
                      ->condition("board_name", $matches[1])
                      ->execute()
                      ->fetch();
    if ($board->board_type) {
      $thread = $this->db->select("posts")
                      ->fields("posts", array("post_parent"))
                      ->condition("post_board", $board->board_id)
                      ->condition("post_id", $matches[2])
                      ->execute()
                      ->fetchField();
      if ($thread !== FALSE) {
        if ($thread == 0) {
          $realid = $matches[2];
        } else {
          if ($board->board_type != 1) {
            $realid = $thread;
          }
        }
        
        if ($result[0]["type"] != 1) {
          return '<a href="'.kxEnv::Get('kx:paths:boards:folder').$matches[1].'/res/'.$realid.'.html#'.$matches[2].'" class="ref|' . $matches[1] . '|' . $realid . '|' . $matches[2] . '">'.$matches[0].'</a>';
        } else {
          return '<a href="'.kxEnv::Get('kx:paths:boards:folder').$matches[1].'/res/'.$realid.'.html" class="ref|' . $matches[1] . '|' . $realid . '|' . $realid . '">'.$matches[0].'</a>';
        }
      }
    }
    
    return $matches[0];
  }
  
  public function wordFilter(&$buffer) {
    $filters = kxEnv::Get("cache:filters:wordfilters");

    foreach ($filters as $filter) {
      if ( (!$filter->filter_boards || in_array($this->environment->get("kx:classes:board:id"), unserialize($filter->filter_boards))) && (!$filter->filter_regex && kxMb::stripos($buffer, $filter->filter_word) !== false) || ($filter->filter_regex && preg_match($filter->filter_word, $buffer))) {
        $buffer = ($filter->filter_regex == 1) ? preg_replace($filter->filter_word, $filter->filter_replace, $buffer) : str_ireplace($filter->filter_word, $filter->filter_replace, $buffer);
      }
    }
  }
  
  public function checkNotEmpty(&$buffer) {
    $buffer_temp = str_replace("\n", "", $buffer);
    $buffer_temp = str_replace("<br>", "", $buffer_temp);
    $buffer_temp = str_replace("<br/>", "", $buffer_temp);
    $buffer_temp = str_replace("<br />", "", $buffer_temp);

    $buffer_temp = str_replace(" ", "", $buffer_temp);
    
    if ($buffer_temp=="") {
      $buffer = "";
    }
  }
  
  public function cutWord(&$message, $where) {
    $txt_split_primary = preg_split('/\n/', $message);
    $txt_processed = '';
   
    foreach ($txt_split_primary as $txt_split) {
      $txt_split_secondary = preg_split('/ /', $txt_split);
      
      foreach ($txt_split_secondary as $txt_segment) {
        $segment_length = kxMb::strlen($txt_segment);
        while ($segment_length > $where) {
          $txt_processed .= kxMb::substr($txt_segment, 0, $where) . "\n";
          $txt_segment    = kxMb::substr($txt_segment, $where);
          $segment_length = kxMb::strlen($txt_segment);
        }
        $txt_processed .= $txt_segment . ' ';
      }
      
      $txt_processed = kxMb::substr($txt_processed, 0, -1);
      $txt_processed .= "\n";
    }
    $message = $txt_processed;
  }
}
?>