<?php

use Edaha\Entities\Board;
use Edaha\Interfaces\RegeneratorInterface;

class manage_board_board_board extends kxCmd
{
    public $errorMessage = '';

    /**
     * Arguments eventually being sent to twig.
     *
     * @var array()
     */
    protected $twigData;

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
                    $this->twigData['notice']['message'] = sprintf(_('Board %s successfully regenerated!'), $this->request['board_id']);
                } else {
                    $this->twigData['notice']['type'] = 'error';
                    $this->twigData['notice']['message'] = _('Board failed to regenerate').': '.$this->errorMessage;
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
        $board = $this->entityManager->find(Board::class, $this->request['board_id']);
        if (is_null($board)) {
            $this->errorMessage = sprintf(_("Couldn't find board /%s/."), $this->request['board']);

            return false;
        }

        $regenerator = new $board->renderer($board, $this->entityManager);
        if (!$regenerator instanceof RegeneratorInterface) {
            $this->errorMessage = sprintf(_("Couldn't find board renderer for /%s/."), $board->directory);

            return false;
        }

        $regenerator->regenerateAllPages();
        $regenerator->regenerateAllThreads();

        return true;
    }

    private function _board()
    {
        $boards_doctrine = $this->entityManager->getRepository('\Edaha\Entities\Board')->getAllBoards();

        $this->twigData['boards'] = $boards_doctrine;

        $board_types = $this->entityManager->getRepository('\Edaha\Entities\Module')->getBoardModules();
        if (empty($board_types)) {
            $this->twigData['notice']['type'] = 'error';
            $this->twigData['notice']['message'] = _('No board types found. Please install board modules.');

            return;
        }
        $this->twigData['board_types'] = [];
        foreach ($board_types as $type) {
            $this->twigData['board_types'][$type->name] = [
                'value' => $type->class,
            ];
        }

        kxTemplate::output('manage/board', $this->twigData);
    }

    private function _post()
    {
        // Handles adding board
        kxForm::addRule('name', 'required')
            ->addRule('description', 'required')
            ->addRule('start', 'numeric')
            ->check()
        ;

        // Begin Doctrine implementation
        $board = $this->entityManager->getRepository('\Edaha\Entities\Board')->findOneBy(['directory' => $this->request['name']]);

        if ($board && (!isset($this->request['edit']) || '' == $this->request['edit'])) {
            $this->twigData['notice']['type'] = 'error';
            $this->twigData['notice']['message'] = sprintf(_('Board /%s/ already exists.'), $this->request['name']);

            return;
        }
        if (is_null($board) && '' == $this->request['edit']) {
            $board = new Board($this->request['description'], $this->request['name']);
            $board->type = $this->request['board_type'];
            $board->post_id_start_at = $this->request['start'] || 1;
            $this->entityManager->persist($board);
            $this->entityManager->flush();
        }

        if (!isset($this->request['edit']) || '' == $this->request['edit']) {
            $this->twigData['notice']['message'] = _('Board successfully added.');
            logging::addLogEntry(
                kxFunc::getManageUser()['user_name'],
                sprintf('Created board /%s/', $board->directory),
                __CLASS__
            );
        } else {
            // Edit board
            $this->twigData['notice']['message'] = _('Board successfully edited.');
            logging::addLogEntry(
                kxFunc::getManageUser()['user_name'],
                sprintf('Edited board /%s/', $board->directory),
                __CLASS__
            );
        }
        $this->twigData['notice']['type'] = 'success';
    }

    private function _del()
    {
        $board = $this->entityManager->getRepository('\Edaha\Entities\Board')->findOneBy(['directory' => $this->request['name']]);
        if ($board) {
            $this->entityManager->remove($board);
            $this->entityManager->flush();
            $this->twigData['notice']['type'] = 'success';
            $this->twigData['notice']['message'] = _('Board successfully deleted.');
            logging::addLogEntry(
                kxFunc::getManageUser()['user_name'],
                sprintf('Deleted board /%s/', $this->request['board']),
                __CLASS__
            );
        } else {
            $this->twigData['notice']['type'] = 'error';
            $this->twigData['notice']['message'] = _('Unknown board.');
        }
    }
}
