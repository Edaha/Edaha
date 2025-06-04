<?php

use Edaha\Entities\Section;

class manage_board_board_section extends kxCmd
{
    /**
     * Arguments eventually being sent to twig
     *
     * @var Array()
     */
    protected $twigData;

    public $errorMessage = '';

    public function exec(kxEnv $environment)
    {
        switch ($this->request['do']) {
            case 'add':
                $this->_add();
                break;
            case 'edit':
                $this->_edit();
                break;
            case 'delete':
                $this->_delete();
                break;
            default:
                break;
        }

        $this->_board();
    }

    private function _board()
    {
        $this->twigData['sections'] = $this->entityManager->getRepository(Section::class)->findAll();

        kxTemplate::output("manage/section", $this->twigData);
    }

    private function _add()
    {
        $section = new Section($this->request['name'], (int) $this->request['hidden']);
        $this->entityManager->persist($section);
        $this->entityManager->flush();
    }
}
