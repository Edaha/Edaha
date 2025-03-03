<?php

class manage_core_index_statgraphs extends kxCmd {

  public function exec( kxEnv $environment ) {
    
    $this->request['period'] = (isset($this->request['period'])) ? (int) $this->request['period'] : 1;
    
    $this->twigData['postsperperiod'] = $this->_graphData($this->request['period']);
    
    kxTemplate::output("manage/statgraphs", $this->twigData);
  }
  
  private function _graphData($period = 1) {
    $boards = $this->db->select("boards")
                   ->fields("boards", array("board_id", "board_name"))
                   ->execute()
                   ->fetchAll();
    
    $postsperboard = $this->db->select("posts")
                              ->fields("posts")
                              ->where("post_board = ? AND post_timestamp >= ?")
                              ->countQuery()
                              ->build();
    $period = time() - (86400 * $period);
    foreach ($boards as $board) {
      $postsperboard->execute(array($board->board_id, $period));
      $values[] = array($board->board_name, (int) current($postsperboard->fetchCol()));
    }
    
    // Array with the needed column information
    $columns = array(
                  array('id' => 'board_name',
                        'label' => 'Board',
                        'type' => 'string'
                        ),
                  array('id' => 'board_posts',
                        'label' => 'Posts',
                        'type' => 'number'
                        )
                  );
    
    return $this->_buildJson($columns, $values);
  }
  
  // Builds json for Google Graph API thing
  private function _buildJson($columns = array(), $values = array()) {
    $numCols = count($columns);
    
    $json = array('cols' => $columns);
    
    foreach($values as $key => $arr) {
      foreach ($arr as $val) {
        $json['rows'][$key]['c'][] = array('v' => $val);
      }
    }
    
    /*echo '<pre>';
    print(json_encode($json));
    die();*/
    return json_encode($json);
  }

}

?>