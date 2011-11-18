<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Section for building the main menu
 * Last Updated: $Date$
 
 * @author 		$Author$

 * @package		kusaba
 * @subpackage	core

 * @version		$Revision$
 *
 */
 
if (!defined('KUSABA_RUNNING'))
{
  print "<h1>Access denied</h1>You cannot access this file directly.";
  die();
}

class public_core_index_menu extends kxCmd {

  public function exec( kxEnv $environment ) {
 
    switch( $this->request['do'] )
    {
      case 'get':
      default:
        $this->generateMenu();
      break;
      case 'print':
        $this->printMenu();
      break;
    }
  }
  
	private function _getMenu($savetofile = false, $option = false) {

		//$twigData['boardpath'] = getCLBoardPath();

		$twigData['styles'] = explode(':', kxEnv::Get('kx:css:menustyles'));

		if ($savetofile) {
			$file = 'menu.html';
		} else {
			$file = 'menu.php';
		}

		$twigData['file'] = $file;

		$sections = Array();
    $boardsExist = $this->db->select("boards")
                            ->fields("boards")
                            ->countQuery()
                            ->execute()
                            ->fetchField();
		if ($boardsExist) {
      $sections = $this->db->select("sections")
                           ->fields("sections")
                           ->orderBy("section_order")
                           ->execute()
                           ->fetchAll();
      $results = $this->db->select("boards")
                          ->fields("boards", array("board_order", "board_name", "board_desc", "board_locked", "board_trial", "board_popular"))
                          ->where("section = ?")
                          ->orderBy("board_order")
                          ->orderBy("board_name")
                          ->build();
			foreach($sections AS $key=>$section) {
				$results->execute(array($section['id']));
				$boards = $results->fetchAll();
        $sections[$key]['boards'] = $boards;
			}
		}
		$twigData['boards'] = $sections;

			if ($savetofile) {
        file_put_contents(KX_ROOT . '/menu.html', kxTemplate::get('menu', $twigData));
        return true;
			} else {
					return kxTemplate::get('menu', $twigData);
			}

	}

	public function generateMenu() {
		return $this->_getMenu(true);
	}

	public function printMenu() {
    return $this->_getMenu(false, $_COOKIE['tcshowdirs']);
	}
}