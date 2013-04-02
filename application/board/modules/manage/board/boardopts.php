<?php

class manage_board_board_boardopts extends kxCmd {
  public function exec(kxEnv $environment){
    switch($this->request['do']) {
      case 'edit':
        $this->_edit();
        break;
      
      case 'post':
        $this->_update();
        break;
        
      default:
        //there's a problem
        die();
        break;
    }
    
    kxTemplate::output("manage/boardopts", $this->twigData);
  }
  
  private function _edit() {
    $board_options = kxEnv::Get('cache:boardopts:' . $this->request['board']);
    
    $this->twigData['board_options'] = $board_options[0];
    $this->twigData['sections'] = $this->db->select("sections")
                                           ->fields("sections")
                                           ->orderBy("section_order")
                                           ->execute()
                                           ->fetchAll();
    $this->twigData['filetypes'] = kxEnv::get('cache:attachments:filetypes');
  }
  
  private function _update() {
    // A few checks to ensure a valid submission
    kxForm::addRule('id', 'numeric')
          ->addRule('board', 'required')
          ->check();
    $board_exists = $this->db->select("boards")
                             ->condition("board_id", $this->request['id'])
                             ->countQuery()
                             ->execute()
                             ->fetchField();
    // Should return 1, otherwise something is very wrong
    if ($board_exists != 1) {
      die();
    }
    /*echo '<pre>';
    print_r($this->request);
    die();*/
    $board_fields = array(
              'board_desc'   => $this->request['title'],
              'board_locale' => $this->request['locale'],
              'board_type'   => (int) $this->request['type'],
              'board_upload_type' => (int) $this->request['upload_type'],
              'board_section' => (int) $this->request['board_section'],
              'board_order'   => (int) $this->request['order'],
              'board_header_image'   => $this->request['header_image'],
              'board_include_header' => $this->request['include_header'],
              'board_anonymous' => $this->request['anonymous'],
              // TODO: Add this to the template
              'board_default_style'   => 'edaha',
              'board_allowed_embeds'  => '',
              'board_max_upload_size' => (int) $this->request['max_upload_size'],
              'board_max_message_length' => (int) $this->request['max_message_length'],
              'board_max_pages'   => (int) $this->request['max_pages'],
              'board_max_age'     => (int) $this->request['max_age'],
              'board_mark_page'   => (int) $this->request['mark_page'],
              'board_max_replies' => (int) $this->request['max_replies'],
              'board_locked' => (int) isset($this->request['locked']),
              'board_show_id' => (int) isset($this->request['show_id']),
              'board_compact_list' => (int) isset($this->request['compact_list']),
              'board_reporting' => (int) isset($this->request['reporting']),
              'board_captcha' => (int) isset($this->request['captcha']),
              'board_archiving' => (int) isset($this->request['archiving']),
              'board_catalog' => (int) isset($this->request['catalog']),
              'board_no_file' => (int) isset($this->request['no_file']),
              'board_redirect_to_thread' => (int) isset($this->request['redirect_to_thread']),
              'board_forced_anon' => (int) isset($this->request['forced_anon']),
              'board_trial' => (int) isset($this->request['trial']),
              'board_popular' => (int) isset($this->request['popular'])
             );
    
    $this->db->update("boards")
             ->fields($board_fields)
             ->condition('board_id', $this->request['id'])
             ->execute();
    
    // Clear previous filetype settings
    $this->db->delete("board_filetypes")
             ->condition("board_id", $this->request['id'])
             ->execute();
    
    // Add new filetypes
    
    foreach ($this->request['filetypes'] as $type) {
      $this->db->insert("board_filetypes")
               ->fields(array('board_id' => $this->request['id'], 'type_id' => $type))
               ->execute();
    }
                                    
    
    $this->twigData['boardredirect'] = true;
    $this->twigData['notice']['type'] = 'success';
    $this->twigData['notice']['message'] = _gettext('Board updated. Redirecting...');
    
    // Update the cache
    $this->recacheBoardOptions();
  }
  
  public function recacheBoardOptions() {
    // Get the requested board's options
    $recache_board_options = $this->db->select("boards")
                              ->fields("boards")
                              ->condition("board_name",$this->request['board'])
                              ->execute()
                              ->fetchAll();
    // Get its associated filetypes
    $recache_board_options[0]->board_filetypes = $this->db->select("board_filetypes")
                                               ->fields("board_filetypes", array('type_id'))
                                               ->condition("board_id", $recache_board_options[0]->board_id)
                                               ->execute()
                                               ->fetchCol();
    /*echo 'recache:<br><pre>';
    print_r($recache_board_options);
    echo '</pre>';
    die();*/
    // And cache them
    kxEnv::set('cache:boardopts:' . $this->request['board'], $recache_board_options);
  }
  
}