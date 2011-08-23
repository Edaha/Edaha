<?php

class manage_board_statistics_stats extends kxCmd {
  public function exec(kxEnv $environment){
  
    $type = Array("posts", "uniques", "files");
    if (!isset($this->request['time'])) {
      $this->request['time'] = 24;
    }
    $boards = $this->db->select("boards")
                       ->fields("boards")
                       ->execute()
                       ->fetchAll();
    foreach ($boards as $board) {
      reset($type);
      do {
        switch (current($type)) {
          case "posts":
            // Total posts
            $results = $this->db->select("posts");
            break;
          case "uniques":
            // Total uniques
            $results = $this->db->select("posts")
                                ->fields("posts", array("post_ip_md5"))
                                ->distinct();
            break;
          case "files":
            // Total Files
            $results = $this->db->select("post_files");
            $results->join("posts", NULL, "file_post = post_id");
            break;
        }
        $results = $results->condition("post_deleted", 0)
                           ->condition("post_board", $board->board_id);
        if (!empty($this->request['time'])) {
          $results = $results->condition("post_timestamp", time()-( $this->request['time'] * 60 * 60 ), ">=");
        }
        $twigData['stats'][$board->board_name][current($type)] = $results->countQuery()
                                                                ->execute()
                                                                ->fetchField();
                                                       
      } while ( next($type) );
    }
    kxTemplate::output("manage/stats", $twigData);
  }
}