<?php

class manage_core_site_front extends kxCmd {
  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;
  
  public function exec(kxEnv $environment){
    switch ($this->request['action']) {
      case 'post':
        $this->_post();
        break;
      case 'edit':
        $this->_edit();
        break;
      case 'del':
        $this->_del();
        break;
    }
    switch ($this->request['do']) {        
      case 'news':
      default:
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
  
  private function _faq() {
    $this->twigData['entries'] = $this->db->select("front")
                                      ->fields("front")
                                      ->condition("entry_type", 1)
                                      ->orderBy("entry_order", "ASC")
                                      ->execute()
                                      ->fetchAll();
    kxTemplate::output("manage/faq", $this->twigData);
  }
  
  private function _rules() {
    $this->twigData['entries'] = $this->db->select("front")
                                      ->fields("front")
                                      ->condition("entry_type", 2)
                                      ->orderBy("entry_order", "ASC")
                                      ->execute()
                                      ->fetchAll();
    kxTemplate::output("manage/rules", $this->twigData);
  }  

  private function _post() {
    // Handles posting of front page content
    kxForm::addRule('subject','required')
          ->addRule('message','required')
          ->addRule('type','numeric')
          ->check();
    $fields = array(
              'entry_subject' => $this->request['subject'],
              'entry_message' => $this->request['message'],
              'entry_type'    => intval($this->request['type'])
              );

    if ($this->request['do'] == 'news') {
      // News-specific fields
      $fields['entry_email'] = $this->request['email'];
      $fields['entry_name'] = ''; //TODO: make entry_name for current username
      
      if ($this->request['edit'] == "") {
        $fields['entry_time'] = time();


      }
    } else {
      // Other front page fields
      $fields['entry_order'] = $this->request['order'];
	if ($this->request['order'] == "") {
		$fields['entry_order'] = 0;
	}
    }
    
    if ($this->request['edit'] == "") {
      // New post
      $this->db->insert("front")
               ->fields($fields)
               ->execute();
      $this->twigData['notice'] = _('Entry successfully added.');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf('Created new %s entry', $this->request['do']),
        __CLASS__
      );
    } else {
      // Update post
      $this->db->update("front")
               ->fields($fields)
               ->condition("entry_id", $this->request['edit'])
               ->execute();
      $this->twigData['notice'] = _('Entry successfully edited.');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf('Edited %s entry', $this->request['do']),
        __CLASS__
      );
    }
    $this->twigData['notice_type'] = 'success';
  }
  
  private function _edit() {
    if ($this->request['do'] == 'news') {
      $type = 0;
    } else if ($this->request['do'] == 'faq') {
      $type = 1;
    } else if ($this->request['do'] == 'rules') {
      $type = 2;
    }
    $this->twigData['entry'] = $this->db->select("front")
                                        ->fields("front")
                                        ->condition("entry_type", $type)
                                        ->condition("entry_id", $this->request['id'])
                                        ->orderBy("entry_id", "DESC")
                                        ->execute()
                                        ->fetchAssoc();
  }
  
  private function _del() {
    $this->db->delete("front")
             ->condition("entry_id", $this->request['id'])
             ->execute();
    $this->twigData['notice_type'] = 'success';
    $this->twigData['notice'] = _('Entry successfully deleted.');
    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf('Deleted %s entry', $this->request['do']),
      __CLASS__
    );
  }

}
