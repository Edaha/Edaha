<?php
if (!defined('KUSABA_RUNNING'))
{
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}
use \Edaha\Entities\Post;

class public_board_text_text extends public_board_base_baseboard {
  public $boardType = 'text';
  
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

  public function checkFields($post) {
    if ($post->is_reply) {
      if (!isset($post->message) || $post->message == '') {
        kxFunc::showError(_('A message is required for a reply.'));
      }
    }
    else {
      if (!isset($post->subject) || $post->subject == '') {
        kxFunc::showError(_('A subject is required for a new thread.'));
      }
      $thread = $this->entityManager
        ->getRepository(Post::class)
        ->findOneBy(array('board' => $this->board->id, 'subject' => $post->subject, 'parent' => null));
      if (isset($thread)) {
        kxFunc::showError(_('Duplicate thread subject'), _('Text boards may have only one thread with a unique subject. Please pick another.'));
      }
    }
  }

  public function coloredQuote(&$message) {
    parent::coloredQuote($message);
    // Remove the > from the quoted line if it is a text board 
    $message = str_replace('<span class="quote">&gt;', '<span class="quote">', $message);
  }
  
  public function clickableQuote(&$buffer) {

    // Add html for links to posts in the board the post was made
    $buffer = preg_replace_callback('/&gt;&gt;([r]?[l]?[f]?[q]?[0-9,\-,\,]+)/', array(&$this, 'interthreadQuoteCheck'), $buffer);
    
    // Add html for links to posts made in a different board
    $buffer = preg_replace_callback('/&gt;&gt;\/([a-z]+)\/([0-9]+)/', array($this->environment->get('kx:classes:board:parse:id'), 'interboardQuoteCheck'), $buffer);
  }

  public function interthreadQuoteCheck($matches) {

    $lastchar = '';
    // If the quote ends with a , or -, cut it off.
    if(substr($matches[0], -1) == "," || substr($matches[0], -1) == "-") {
      $lastchar = substr($matches[0], -1);
      $matches[1] = substr($matches[1], 0, -1);
      $matches[0] = substr($matches[0], 0, -1);
    }
    return $this->environment->get('kx:classes:board:parse:id')->doDynamicPostLink($matches);
  }

  public function doUpload($postData) {
    return array();
  }

  public function postCommit(Post $post) {
    $this->twigData['isindex'] = true;
    parent::postCommit($post);
  }
}