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
    // Remove the > from the quoted line if it is a text board 
    if ($boardtype==1) {
      $buffer = str_replace('<span class="quote">&gt;', '<span class="quote">', $buffer);
    }
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
    if ($this->boardtype != 1 && is_numeric($matches[1])) {
      
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
    } else {
      $return = $matches[0];
      
      $postids = getQuoteIds($matches[1]);
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
    }
    
    return $return;
  }
  
  public function interboardQuoteCheck($matches) {
    global $db;

    $result = $db->prepare("SELECT id, type FROM " . kxEnv::Get('kx:db:prefix') . "boards WHERE name = ?");
    $result->execute(array($matches[1]));
    $result = $result->fetchAll();
    if ($result[0]["type"] != '') {
      $result2 = $db->prepare("SELECT parentid FROM " . kxEnv::Get('kx:db:prefix') . "posts WHERE boardid = ? AND id = ?");
      $result2->execute(array($result[0]['id'], $matches[2]));
      $result2 = $result2->fetchColumn();
      if ($result2 != '') {
        if ($result2 == 0) {
          $realid = $matches[2];
        } else {
          if ($result[0]['type'] != 1) {
            $realid = $result2;
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
  
  public function wordfilter($buffer, $board) {
    global $db;
    
    $results = $db->query("SELECT * FROM " . kxEnv::Get('kx:db:prefix') . "wordfilter");
    $results = $results->fetchAll();
    foreach($results AS $line) {
      $array_boards = explode('|', $line['boards']);
      if (in_array($board, $array_boards)) {
        $replace_word = $line['word'];
        $replace_replacedby = $line['replacedby'];
        
        $buffer = ($line['regex'] == 1) ? preg_replace($replace_word, $replace_replacedby, $buffer) : str_ireplace($replace_word, $replace_replacedby, $buffer);
      }
    }
    
    return $buffer;
  }
  
  public function checkNotEmpty($buffer) {
    $buffer_temp = str_replace("\n", "", $buffer);
    $buffer_temp = str_replace("<br>", "", $buffer_temp);
    $buffer_temp = str_replace("<br/>", "", $buffer_temp);
    $buffer_temp = str_replace("<br />", "", $buffer_temp);

    $buffer_temp = str_replace(" ", "", $buffer_temp);
    
    if ($buffer_temp=="") {
      return "";
    } else {
      return $buffer;
    }
  }
  
  public function cutWord($txt, $where) {
    $txt_split_primary = preg_split('/\n/', $txt);
    $txt_processed = '';
   
    foreach ($txt_split_primary as $txt_split) {
      $txt_split_secondary = preg_split('/ /', $txt_split);
      
      foreach ($txt_split_secondary as $txt_segment) {
        $segment_length = kxMb::strlen($txt_segment);
        while ($segment_length > $where) {
          $txt_processed .= kxMb::strlen($txt_segment, 0, $where) . "\n";
          $txt_segment    = kxMb::strlen($txt_segment, $where);
          $segment_length = kxMb::strlen($txt_segment);
        }
        $txt_processed .= $txt_segment . ' ';
      }
      
      $txt_processed = kxMb::strlen($txt_processed, 0, -1);
      $txt_processed .= "\n";
    }
    
    return $txt_processed;
  }
  
  public function ParsePost(&$message) {
    $message = trim($message);
    $this->cutWord($message, (kxEnv::get('kx:limits:linelength') / 15));
    $message = htmlspecialchars($message, ENT_QUOTES, kxEnv::get('kx:charset'));
    if (kxEnv::Get('kx:posts:makelinks')) {
      $this->makeClickable($message);
    }
    $this->ClickableQuote($message);
    $this->ColoredQuote($message);
    $message = str_replace("\n", '<br />', $message);
    $this->BBCode($message);
    $this->Wordfilter($message, $board);
    $this->CheckNotEmpty($message);

  }
}
?>