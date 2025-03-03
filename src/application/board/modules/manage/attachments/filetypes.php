<?php

class manage_board_attachments_filetypes extends kxCmd {
  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;
  

  public function exec(kxEnv $environment){
    switch($this->request['do']) {
      case 'del':
        $this->_del();
        break;

      case 'edit':
        $this->_edit();
        break;
      
      case 'edit-post':
      case 'add':
        $this->_post();
        break;
    }
    $this->_filetypes();
  }
  
  private function _filetypes() {
    // Retrieve filetypes from cache
    $this->twigData['filetypes'] = kxEnv::get('cache:attachments:filetypes');

    kxTemplate::output("manage/filetypes", $this->twigData);
  }
  
  private function _post() {
    // Basic check
    $check = kxForm::addRule('ext','required');
    if ($this->request['do'] == 'edit') {
      $check->addRule('id', 'numeric');
    }
    $check->check();
          
    $fields = array(
                'type_ext' => $this->request['ext'],
                'type_mime' => $this->request['mime'],
                'type_image' => $this->request['image'],
                'type_image_width' => (int) $this->request['image_width'],
                'type_image_height' => (int) $this->request['image_height'],
                'type_force_thumb' => (int) $this->request['create_thumbnail']
              );
          
    try {
      if ($this->request['do'] == 'add') {
        // New entry
        $this->db->insert("filetypes")
                 ->fields($fields)
                 ->execute();
      } else {
        // Modifying old
        $this->db->update("filetypes")
                 ->fields($fields)
                 ->condition('type_id', $this->request['id'])
                 ->execute();
      }
    } catch (Exception $e) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = _('An error occured: ') . $e->getMessage();
    }
    
    if (!isset($this->twigData['notice'])) {
      $this->twigData['notice']['type'] = 'success';
      $this->twigData['notice']['message'] = ($this->request['do'] == 'add') ? _('Filetype added successfully!') : _('Filetype edited successfully!');
      $log_message = ($this->request['do'] == 'add') ? _('Added filetype %s') : _('Edited filetype %s');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf($log_message, $this->request['ext']),
        __CLASS__
      );
    }
    
    // Need to update the cache
    $this->recacheFiletypes();
  }
  
  private function _del() {
    // Basic check
    kxForm::addRule('id', 'numeric')
          ->check();
    
    try {
      $this->db->delete("filetypes")
               ->condition("type_id", $this->request['id'])
               ->execute();
      $this->db->delete("board_filetypes")
               ->condition("type_id", $this->request['id'])
               ->execute();
    } catch (Exception $e) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = _('An error occured: ') . $e->getMessage();
    }
    
    if (!isset($this->twigData['notice'])) {
      $this->twigData['notice']['type'] = 'success';
      $this->twigData['notice']['message'] = _('Filetype deleted successfully!');
    }
    
    // Need to update the cache
    $this->recacheFiletypes();
  }
  
  private function _edit() {
    kxForm::addRule('id', 'numeric')
          ->check();
    
    $this->twigData['filetype'] = $this->db->select("filetypes")
                                           ->fields("filetypes")
                                           ->condition('type_id', $this->request['id'])
                                           ->execute()
                                           ->fetch();
  }
  
  public function recacheFiletypes() {
    // Get all the filetypes...
    $recache_filetypes = $this->db->select("filetypes")
                                  ->fields("filetypes")
                                  ->orderBy("type_id")
                                  ->execute()
                                  ->fetchAll();
    /*echo '<pre>';
    print_r($recache_filetypes);
    echo '</pre>';*/
    // Cache them
    kxEnv::set('cache:attachments:filetypes', $recache_filetypes);
    
  }
  
}

?>