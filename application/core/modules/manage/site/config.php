<?php

class manage_core_site_config extends kxCmd {
    $this->twigData['locale'] = kxEnv::Get('kx:misc:locale');
    $result = $this->db->select('staff', 'stf')
               ->fields('stf', array('user_name'));
    $result->innerJoin("manage_sessions", "ms", "ms.session_staff_id = stf.user_id");
    $this->twigData['name'] = $result->condition('session_id', $this->request['sid'])
                               ->execute()
                               ->fetchField();
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