<?php

class manage_core_site_config extends kxCmd {
  public function exec(kxEnv $environment) {
    switch ( (isset($_GET['do'])) ? $_GET['do'] : '' ) {
      case 'edit':
        $this->_show();
        break;
      case 'save':
        $this->_save();
        break;
      default:
        $this->_show();
        break;
    }        
  }
  
  private function _show() {
    $options = $this->db->select("configuration")
                        ->fields("configuration")
                        ->orderBy("config_id")
                        ->execute()
                        ->fetchAll();
    $twig_data['options'] = $options;
    kxTemplate::output("manage/site_config", $twig_data);
  }
  private function _save() {
    echo "not implemented";
    $this->_show();
  }
}