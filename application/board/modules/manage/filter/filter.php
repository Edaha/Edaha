<?php

class manage_board_filter_filter extends kxCmd {
  
  public function exec( kxEnv $environment ) {
      switch ($this->request['do']) {        
        case 'add':
          $this->addFilter();
        case 'remove':
          $this->delFilter();
        case 'edit':
          $this->editFilter();
        case 'view':
        default:
          $this->showFilters();
          break;
      }
  }
  
  public function showFilters() {
        $this->twigData['filters'] = $this->db->select("filter")
                                              ->fields("filter")
                                              ->execute()
                                              ->fetchAll();
        $this->twigData['sections'] = kxFunc::fullBoardList();
        
        kxTemplate::output('manage/filter', $this->twigData);
  }
}