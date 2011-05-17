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
 * Section for building the news page
 * Last Updated: $Date$
 
 * @author 		$Author$

 * @package		kusaba
 * @subpackage	core

 * @version		$Revision$
 *
 */
 
if (!defined('KUSABA_RUNNING'))
{
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}

class public_core_index_news extends kxCmd {

  public function exec( kxEnv $environment ) {
      // i might have mixed up faq with rules, if so, switch the types around..
  if(isset($_GET['p'])){
      switch($_GET['p']){
        case 'faq':
            $type = 1;
            break;
        case 'rules':
            $type = 2;
            break;
      }
  }else{
      $type = 0;
  }
    $dwoo_data['styles'] = explode(':', kxEnv::Get('kx:css:menustyles'));
    $dwoo_data['entries'] = $this->db->select("front")
                                     ->fields("front")
                                     ->condition("entry_type", $type)
                                     ->orderBy("entry_time", "DESC")
                                     ->execute()
                                     ->fetchAll();
    $dwoo_data['sections'] = $this->db->select("sections")
                                      ->fields("sections")
                                      ->orderBy("section_order")
                                      ->execute()
                                      ->fetchAll();
    $dwoo_data['boards'] = $this->db->select("boards")
                                    ->fields("boards")
                                    ->orderBy("board_section")
                                    ->orderBy("board_order")
                                    ->execute()
                                    ->fetchAll();

    // Get recent images
    $images = $this->db->select("post_files");
    $images->innerJoin("posts", "", "post_id = file_post AND post_board = file_board");
    $images = $images->fields("post_files", array("file_name", "file_type", "file_board", "file_thumb_width", "file_thumb_height"))
                     ->fields("posts", array("post_id", "post_parent"))
                     ->condition("file_name", "", "!=")
                     ->orderBy("post_timestamp", "DESC")
                     ->range(0,3)
                     ->execute()
                     ->fetchAll();
    $i = 0;
    if (count($images) > 0) {
      $results =  $this->db->select('boards')
                    ->fields('boards', array('board_name'))
                    ->where('board_id = ?')
                    ->range(0,1)
                    ->build();
      while ($i < count($images)) {
       $results->execute(array($images[$i]->board_id));
       $board= $results->fetchAll();
       $images[$i]->boardname = $board[0]->board_name;
       $i++;
      }
    }
    $dwoo_data['images'] = $images;

    kxTemplate::output("index", $dwoo_data);
  }
}