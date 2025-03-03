<?php

class manage_board_filter_filter extends kxCmd {
  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;
  
  
  public function exec( kxEnv $environment ) {
    switch ($this->request['do']) {
      case 'add':
      case 'edit-post':
        $this->_postFilter();
        break;
      case 'del':
        $this->_delFilter();
        break;
      case 'edit':
        $this->_editFilter();
        break;
      default:
        break;
    }
    $this->_showFilters();
  }
  
  private function _showFilters() {
    $filters = kxEnv::Get('cache:filters:wordfilters');
    
    $this->twigData['filters'] = $filters;
    $this->twigData['sections'] = kxFunc::fullBoardList();
    
    kxTemplate::output('manage/filter', $this->twigData);
  }
  
  private function _postFilter() {
    kxForm::addRule('filter_word', 'required')
          ->check();
          
    $fields = array(
                    'filter_word' => $_POST['filter_word'],
                    'filter_type' => array_sum($this->request['filter_actions']),
                    'filter_replacement' => $_POST['filter_replacement'],
                    'filter_added' => time(),
                    'filter_regex' => (int) isset($this->request['filter_regex'])
                    );
    
    // Checks on the ban duration. IMPROVE?
    if (strToLower($this->request['filter_ban_duration']) == 'forever') {
      // A ban lasting forever!
      $ban_duration = 0;
    } else if ($this->request['filter_ban_duration'] == '') {
      $ban_duration = null;
    } else if (!is_numeric($this->request['filter_ban_duration'])) {
      // It's in strtotime format (we hope, anyways)
      $ban_duration = (int) strtotime($this->request['filter_ban_duration'], 0);
    } else {
      // It's already a time in seconds
      $ban_duration = (int) $this->request['filter_ban_duration'];
    }
    
    $fields['filter_ban_duration'] = $ban_duration;
    
    try {
      if ($this->request['do'] == 'add') {
        $filter_id = $this->db->insert("filters")
                              ->fields($fields)
                              ->execute();
        
      } else {
        kxForm::addRule('id', 'numeric')
              ->check();
        
        $this->db->update("filters")
                 ->fields($fields)
                 ->condition("filter_id", $this->request['id'])
                 ->execute();
        // Clear the board entries to be replaced later in the function
        $this->db->delete("board_filters")
                 ->condition("filter_id", $this->request['id'])
                 ->execute();
        
      }
      
      
      foreach ($this->request['filter_boards'] as $board) {
        $this->db->insert("board_filters")
                 ->fields(array(
                                'board_id'  => $board,
                                'filter_id' => ($this->request['do'] == 'add') ? $filter_id : $this->request['id']
                                ))
                 ->execute();
      }
    } catch (Exception $e) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = _gettext('An error occured: ') . $e->getMessage();
    }
    
    if (!isset($this->twigData['notice'])) {
      $this->twigData['notice']['message'] = ($this->request['do'] == 'add') ? _gettext('Filter successfully added!') : _gettext('Filter successfully edited!');
      $this->twigData['notice']['type'] = 'success';
    }
    
    $this->_recacheFilters();
  }
  
  private function _editFilter() {
    // Load the filter to be edited
    kxForm::addRule('id', 'numeric')
          ->check();
    
    $filters = kxEnv::get('cache:filters:wordfilters');
    
    // Have to remove the unnecessary filters (aka the ones that aren't what we're editing) from the filters array
    for ($i=0; $i<count($filters); $i++) {
      if ($filters[$i]->filter_id !== $this->request['id']) {
        unset($filters[$i]);
      }
    }
    
    $this->twigData['edit_filter'] = current($filters);
  }
  
  private function _delFilter() {
    kxForm::addRule('id', 'numeric')
          ->check();
          
    try {
      $this->db->delete("filters")
               ->condition("filter_id", $this->request['id'])
               ->execute();
      $this->db->delete("board_filters")
               ->condition("filter_id", $this->request['id'])
               ->execute();
    } catch (Exception $e) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = _gettext('An error occured: ') . $e->getMessage();
    }
    
    if (!isset($this->twigData['notice'])) {
      $this->twigData['notice']['type'] = 'success';
      $this->twigData['notice']['message'] = _gettext('Filter deleted successfully!');
    }
    
    $this->_recacheFilters();
  }
  
  private function _recacheFilters() {
    $filters = $this->db->select("filters")
                    ->fields("filters")
                    ->orderBy("filter_id")
                    ->execute()
                    ->fetchAll();
   
    $fetch_boards = $this->db->select("board_filters");
    $fetch_boards->innerJoin("boards", "", "board_filters.board_id = boards.board_id");
    $fetch_boards = $fetch_boards->fields("boards", array('board_name'))
                                 ->where("filter_id = ?")
                                 ->build();
    foreach ($filters as $filter) {
      $fetch_boards->execute(array($filter->filter_id));
      $filter->filter_boards = $fetch_boards->fetchCol();
    }
    
    kxEnv::set('cache:filters:wordfilters', $filters);
  }
}