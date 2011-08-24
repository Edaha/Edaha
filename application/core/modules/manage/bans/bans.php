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
  
  public function exec( kxEnv $environment ) {
    $this->twigData = array();
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
    // TODO: Add query,   
    $this->twigData['bans'] = $this->db->select("banlist")
                                       ->fields("banlist")
                                       ->orderBy("ban_created", "DESC")
                                       ->range(0,20)
                                       ->execute()
                                       ->fetchAll();
                                       
   kxTemplate::output('manage/bans_view', $this->twigData);
  }
  
  private function _addBan() {
    // TODO: Complete this

    $this->twigData['sections'] = kxFunc::fullBoardList();
    
    kxTemplate::output('manage/bans_add', $this->twigData);
  }
  
}

?>