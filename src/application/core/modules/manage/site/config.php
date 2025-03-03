<?php

class manage_core_site_config extends kxCmd {
  /**
   * Groups that settings are placed into
   * 
   * @var Array()
   */
  protected $config_groups;

  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;

  public function exec(kxEnv $environment) {
    switch ( (isset($_GET['do'])) ? $_GET['do'] : '' ) {
      case 'save':
        $this->_save();
        break;
      default:
        $this->_show();
        break;
    }
    kxTemplate::output("manage/site_config", $this->twigData);
  }
  
  private function _show() {    
    $this->_loadOptions();
    
    $this->twigData['config_groups'] = $this->config_groups;
  }
  private function _save() {  
    $this->_loadOptions();
    // TODO: Make this work.
    /*kxForm::addRule('config[images:thumbw]', 'numeric')
          ->addRule('config[images:thumbh]', 'numeric')
          ->addRule('config[images:replythumbw]', 'numeric')
          ->addRule('config[images:replythumbh]', 'numeric')
          ->addRule('config[images:catthumbw]', 'numeric')
          ->addRule('config[images:catthumbh]', 'numeric')
          ->addRule('config[display:imgthreads]', 'numeric')
          ->addRule('config[display:txtthreads]', 'numeric')
          ->addRule('config[display:replies]', 'numeric')
          ->addRule('config[display:stickyreplies]', 'numeric')
          ->addRule('config[limits:threaddelay]', 'numeric')
          ->addRule('config[limits:replydelay]', 'numeric')
          ->addRule('config[misc:modlogdays]', 'numeric')
          ->check();*/
    foreach ($this->config_groups as $group) {
      foreach ($group->options as $option) {
        if ($option->config_type == 'true_false') {
          $_POST['config'][$option->config_variable] = (boolean) $_POST['config'][$option->config_variable];
        }
      }
    }
    //echo '<pre>';
    //var_dump($_POST);
    //var_dump($this->request);
    //die();
    
    foreach ($_POST['config'] as $option => $value) {
      kxEnv::set('kx:' . $option, $value);
    }
    
    $new_config = array("all" => current((array) kxEnv::dumpConfig()));
    //print_r($new_config);
    unset($new_config['all']['kx']['autoload']);
    unset($new_config['all']['kx']['classes']);
    
    $new_config = kxYml::dump($new_config);
    
    $new_config = "#!php" . "\n" . "#<?php header('HTTP/1.1 404 Not Found'); die(); ?>" . "\n" . $new_config;
    
    if (file_put_contents(KX_ROOT . "/config/main.yml.php", $new_config) !== false) {
      $this->twigData['notice']['type'] = 'success';
      $this->twigData['notice']['message'] = _gettext('Configuration updated.');
    } else {
      $this->twigData['notice']['type'] = 'failure';
      $this->twigData['notice']['message'] = _gettext('An error occured while trying to save configuration data.');
    }
    
    $this->_show();
  }
  
  private function _loadOptions() {
    if (!isset($this->config_groups)) {
      $this->config_groups = $this->db->select("config_groups")
                          ->fields("config_groups")
                          ->orderBy("group_id")
                          ->execute()
                          ->fetchAll();
      
      $options = $this->db->select("configuration")
                          ->fields("configuration")
                          ->where("config_group = ?")
                          ->build();
      
      foreach ($this->config_groups as $group) {
        $options->execute(array($group->group_id));
        $group->options = $options->fetchAll();
      }
    }
  }
}