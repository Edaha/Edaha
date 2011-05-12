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
    $this->twigData['news'] = $this->db->select("front")
                                       ->fields("front")
                                       ->condition("entry_type", 0)
                                       ->orderBy("entry_time", "DESC")
                                       ->execute()
                                       ->fetchAll();
    kxTemplate::output("manage/news");
  }
  
  private function _postNews() {
      kxForm::addRule('subject','required')
            ->addRule('news','required')
            ->check();
            
      $this->db->insert("front")
               ->fields(array(
                 'entry_subject' => $this->request['subject'],
                 'entry_message' => $this->request['news'],
                 'entry_type' => 0,
                 'entry_time' => time()
               ))
               ->execute();
        $dwoo_data['notice_type'] = 'success';
        $dwoo_data['notice'] = _gettext('User added successfully');
  }

}