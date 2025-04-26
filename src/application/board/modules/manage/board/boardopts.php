<?php

class manage_board_board_boardopts extends kxCmd
{
  /**
   * Arguments eventually being sent to twig
   *
   * @var Array()
   */
  protected $twigData;

  public function exec(kxEnv $environment)
  {
    switch ($this->request['do']) {
      case 'edit':
        $this->_edit();
        break;

      case 'post':
        $this->_update();
        break;

      default:
        //there's a problem
        die();
        break;
    }

    kxTemplate::output("manage/boardopts", $this->twigData);
  }

  private function _edit()
  {
    // $board_options = kxEnv::Get('cache:boardopts:' . $this->request['board']);
    $board = $this->entityManager->find('\Edaha\Entities\Board', $this->request['id']);
    if (is_null($board)) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = sprintf(_("Couldn't find board /%s/."), $this->request['board']);
      return;
    }

    $board->locale;

    $this->twigData['board'] = $board;
    
    foreach ($board->options as $option) {
      $this->twigData['options'][$option->name] = $option->value;
    }

    $this->twigData['filetypes'] = kxEnv::get('cache:attachments:filetypes');
    
    $this->twigData['board_types'] = $this->db->select("modules")
      ->fields("modules", ["module_file", "module_name"])
      ->condition("module_application", "board")
      ->condition("module_manage", 0)
      ->execute()
      ->fetchAll();
  }

  private function _update()
  {
    // A few checks to ensure a valid submission
    kxForm::addRule('id', 'numeric')
      ->addRule('board', 'required')
      ->check();

    $board = $this->entityManager->find('\Edaha\Entities\Board', $this->request['id']);

    if (is_null($board)) {
      $this->twigData['notice']['type'] = 'error';
      $this->twigData['notice']['message'] = sprintf(_("Couldn't find board /%s/."), $this->request['board']);
      return;
    }

    $board_fields = array(
      'name' => $this->request['title'],
      'locale' => $this->request['locale'],
      'type' => $this->request['type'],
      'upload_type' => (int) $this->request['upload_type'],
      'board_section' => (int) $this->request['board_section'],
      'order' => (int) $this->request['order'],
      'header_image' => $this->request['header_image'],
      'include_header' => $this->request['include_header'],
      'anonymous' => $this->request['anonymous'],
      // TODO: Add this to the template
      'default_style' => 'edaha',
      'allowed_embeds' => '',
      'max_upload_size' => (int) $this->request['max_upload_size'],
      'max_message_length' => (int) $this->request['max_message_length'],
      'max_pages' => (int) $this->request['max_pages'],
      'max_age' => (int) $this->request['max_age'],
      'mark_page' => (int) $this->request['mark_page'],
      'max_replies' => (int) $this->request['max_replies'],
      'locked' => (int) isset($this->request['locked']),
      'show_id' => (int) isset($this->request['show_id']),
      'compact_list' => (int) isset($this->request['compact_list']),
      'reporting' => (int) isset($this->request['reporting']),
      'captcha' => (int) isset($this->request['captcha']),
      'archiving' => (int) isset($this->request['archiving']),
      'catalog' => (int) isset($this->request['catalog']),
      'no_file' => (int) isset($this->request['no_file']),
      'redirect_to_thread' => (int) isset($this->request['redirect_to_thread']),
      'forced_anon' => (int) isset($this->request['forced_anon']),
      'trial' => (int) isset($this->request['trial']),
      'popular' => (int) isset($this->request['popular']),
      'max_files' => (int) $this->request['max_files'],
    );

    foreach ($board_fields as $key => $value) {
      if ($value != $board->$key) {
        $board->$key = $value;
      }
    }

    $this->entityManager->persist($board);
    $this->entityManager->flush();

    // TODO: Filetypes (Attachments) object
    // Clear previous filetype settings
    // Add new filetypes

    $this->twigData['boardredirect'] = true;
    $this->twigData['notice']['type'] = 'success';
    $this->twigData['notice']['message'] = _('Board updated. Redirecting...');
    logging::addLogEntry(
      kxFunc::getManageUser()['user_name'],
      sprintf('Edited board /%s/',  $this->request['board']),
      __CLASS__
    );
    // TODO Update the cache
    // $this->recacheBoardOptions();
  }

  public function recacheBoardOptions()
  {
    // Get the requested board's options
    $recache_board_options = $this->db->select("boards")
      ->fields("boards")
      ->condition("board_name", $this->request['board'])
      ->execute()
      ->fetchAll();
    // Get its associated filetypes
    $recache_board_options[0]->board_filetypes = $this->db->select("board_filetypes")
      ->fields("board_filetypes", array('type_id'))
      ->condition("board_id", $recache_board_options[0]->board_id)
      ->execute()
      ->fetchCol();
    /*echo 'recache:<br><pre>';
    print_r($recache_board_options);
    echo '</pre>';
    die();*/
    // And cache them
    kxEnv::set('cache:boardopts:' . $this->request['board'], $recache_board_options);
  }
}
