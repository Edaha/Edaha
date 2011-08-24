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
 * Section for building an upload-type imageboard
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

class public_board_upload_upload extends public_board_base_baseboard {
  
  public function validPost() {
    if (
      ( /* A message is set, or an image was provided */
        isset($this->request['message']) ||
        isset($_FILES['imagefile'])
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
    $this->postClass = $this->environment->get('kx:classes:board:posting:id');
    $postData['thread_info']['tag'] = $this->postClass->getPostTag();

    parent::processPost($postData);
  }
      
  public function checkFields($postData) {
    
    if (!$postData['is_reply']) {
      if (empty($postData['files'][0])) {
        kxFunc::showError(_gettext('A file is required for a new thread.'));
      }
    }
    else {
      if (!$this->postClass->checkEmpty($postData)) {
        kxFunc::showError(_gettext('An image, or message, is required for a reply.'));
      }
    }

  }
  
  public function regeneratePages() {
    $postsperpage = 30;
    parent::regeneratePages();
  }
  public function buildThread($thread) {
    if (!$thread['tag']) $thread['tag'] = '*';
    return $thread;
  }
}