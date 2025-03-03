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
 * Section for building an image-type imageboard
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

class public_board_image_image extends public_board_base_baseboard {
  
  public $boardType = 'image';
  
  public function validPost() {
    if (
      ( /* A message is set, or an image was provided */
        isset($this->request['message']) ||
        isset($_FILES['imagefile'])
      ) || (
        ( /* It has embedding allowed */
            $this->board->board_upload_type == 1 ||
            $this->board->board_upload_type == 2
        ) && ( /* An embed ID was provided, or no file was checked and no ID was supplied */
            isset($this->request['embed']) ||
            (
              $this->board->board_upload_type == 2 &&
              !isset($_FILES['imagefile']) &&
              isset($this->request['nofile']) &&
              $this->board->enable_no_file == true
            )
        )
      )
    ) {
      return true;
    } else {
      return false;
    }
  }
  
  public function checkFields($postData) {
    if (!$postData['is_reply']) {
      if (($this->board->board_upload_type == 1 || $this->board->board_upload_type == 2) && !empty($this->board->board_embeds_allowed)) {
        if ($this->postClass->checkEmbed($postData)) {
          kxFunc::showError(_gettext('Please enter an embed ID.'));
        }
      }
      if (empty($postData['files'][0]) && ( ( !isset($this->request['nofile']) && $this->board->board_no_file == 1 ) || $this->board->board_no_file == 0 ) ) {
        if (($this->board->board_upload_type != 0 && empty($this->request['embed'])) || $this->board->board_upload_type == 0) {
          kxFunc::showError(_gettext('A file is required for a new thread. If embedding is allowed, either a file or embed ID is required.'));
        }
      }
    }
    else {
      if (!$this->postClass->checkEmpty($postData)) {
        kxFunc::showError(_gettext('An image, or message, is required for a reply.'));
      }
    }
    if (isset($this->request['nofile']) && $this->board->board_enable_no_file == 1) {
      if (!$this->postClass->checkNoFile) {
        kxFunc::showError('A message is required to post without a file.');
      }
    }
  }
}