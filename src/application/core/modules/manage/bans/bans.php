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
 * Bans module
 * Last Updated: $Date: $
 
 * @author    $Author: $
 
 * @package   kusaba
 
 * @version   $Revision: $
 *
 */
class manage_core_bans_bans extends kxCmd {
  /**
   * Arguments eventually being sent to twig
   * 
   * @var Array()
   */
  protected $twigData;
  
  
  public function exec( kxEnv $environment ) {
    switch ( (isset($_GET['do'])) ? $_GET['do'] : '' ) {
      case 'view':
        $this->_viewBans();
        break;
      
      default:
        $this->_addBan();
        break;
      
    }
  }
  
  private function _viewBans() {
    $bans = $this->entityManager->getRepository('Edaha\Entities\Ban')->getAllBans();
    $this->twigData['bans'] = $bans;
    kxTemplate::output('manage/bans_view', $this->twigData);
  }
  
  private function _addBan() {
    if ($this->request['action'] == 'post') {
      // Ban the user
      // TODO Form validation
      kxBans::BanUser(
        $this->request['ban_ip'],
        $this->request['ban_boards'],
        (int) $this->request['ban_duration'],
        $this->request['ban_reason'],
        (int) isset($this->request['ban_allowread']),
        (int) isset($this->request['ban_allow_appeal']),
        $this->request['ban_notes'],
        kxFunc::getManageUser()['user_id'],
        (int) isset($this->request['ban_deleteall']),
      );

    }
    // TODO: Complete this

    $this->twigData['sections'] = kxFunc::fullBoardList();

    // logging::addLogEntry(
    //   kxFunc::getManageUser()['user_name'],
    //   sprintf('Banned IP %s', ip_address),
    //   __CLASS__
    // );
    
    kxTemplate::output('manage/bans_add', $this->twigData);
  }
  
}

?>