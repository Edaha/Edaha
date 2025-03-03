<?php

class manage_board_board_board extends kxCmd
{
  /**
   * Arguments eventually being sent to twig
   *
   * @var Array()
   */
  protected $twigData;

  public $errorMessage = '';

  private $_boardClass;

  public function exec(kxEnv $environment)
  {
    switch ($this->request['action']) {
      case 'post':
        $this->_post();
        break;
      case 'del':
        $this->_del();
        break;
      case 'regen':
        // Vars $_GET['id']
        if ($this->onRegen()) {
          $this->twigData['notice']['type'] = 'success';
          $this->twigData['notice']['message'] = sprintf(_gettext('Board %s successfully regenerated!'), $this->request['board']);
        } else {
          $this->twigData['notice']['type'] = 'error';
          $this->twigData['notice']['message'] = _gettext('Board failed to regenerate') . ": " . $this->errorMessage;
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
      $this->errorMessage = sprintf(_gettext("Couldn't find board /%s/."), $this->request['board']);
      return false;
    }

    $board_modules = $this->db->select("modules")
      ->fields("modules", array("module_file"))
      ->condition("module_application", "board")
      ->condition("module_manage", 0)
      ->execute()
      ->fetchCol();
    foreach ($board_modules as $module) {
      if ($module == $boardType) {
        $module_to_load = $module;
      }
    }

    // Module loading time!
    $moduledir = kxFunc::getAppDir("board") . '/modules/public/' . $module_to_load . '/';
    if (file_exists($moduledir . $module_to_load . '.php')) {
      require_once $moduledir . $module_to_load . '.php';
    }

    // Some routine checks...
    $className = "public_board_" . $module_to_load . "_" . $module_to_load;
    if (class_exists($className)) {
      $module_class = new ReflectionClass($className);
      if ($module_class->isSubClassOf(new ReflectionClass('kxCmd'))) {
        $this->_boardClass = $module_class->newInstance($this->environment);
        $this->_boardClass->execute($this->environment);
      } else {
        $this->errorMessage = sprintf("Couldn't find module %s", $className);
        return false;
      }
    }

    $this->_boardClass->regeneratePages();
    $this->_boardClass->regenerateThreads();
    return true;
  }

  private function _board()
  {
    // DATABASE DRIVERS, DATABASE DRIVERS NEVER CHANGE
    // EXCEPT WHEN SAZ FUCKS WITH THEM
    $array_o_boards = $this->db->select("boards")
      ->fields('boards', array('board_name', 'board_desc'))
      ->orderBy("board_name")
      ->execute()
      ->fetchAll();
    $this->twigData['entries'] = array();
    foreach ($array_o_boards as $board) {
      $this->twigData['entries'][$board->board_name] = $board->board_desc;
    }

    $board_types_query = $this->db->select("modules")
      ->fields("modules", ["module_file", "module_name"])
      ->condition("module_application", "board")
      ->condition("module_manage", 0)
      ->execute()
      ->fetchAll();
    foreach ($board_types_query as $type) {
      $this->twigData['board_types'][$type->module_name] = [
        'value' => $type->module_file,
      ];
    }
    $this->twigData['board_types'];

    kxTemplate::output("manage/board", $this->twigData);
  }

  private function _post()
  {
    // Handles adding board
    kxForm::addRule('name', 'required')
      ->addRule('description', 'required')
      ->addRule('start', 'numeric')
      ->check();
    $fields = array(
      'board_name' => $this->request['name'],
      'board_desc' => $this->request['description'],
      'board_start' => intval($this->request['start']),
      'board_created_on' => time(),
      'board_header_image' => '',
      'board_include_header' => '',
      'board_type' => $this->request['board_type'],
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
      $this->twigData['notice']['message'] = _gettext('Board successfully added.');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf('Created board /%s/', $fields['board_name']),
        __CLASS__
      );
    } else {
      // Edit board
      $this->db->update("boards")
        ->fields($fields)
        ->condition("board_id", $this->request['edit'])
        ->execute();
      $this->twigData['notice']['message'] = _gettext('Board successfully edited.');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf('Edited board /%s/', $fields['board_name']),
        __CLASS__
      );
    }
    $this->twigData['notice']['type'] = 'success';
  }

  private function _del()
  {
    $this->db->delete("boards")
      ->condition("board_name", $this->request['board'])
      ->execute();
    $this->twigData['notice']['type'] = 'success';
    $this->twigData['notice']['message'] = _gettext('Board successfully deleted.');
    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf('Deleted board /%s/', $this->request['board']),
      __CLASS__
    );
  }
}
