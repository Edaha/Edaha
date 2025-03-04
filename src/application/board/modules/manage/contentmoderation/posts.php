<?php

class manage_board_contentmoderation_posts extends kxCmd
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
    $this->twigData['recent_posts'] = $this->db->select("posts")
      ->fields("posts", ["post_id", "post_message", "post_parent"])
      ->fields("boards", ["board_id", "board_name"]);
    $this->twigData['recent_posts']->innerJoin("boards", "", "post_board = board_id");
    $this->twigData['recent_posts'] = $this->twigData['recent_posts']->condition("post_deleted", 0)
      ->condition("post_reviewed", 0)
      ->orderBy("post_timestamp", "DESC")
      ->range(0, 100)
      ->execute()
      ->fetchAll();
    kxTemplate::output('manage/recents', $this->twigData);
  }

  private function _process()
  {
    if (!isset($this->request['posts']) or $this->request['action'] == '') {
      return;
    }

    $fields['post_reviewed'] = 1;
    if ($this->request['action'] == 'delete') {
      foreach ($this->request['posts'] as $post) {
        $board_id = (int) explode('|', $post)[0];
        $post_id = (int) explode('|', $post)[1];
        kxFunc::deletePost($board_id, $post_id);
      }
    } else {
      foreach ($this->request['posts'] as $post) {
        $board_id = (int) explode('|', $post)[0];
        $post_id = (int) explode('|', $post)[1];
        $board_posts[$board_id][] = $post_id;
      }

      foreach ($board_posts as $board_id => $posts) {
        $where_clauses[] = '(post_board = ' . $board_id . ' and post_id in (' . implode(',', $posts) . '))';
      }

      $process_query = $this->db->update("posts")
        ->fields($fields)
        ->where(implode(" or ", $where_clauses))
        ->execute();
    }

    if ($this->request['action'] == 'delete') {
      $log_message = "Deleted %d posts";
    } else {
      $log_message = "Approved %d posts";
    }

    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf($log_message, count($this->request['posts'])),
      __CLASS__
    );
  }
}
