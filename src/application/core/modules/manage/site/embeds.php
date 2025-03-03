<?php

class manage_core_site_embeds extends kxCmd {
  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;
  
  public function exec(kxEnv $environment) {
    switch ( (isset($_GET['do'])) ? $_GET['do'] : '' ) {
      case 'edit':
        $this->_edit();
        break;
      default:
        $this->_show();
        break;
    }        
  }
  
  private function _show() {
    if ($this->request['action'] == 'edit' && !empty($this->request['embed_id'])) {
      $this->twigData['edit_embed'] = $this->db->select("embeds")
                                 ->fields("embeds")
                                 ->condition("embed_id", $this->request['embed_id'])
                                 ->execute()
                                 ->fetch();
    }
    $this->twigData['embeds'] = $this->db->select("embeds")
                                 ->fields("embeds")
                                 ->orderBy("embed_id")
                                 ->execute()
                                 ->fetchAll();
    kxTemplate::output("manage/embeds", $this->twigData);
  }
  private function _edit() {
    if ($this->request['action'] == 'delete' && !empty($this->request['embed_id'])) {
      $this->db->delete("embeds")
        ->condition("embed_id", $this->request['embed_id'])
        ->execute();
        $this->twigData['embed_success'] = 'Deleted';
        $this->_show();
        return;
    }
    if (empty($this->request['embed_name']) || empty($this->request['embed_ext']) || empty($this->request['embed_url'])  || empty($this->request['embed_width'])  || empty($this->request['embed_height'])  || empty($this->request['embed_width'])) {
      $this->twigData['embed_error'] = true;
      $this->_show();
      return;
    }
    if ($this->request['action'] == 'add' && !empty($this->request['embed_id'])) {
      $this->db->update("embeds")
        ->fields(array(
          'embed_name' => $this->request['embed_name'],
          'embed_ext' => $this->request['embed_ext'],
          'embed_height' => $this->request['embed_height'],
          'embed_width' => $this->request['embed_width'],
          'embed_url' => $this->request['embed_url'],
          'embed_code' => $this->request['embed_code']
        ))
        ->condition("embed_id", $this->request['embed_id'])
        ->execute();
        $this->twigData['embed_success'] = 'Updated';
    } elseif ($this->request['action'] == 'add') {
      $this->db->insert("embeds")
        ->fields(array(
          'embed_name' => $this->request['embed_name'],
          'embed_ext' => $this->request['embed_ext'],
          'embed_height' => $this->request['embed_height'],
          'embed_width' => $this->request['embed_width'],
          'embed_url' => $this->request['embed_url'],
          'embed_code' => $this->request['embed_code']
        ))
        ->execute();
        $this->twigData['embed_success'] = 'Added';
    }
    $this->_show();
  }
}