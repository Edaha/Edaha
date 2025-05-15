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
        ->getRepository(\Edaha\Entities\Post::class)
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

  public function postCommit(\Edaha\Entities\Post $post) {
    $this->twigData['isindex'] = true;
    parent::postCommit($post);
  }
}