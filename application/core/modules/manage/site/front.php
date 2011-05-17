<?php

class manage_core_site_front extends kxCmd {
  public function exec(kxEnv $environment){
    switch ($this->request['do']) {        
      case 'news':
      default:
        $this->_news();
        break;
      case 'news-edit':
        $this->_editNews();
        $this->_news();
        break;
      case 'news-del':
        $this->_delNews();
        $this->_news();
        break;
      case 'news-post':
        $this->_postNews();
        $this->_news();
        break;
      case 'faq':
        $this->_faq();
        break;
      case 'rules':
        $this->_rules();
        break;
    }    
  }
  private function _news() {
    $this->twigData['entries'] = $this->db->select("front")
                                      ->fields("front")
                                      ->condition("entry_type", 0)
                                      ->orderBy("entry_time", "DESC")
                                      ->execute()
                                      ->fetchAll();
    kxTemplate::output("manage/news", $this->twigData);
  }
  
  private function _editNews() {
  
    $this->twigData['news'] = $this->db->select("front")
                                       ->fields("front")
                                       ->condition("entry_type", 0)
                                       ->condition("id", $this->request['id'])
                                       ->orderBy("entry_time", "DESC")
                                       ->execute()
                                       ->fetchAssoc();
  }
  
  private function _postNews() {
      kxForm::addRule('subject','required')
            ->addRule('news','required')
            ->check();
      if($this->request['edit'] == ""){
        $this->db->insert("front")
               ->fields(array(
                 'entry_subject' => $this->request['subject'],
                 'entry_message' => $this->request['news'],
                 'entry_email'   => $this->request['email'],
                 'entry_type'    => 0,
                 'entry_time'    => time()
               ))
               ->execute();
        $dwoo_data['notice'] = _gettext('News entry successfully added.');
      }else{
          $fields = array();
          $fields['entry_subject'] = $this->request['subject'];
          $fields['entry_message'] = $this->request['news'];
          $fields['entry_email'] = $this->request['email'];
        $this->db->update("front")
                 ->fields($fields)
                 ->condition("id", $this->request['edit'])
                 ->execute();
        $dwoo_data['notice'] = _gettext('News entry successfully editted.');
      }
        $dwoo_data['notice_type'] = 'success';
  }
  
    private function _delNews() {
    $this->db->delete("front")
             ->condition("id", $this->request['id'])
             ->execute();
    $dwoo_data['notice_type'] = 'success';
    $dwoo_data['notice'] = _gettext('News entry successfully deleted.');
  }

}