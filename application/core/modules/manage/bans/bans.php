<?php

class manage_core_bans_bans extends kxCmd {
  
  public function exec( kxEnv $environment ) {
    // TODO: Complete this
    $this->twigData = array();
    
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
    
    kxTemplate::output('manage/bans', $this->twigData);
  }
  
}

?>