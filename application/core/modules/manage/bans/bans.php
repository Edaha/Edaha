<?php

class manage_core_bans_bans extends kxCmd {
  
  public function exec( kxEnv $environment ) {
    // TODO: Complete this
    $this->twigData = array();
    
    $this->twigData['sections'] = $this->db->select("sections")
                                       ->fields("sections")
                                       ->orderBy("section_order")
                                       ->execute()
                                       ->fetchAll();
    
    $this->twigData['boards'] = $this->db->select("boards")
                                     ->fields("boards")
                                     ->orderBy("board_section")
                                     ->orderBy("board_order")
                                     ->execute()
                                     ->fetchAll();
    
    kxTemplate::output('manage/bans', $this->twigData);
  }
  
}

?>