<?php

class manage_board_board_board extends kxCmd {
  public $errorMessage='';
  public function exec(kxEnv $environment){
    switch ($this->request['action']) {
      case 'post':
        $this->_post();
        break;
      case 'del':
        $this->_del();
        break;
      case 'regen':
        // Vars $_GET['id']
        if($this->onRegen()) {
          $this->twigData['notice']['type'] = 'success';
          $this->twigData['notice']['message'] = _gettext('Board successfully regenerated!');
        } else {
          $this->twigData['notice']['type'] = 'error';
          $this->twigData['notice']['message'] = _gettext('Board failed to regenerate').": ".$this->errorMessage;
        }
        break;
    }
    switch ($this->request['do']) {        
      case 'board':
      default:
        $this->_board();
        break;
    }    
  }
  
  private function onRegen()
  {
    // Grabing essential data about the board
    $boardType = $this->db->select("boards")
                      ->fields("boards", array("board_type"))
                      ->condition("board_name", $this->request['board'])
                      ->execute()
                      ->fetchField();
    // Nope
    if ($boardType === false) {
      $this->errorMessage=sprintf(_gettext("Couldn't find board /%s/."),$this->request['board']);
      return false;
    }
    //Check against our built-in board types.
    if (in_array($boardType, array(0,1,2,3))){
      $types = array('image', 'text', 'oekaki', 'upload');
      $module_to_load = $types[$boardType];
    }
    //Okay, so it's a a custom board type. Let's find out which kind...
    else {
      $result = $this->db->select("modules")
                         ->fields("modules", array("module_variables", "module_directory"))
                         ->condition("module_application", 1)
                         ->execute()
                         ->fetchAll();
      foreach ($result as $line) {
        $varibles = unserialize($line->module_variables);
        if (isset($variables['board_type_id']) && $variables['board_type_id'] == $boardType) {
          $module_to_load = $line->module_directory;
        }
      }
    }
    // Module loading time!
    $moduledir = kxFunc::getAppDir( "board" ) . '/modules/public/' . $module_to_load . '/';
    if (file_exists($moduledir . $module_to_load . '.php')) {
      require_once($moduledir . $module_to_load . '.php');
    }
	
    // Some routine checks...
    $className = "public_board_".$module_to_load."_".$module_to_load;
    if(class_exists($className)) {
      $module_class = new ReflectionClass($className);
      if ( $module_class->isSubClassOf(new ReflectionClass('kxCmd'))) {
        $this->_boardClass = $module_class->newInstance($this->environment);
        $this->_boardClass->execute($this->environment);
      } else {
	    $this->errorMessage=sprintf("Couldn't find module %s",$className);
        return false;
      }
    }
	
	$this->_boardClass->regeneratePages();
	$this->_boardClass->regenerateThreads();
	return true;
  }
  
  private function _board() {
    // DATABASE DRIVERS, DATABASE DRIVERS NEVER CHANGE
    // EXCEPT WHEN SAZ FUCKS WITH THEM
    $array_o_boards = $this->db->select("boards")
                      ->fields('boards',array('board_name','board_desc'))
                      ->orderBy("board_name")
                      ->execute()
                      ->fetchAll();
    $this->twigData['entries'] = array();
    foreach($array_o_boards as $board){
      $this->twigData['entries'][$board->board_name]=$board->board_desc;
    }
    
    kxTemplate::output("manage/board", $this->twigData);
  }
  
  private function _post() {
    // Handles adding board
    kxForm::addRule('name','required')
          ->addRule('description','required')
          ->addRule('start','numeric')
          ->check();
    $fields = array(
              'board_name'  => $this->request['name'],
              'board_desc'  => $this->request['description'],
              'board_start' => intval($this->request['start']),
              'board_created_on'   => time(),
              'board_header_image' => '',
              'board_include_header' => ''
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
      $this->twigData['notice']['message'] = _gettext('Board successfully edited.');
    }
    $this->twigData['notice']['type'] = 'success';
  }
  
  private function _del() {
    $this->db->delete("boards")
             ->condition("board_id", $this->request['board'])
             ->execute();
    $this->twigData['notice_type'] = 'success';
    $this->twigData['notice'] = _gettext('Board successfully deleted.');
  }

}
