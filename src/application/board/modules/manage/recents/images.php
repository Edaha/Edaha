<?php

class manage_board_recents_images extends kxCmd
{
  /**
   * Arguments eventually being sent to twig
   *
   * @var Array()
   */
  protected $twigData;

  public function exec(kxEnv $environment)
  {
    switch ($this->request['do']) {
      case 'process':
        $this->_process();
        break;
      default:
        break;
    }
    $this->twigData['section'] = $this->request['section'];
    $this->_show();
  }

  private function _show()
  {
    $this->twigData['recent_images'] = $this->db->select("post_files")
      ->fields("post_files", ["file_post", "file_board", "file_name", "file_type", "file_thumb_width", "file_thumb_height"])
      ->fields("boards", ["board_id", "board_name"])
      ->fields("posts", ["post_timestamp", "post_parent"]);
    $this->twigData['recent_images']->innerJoin("boards", "", "file_board = board_id");
    $this->twigData['recent_images']->innerJoin("posts", "", "file_post = post_id");
    $this->twigData['recent_images'] = $this->twigData['recent_images']->condition("post_deleted", 0)
      ->condition("file_reviewed", 0)
      ->orderBy("post_timestamp", "DESC")
      ->range(0, 100)
      ->execute()
      ->fetchAll();
    
    kxTemplate::output('manage/recents', $this->twigData);
  }

  private function _process()
  {
    if (!isset($this->request['files']) or $this->request['action'] == '') {
      return;
    }
    
    $reviewed_count = 0;
    if ($this->request['action'] == 'approve') {
      // TODO Get the images, delete from saved location, then delete from db
      $fields['file_reviewed'] = 1;

      foreach ($this->request['files'] as $file) {
        $file_board = explode('|', $file)[0];
        $file_post = explode('|', $file)[1];
        $file_name = explode('|', $file)[2];
        $process_query = $this->db->update("post_files")
          ->fields($fields)
          ->condition("file_board", $file_board)
          ->condition("file_post", $file_post)
          ->condition("file_name", $file_name)
          ->execute();
        $reviewed_count = $reviewed_count + $process_query;
      }

      $log_message = "Approved %d posts";
    } else if ($this->request['action'] == 'delete') {
      // TODO Get the images, delete from saved location, then delete from db
      $files_query = $this->db->select("post_files")
        ->fields("post_files", ["file_board", "file_post", "file_name", "file_type"])
        ->fields("boards", ["board_name"]);
      $files_query->innerJoin("boards", "", "board_id = file_board");
      $files_query = $files_query->where("file_board = ?")
        ->where("file_post = ?")
        ->where("file_name = ?")
        ->build();

      foreach ($this->request['files'] as $file) {
        $file_board = explode('|', $file)[0];
        $file_post = explode('|', $file)[1];
        $file_name = explode('|', $file)[2];
        
        $files_query->execute([$file_board, $file_post, $file_name]);
        $file_details = $files_query->fetch();
        
        $file_paths['main']    = KX_BOARD . '/' . $file_details->board_name . '/src/' . $file_details->file_name . '.' . $file_details->file_type;
        $file_paths['thumb']   = KX_BOARD . '/' . $file_details->board_name . '/thumb/' . $file_details->file_name . 's.' . $file_details->file_type;
        $file_paths['catalog'] = KX_BOARD . '/' . $file_details->board_name . '/src/' . $file_details->file_name . 'c.' . $file_details->file_type;
        
        foreach ($file_paths as $path) {
          try {
            unlink($path);
          } catch (Exception $e) {
            print('Error: ' . $e->getMessage() . '\n');
          }
        }

        $process_query = $this->db->delete("post_files")
          ->condition("file_board", $file_board)
          ->condition("file_post", $file_post)
          ->condition("file_name", $file_name)
          ->execute();
        
        $reviewed_count = $reviewed_count + $process_query;
          
        $log_message = "Deleted %d posts";
      }
    }

    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf($log_message, $reviewed_count),
      __CLASS__
    );
  }
}
