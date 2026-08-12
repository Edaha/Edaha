<?php

use kx\kxCmd\kxCmd;
use kx\kxEnv;
use kx\kxTemplate;

class public_core_index_menu extends kxCmd
{
    public function exec(kxEnv $environment)
    {
        switch ($this->request['do']) {
            case 'get':
            default:
                $this->generateMenu();

                break;

            case 'print':
                $this->printMenu();

                break;
        }
    }

    public function generateMenu()
    {
        return $this->_getMenu(true);
    }

    public function printMenu()
    {
        return $this->_getMenu(false, $_COOKIE['tcshowdirs']);
    }

    private function _getMenu($savetofile = false, $option = false)
    {
        // $twigData['boardpath'] = getCLBoardPath();

        $twigData['styles'] = explode(':', kxEnv::Get('kx:css:sitestyles'));

        if ($savetofile) {
            $file = 'menu.html';
        } else {
            $file = 'menu.php';
        }

        $twigData['file'] = $file;

        $sections = [];
        // $boardsExist = $this->db->select("boards")
        //                         ->fields("boards")
        //                         ->countQuery()
        //                         ->execute()
        //                         ->fetchField();
        $boardsExists = false;
        if ($boardsExist) {
            // $sections = $this->db->select("sections")
            //                      ->fields("sections")
            //                      ->orderBy("section_order")
            //                      ->execute()
            //                      ->fetchAll();
            // $results = $this->db->select("boards")
            //                     ->fields("boards", array("board_order", "board_name", "board_desc", "board_locked", "board_trial", "board_popular"))
            //                     ->where("section = ?")
            //                     ->orderBy("board_order")
            //                     ->orderBy("board_name")
            //                     ->build();
            // foreach($sections AS $key=>$section) {
            // 	$results->execute(array($section['id']));
            // 	$boards = $results->fetchAll();
            //   $sections[$key]['boards'] = $boards;
            // }
        }
        $twigData['boards'] = $sections;

        if ($savetofile) {
            file_put_contents(KX_ROOT.'/menu.html', kxTemplate::get('menu', $twigData));

            return true;
        }

        return kxTemplate::get('menu', $twigData);
    }
}
