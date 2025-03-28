<?php

class manage_board_statistics_stats extends kxCmd {
  public function exec(kxEnv $environment){
  
    $types = Array("posts", "uniques", "files");
    if (!isset($this->request['time'])) {
      $this->request['time'] = 24;
    }
    $boards = $this->db->select("boards")
                       ->fields("boards")
                       ->execute()
                       ->fetchAll();

    foreach ($types as $type) {
      switch ($type) {
        case "posts":
          // Total posts
          $result = $this->db->select("posts");
          break;
        case "uniques":
          // Total uniques
          $result = $this->db->select("posts")
                              ->fields("posts", array("ip_md5"))
                              ->distinct();
          break;
        case "files":
          // Total Files
          $result = $this->db->select("post_files");
          $result->join("posts", NULL, "file_post = post_id");
          break;
      }
      $result = $result->where("is_deleted = ?")
                       ->where("board_id = ?")
                       ->where("created_at_timestamp >= ?");
      $results[$type] = $result->countQuery()
                               ->build();
    }

    foreach ($boards as $board) {
      foreach ($results as $k=>$result) {
            $result->execute(array(0, $board->board_id, (!empty($this->request['time']) ? (time()-( $this->request['time'] * 60 * 60 )) : 0)));
            $twigData['stats'][$board->board_name][$k] = $result->fetchField();
      }
    }

    kxTemplate::output("manage/stats", $twigData);
  }
}