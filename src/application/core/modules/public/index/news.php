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

  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;

  public function exec( kxEnv $environment ) {
    if(isset($this->request['view'])){
      switch($this->request['view']){
        case 'faq':
            $type = 1;
            break;
        case 'rules':
            $type = 2;
            break;
      }
    } else {
      $this->request['view'] = '';
      $type = 0;
    }
    $this->twigData['styles'] = explode(':', kxEnv::Get('kx:css:sitestyles'));
    $entries = $this->db->select("front")
                    ->fields("front")
                    ->condition("entry_type", $type);
                    
    if ($this->request['view'] != '') {
      $entries->orderBy("entry_order", "ASC");
    } else {
      $entries->orderBy("entry_time", "DESC");
      if (!isset($this->request['page'])) {
        $entries->range(0,2);
      } else {
        $entries->range((int)$this->request['page'] * 2, 2);
      }
    }
    $this->twigData['entries'] = $entries->execute()
                                         ->fetchAll();
    
    // Figure out the number of pages total, if news
    if ($this->request['view'] == '') {
      $pages = $this->db->select("front")
                        ->condition("entry_type", $type)
                        ->countQuery()
                        ->execute()
                        ->fetchField();
      $this->twigData['pages'] = (int) $pages/2; //round($pages/2, 0, PHP_ROUND_HALF_UP);
    }
    
    $sections = $this->db->select("sections")
                         ->fields("sections")
                         ->orderBy("section_order")
                         ->execute()
                         ->fetchAll();

    $boards = $this->db->select("boards")
                       ->fields("boards", array('board_name', 'board_desc'))
                       ->where("board_section = ?")
                       ->orderBy("board_order")
                       ->build();
    // Add boards to an array within their section
    foreach ($sections as $section) {
      $boards->execute(array($section->id));
      $section->boards = $boards->fetchAll();
    }
    
    $this->twigData['sections'] = $sections;

    // Get recent posts
    $recentposts = $this->db->select("posts");
    $recentposts->innerJoin("boards", "", "posts.board_id = boards.board_id");
    $recentposts = $recentposts->fields("posts")
                               ->fields("boards", array("board_name"))
                               ->orderBy("created_at_timestamp", "DESC")
                               ->range(0,6)
                               ->execute()
                               ->fetchAll();
    $this->twigData['recentposts'] = $recentposts;
    
    
    // Get recent images
    $images = $this->db->select("post_files");
    $images->innerJoin("posts", "", "post_id = file_post AND posts.board_id = file_board");
    $images->innerJoin("boards", "", "posts.board_id = boards.board_id");
    $images = $images->fields("post_files", array("file_name", "file_type", "file_board", "file_thumb_width", "file_thumb_height"))
                     ->fields("posts", array("post_id", "parent_post_id"))
                     ->fields("boards", array("board_name"))
                     ->condition("file_name", "", "!=")
                     ->orderBy("created_at_timestamp", "DESC")
                     ->range(0,3)
                     ->execute()
                     ->fetchAll();
    $this->twigData['recentimages'] = $images;

    kxTemplate::output("index", $this->twigData);
  }
}