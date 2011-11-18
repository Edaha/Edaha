<?php

class manage_board_board_board extends kxCmd {
  public function exec(kxEnv $environment){
    switch ($this->request['action']) {
      case 'post':
        $this->_post();
        break;
      case 'del':
        $this->_del();
        break;
    }
    switch ($this->request['do']) {        
      case 'board':
      default:
        $this->_board();
        break;
    }    
  }
  
  private function _board() {
    $this->twigData['entries'] = $this->db->select("boards")
                                      ->fields("boards")
                                      ->orderBy("board_name")
                                      ->execute()
                                      ->fetchAll();
    kxTemplate::output("manage/board", $this->twigData);
  }
  
  private function _post() {
    // Handles adding board
    kxForm::addRule('name','required')
          ->addRule('description','required')
          ->addRule('start','numeric')
          ->check();
    $fields = array(
              'board_name' => $this->request['name'],
              'board_desc' => $this->request['description'],
              'board_start'    => intval($this->request['start']),
              'createdon' => time(),
              'image' => '',
              'includeheader' => ''
              );
       // If the first post ID is left empty make it 1
    if ($fields['board_start'] == "") {
      $fields['board_start'] = 1;
    }
    if ($this->request['edit'] == "") {
      // Add board
      $this->db->insert("boards")
               ->fields($fields)
               ->execute();
      $this->twigData['notice'] = _gettext('Board successfully added.');
    } else {
      // Edit board
      $this->db->update("boards")
               ->fields($fields)
               ->condition("board_id", $this->request['edit'])
               ->execute();
      $this->twigData['notice'] = _gettext('Board successfully edited.');
    }
    $this->twigData['notice_type'] = 'success';
  }
  
  private function _del() {
    $this->db->delete("boards")
             ->condition("board_id", $this->request['id'])
             ->execute();
    $this->twigData['notice_type'] = 'success';
    $this->twigData['notice'] = _gettext('Board successfully deleted.');
  }

}
