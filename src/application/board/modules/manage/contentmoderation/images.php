<?php

class manage_board_contentmoderation_images extends kxCmd
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
      ->fields("posts", ["created_at_timestamp", "parent_post_id"]);
    $this->twigData['recent_images']->innerJoin("boards", "", "file_board = board_id");
    $this->twigData['recent_images']->innerJoin("posts", "", "file_post = post_id");
    $this->twigData['recent_images'] = $this->twigData['recent_images']->condition("is_deleted", 0)
      ->condition("file_reviewed", 0)
      ->orderBy("created_at_timestamp", "DESC")
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

      $log_message = "Approved %d files";
    } else if ($this->request['action'] == 'delete') {
      foreach ($this->request['files'] as $file) {
        $file_board = explode('|', $file)[0];
        $file_post = explode('|', $file)[1];
        $file_name = explode('|', $file)[2];
        $deleted_image = kxFunc::deleteFile($file_board, $file_name); 
        
        $reviewed_count = $reviewed_count + $deleted_image;
          
        $log_message = "Deleted %d files";
      }
    }

    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf($log_message, $reviewed_count),
      __CLASS__
    );
  }
}
