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

class public_board_image_image extends kxCmd {
  /**
   * Board data
   *
   * @access	public
   * @var		object	stdClass
   */
  protected $board;
  
  protected $thread = 0
  
  protected $method = ""
  
  /**
   * Run requested method
   *
   * @access	public
   * @param	object		Registry object
   * @param	object		Board object
   * @param	int			Thread ID
   * @param	string		Method to run
   * @return	void
   */
  public function exec( kxEnv $environment ) 
  {
    //-----------------------------------------
    // Setup...
    //-----------------------------------------
    
    $this->board = $board;

    require_once( kxFunc::getAppDir('board') .'classes/rebuild.php' );
    $this->environment->set('kx:classes:board:rebuild:id', new rebuild( $this->environment ) );

    //-----------------------------------------
    // Select method to run
    //-----------------------------------------
    
    switch( $method )
    {
      
      case 'regeneratePages':
        $this->_regeneratePages();
      break;
      
      case 'regenerateThreads':
        $this->_regenerateThreads();
      break;

      case 'regenerateThread':
        if( $thread ) {
          $this->_regenerateThread($thread);
        }
        else {
          $this->_regenerateThreads();
        }
      break;
      
      case 'buildHeader':
        $this->_buildHeader();
      break;
      
      default:
        $this->_regenerateAll();
      break;
    }
  }
  public function _regeneratePages() {
    //-----------------------------------------
    // Setup
    //-----------------------------------------

    $postsperpage =	$this->environment->get('kx:display:imgthreads');
    $i = 0;
    $maxpages = $this->board->maxpages;
    $numposts = $this->DB->select("posts")
                         ->countQuery()
                         ->condition("post_board", $this->board->id)
                         ->condition("post_parent", 0)
                         ->condition("post_deleted", 0)
                         ->execute()
                         ->fetchField();
    $totalpages = kxFunc::pageCount($this->board_type, ($numposts-1));
    // Saznote: move to rebuild.php
    $results = $this->DB->select("embeds")
                        ->fields("embeds", array('embed_ext'))
                        ->execute()
                        ->fetchAll();
    foreach ($results as $line) {
      $this->board->filetypes[] = $line->embed_ext;
    }

    //-----------------------------------------
    // Run through each page
    //-----------------------------------------    

    while ($i <= $totalpages) {
      $this->dwoo_data->assign('thispage', $i);
      
      //--------------------------------------------------------------------------------------------------
      // Grab our threads, stickies go first, then follow by bump time, then run through them
      //--------------------------------------------------------------------------------------------------
      $threads = $this->DB->select("posts")
                          ->condition("post_board", $this->board->board_id)
                          ->condition("post_parent", 0)
                          ->condition("post_deleted", 0)
                          ->orderBy("post_stickied", "DESC")
                          ->orderBy("post_bumped", "DESC")
                          ->range($postsperpage * $i, $postsperpage)
                          ->execute()
                          ->fetchAll();
      foreach ($threads as &$thread) {

        //------------------------------------------------------------------------------------------
        // If the thread is on the page set to mark, and hasn't been marked yet, mark it
        //------------------------------------------------------------------------------------------
        if ($thread->post_delete_time == 0 && $this->board->board_mark_page > 0 && $i >= $this->board->board_mark_page) {
          // Saznote: move to rebuild.php
          $this->DB->update("posts")
                   ->fields(array(
                     'post_delete_time' => time() + 7200,
                   ))
                   ->condition("post_board", $this->board->board_id)
                   ->condition("post_id", $thread->post_id)
                   ->execute();
          $this->_RegenerateThreads($thread->post_id);
          // RegenerateThreads overwrites the replythread variable. Reset it here.
          $this->dwoo_data->assign('replythread', 0);
        }
        $this->environment->get('kx:classes:board:rebuild:id')->buildPost($thread, true);
        $omitids = array();
        
        //-----------------------------------------------------------------------------------------------------------------------------------
        // Process stickies without using prepared statements (because they have a different range than regular threads).
        // Since there's usually very few to no stickies we can get away with this with minimal performance impact.
        //-----------------------------------------------------------------------------------------------------------------------------------
        if ($thread['stickied'] == 1) {
          $posts = $this->db->select("posts")
                            ->condition("post_board", $this->board->board_id)
                            ->condition("post_parent", $thread->post_id)
                            ->condition("post_deleted", 0)
                            ->orderBy("id", "DESC")
                            ->range(0, kxEnv::Get('kx:display:stickyreplies'))
                            ->execute()
                            ->fetchAll();
        }
        
        //---------------------------------------------------------------------------------------------------
        // For non-stickies, we check if the $results object exists, or if it isn't an instance of
        // kxDBStatementInterface (like if it was overwritten somwehere else). If is isn't, the
        // query is prepared, otherwise, we used the prepared statement already given to us
        //----------------------------------------------------------------------------------------------------
        else {
          if (empty($results) || !($results instanceof kxDBStatementInterface)) { 
            $results = $this->db->select("posts")
                    ->where("post_board = ? AND post_parent = ? AND post_deleted = 0")
                    ->orderBy("id", "DESC")
                    ->range(0, kxEnv::Get('kx:display:replies'))
                    ->build();
          }
          $results->execute(array($this->board->board_id, $thread->post_id));
          $posts = $results->fetchAll();
        }
        foreach ($posts as &$post) {
          $omitids[] = $post['id'];
          $post = $this->environment->get('kx:classes:board:rebuild:id')->buildPost($post, true);
        }

        //---------------------------------------------------------------------------------------------------
        // Get the number of replies and image-replies for this thread, minus the ones that are
        // already being shown.
        //----------------------------------------------------------------------------------------------------
        $replycount = $this->DB->select("posts", "p");
        $replycount->addExpression('COUNT(DISTINCT(file_post_id))', 'replies');
        $replycount->addExpression('SUM(CASE WHEN file_md5 = \'\' THEN 0 ELSE 1 END)', 'files');
        $replycount->innerJoin("post_files", "f", "post_id = file_post_id AND file_board = post_board");
        $replycount->condition("post_board", $this->board->board_id)
                   ->condition("post_parent", $thread->post_id)
                   ->condition("post_deleted", 0);
        if (!empty($omitids)) {
          $replycount->condition("file_post_id", $omitids, "NOT IN");
        }
        $replycount = $replycount->execute()
                                 ->fetchAll();
        $thread['replies']  = $replycount[0]->replies;
        $newposts['images'] = $replycount[0]->files;

        $posts = array_reverse($posts);
        array_unshift($posts, $thread);
      }
      if (!isset($embeds)) {
        $embeds = $this->DB->select("embeds")
                          ->execute()
                          ->fetchAll();
        $this->dwoo_data->assign('embeds', $embeds);
      }
      if (!isset($header)){
        $header = $this->_pageHeader();
        $header = str_replace("<!sm_threadid>", 0, $header);
      }
      if (!isset($postbox)) {
        $postbox = $this->_postBox();
        $postbox = str_replace("<!sm_threadid>", 0, $postbox);
      }
      $this->dwoo_data->assign('posts', $posts);
      $this->dwoo_data->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));
      $content = $this->dwoo->get(KX_ROOT . kxEnv::Get('kx:templates:dir') . '/' . $this->board['text_readable'] . '_board_page.tpl', $this->dwoo_data);
      $footer = $this->Footer(false, (microtime_float() - $executiontime_start_page), (($this->board['type'] == 1) ? (true) : (false)));
      $content = $header.$postbox.$content.$footer;

      $content = str_replace("\t", '',$content);
      $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

      if ($i == 0) {
        $page = KX_BOARD . '/'.$this->board['name'].'/'.kxEnv::Get('kx:pages:first');
        $this->PrintPage($page, $content, $this->board['name']);
      } else {
        $page = KX_BOARD . '/'.$this->board['name'].'/'.$i.'.html';
        $this->PrintPage($page, $content, $this->board['name']);
      }
      $i++;
    }
  }
}