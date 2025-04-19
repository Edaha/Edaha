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
          $this->twigData['notice']['message'] = sprintf(_('Board %s successfully regenerated!'), $this->request['board']);
        } else {
          $this->twigData['notice']['type'] = 'error';
          $this->twigData['notice']['message'] = _('Board failed to regenerate') . ": " . $this->errorMessage;
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
    $board = $this->entityManager->getRepository('\Edaha\Entities\Board')->findOneBy(['directory' => $this->request['board']]);
    if (is_null($board)) {
      $this->errorMessage = sprintf(_("Couldn't find board /%s/."), $this->request['board']);
      return false;
    }

    $board_modules = $this->db->select("modules")
      ->fields("modules", array("module_file"))
      ->condition("module_application", "board")
      ->condition("module_manage", 0)
      ->execute()
      ->fetchCol();
    foreach ($board_modules as $module) {
      if ($module == $board->board_type) {
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
    $boards_doctrine = $this->entityManager->getRepository('\Edaha\Entities\Board')->getAllBoards();

    $this->twigData['boards'] = $boards_doctrine;

    $board_types_query = $this->db->select("modules")
      ->fields("modules", ["module_file", "module_name"])
      ->condition("module_application", "board")
      ->condition("module_manage", 0)
      ->execute()
      ->fetchAll();

    $this->twigData['board_types'] = array();
    foreach ($board_types_query as $type) {
      $this->twigData['board_types'][$type->module_name] = [
        'value' => $type->module_file,
      ];
    }

    kxTemplate::output("manage/board", $this->twigData);
  }

  private function _post()
  {
    // Handles adding board
    kxForm::addRule('name', 'required')
      ->addRule('description', 'required')
      ->addRule('start', 'numeric')
      ->check();

    // Begin Doctrine implementation
    $board = $this->entityManager->getRepository('\Edaha\Entities\Board')->findOneBy(['directory' => $this->request['name']]);

    if ($board && $this->request['edit'] == "") {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = sprintf(_('Board /%s/ already exists.'), $this->request['name']);
      return;
    } elseif (is_null($board) && $this->request['edit'] == "") {
      $board = new Edaha\Entities\Board($this->request['description'], $this->request['name']);
      $board->setOption('type', $this->request['board_type']);
      $board->setOption('post_id_start_at', $this->request['start'] || 1);
      $this->entityManager->persist($board);
      $this->entityManager->flush();
    }

    if ($this->request['edit'] == "") {
      $this->twigData['notice']['message'] = _('Board successfully added.');
      logging::addLogEntry(
        kxFunc::getManageUser()['user_name'],
        sprintf('Created board /%s/', $fields['board_name']),
        __CLASS__
      );
    } else {
      // Edit board
      $this->twigData['notice']['message'] = _('Board successfully edited.');
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
    $this->twigData['notice']['message'] = _('Board successfully deleted.');
    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf('Deleted board /%s/', $this->request['board']),
      __CLASS__
    );
  }
}
