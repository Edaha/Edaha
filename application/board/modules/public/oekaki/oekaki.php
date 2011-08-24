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
 * Section for building an oekaki-type imageboard
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

class public_board_oekaki_oekaki extends public_board_base_baseboard {
  
  public function validPost() {
    if (
      ( /* A message is set, or an image was provided */
        isset($this->request['message']) ||
        isset($_FILES['imagefile'])
      ) || /* It is a validated oekaki posting */
      $this->environment->get('kx:classes:board:posting:id')->checkOekaki()
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
    $postData['is_oekaki'] = $this->postClass->checkOekaki();
    if ($postData['is_oekaki']) {
      if (file_exists(KX_BOARD . '/' . $this->board->board_name . '/src/' . $files[0]['file_name'] . '.pch')) {
        $postData['thread_info']['message'] .= '<br /><small><a href="' . KX_SCRIPT . '/animation.php?board=' . $this->board->board_name . '&amp;id=' . $files[0]['file_name'] . '">' . _gettext('View animation') . '</a></small>';
      }
    }
    parent::processPost($postData);
  }
      
  public function checkFields($postData) {
    if (!$postData['is_reply']) {
      if (empty($postData['files'][0]) && !$postData['is_oekaki'] && ( ( !isset($this->request['nofile']) && $this->board->board_enable_no_file == 1 ) || $this->board->board_enable_no_file ) ) {
        kxFunc::showError(_gettext('A file is required for a new thread.'));
      }
    }
    else {
      if (!$postData['is_oekaki'] && !$this->postClass->checkEmpty($postData)) {
        kxFunc::showError(_gettext('An image, or message, is required for a reply.'));
      }
    }
    if (isset($this->request['nofile']) && $this->board->board_enable_no_file == 1) {
      if (!$this->postClass->checkNoFile) {
        kxFunc::showError('A message is required to post without a file.');
      }
    }
  }

  /**
   * Build the page header for an oekaki posting
   */  
  private function _oekakiHeader() {
    echo "stub";
    exit();
  }

  /**
   * Generate the postbox area
   *
   * @param integer $replythread The ID of the thread being replied to.  0 if not replying
   * @param string $postboxnotice The postbox notice
   * @return string The generated postbox
   */
  public function postBox($replythread = 0) {

    parent::postBox($replythread);
    $oekposts = $this->db->select("posts")
                         ->fields("posts", array("post_id"))
                         ->innerJoin("post_files", "", "file_post = post_id AND file_board = post_board");
    $oekposts = $oekposts->condition("post_board", $this->board->board_id)
                         ->condition($this->db->condition("OR")
                                              ->condition("post_id", $replythread)
                                              ->condition("post_parent", $replythread))
                         ->condition("file_name", "", "!=")
                         ->condition("file_type", array("jpg", "gif", "png"), "IN")
                         ->condition("post_deleted", 0)
                         ->orderBy("post_parent")
                         ->orderBy("post_timestamp")
                         ->execute()
                         ->fetchAll();
    $this->dwoo_data['oekposts'] = $oekposts;
    
  }
}