<?php

use kx\kxBans;
use kx\kxCmd\kxCmd;
use kx\kxEnv;
use kx\kxFunc;
use kx\kxTemplate;

/*
 * Bans module
 * Last Updated: $Date: $

 * @author    $Author: $

 * @package   kusaba

 * @version   $Revision: $
 *
 */
class manage_core_bans_bans extends kxCmd
{
    /**
     * Arguments eventually being sent to twig.
     *
     * @var array()
     */
    protected $twigData;

    public function exec(kxEnv $environment)
    {
        switch ((isset($_GET['do'])) ? $_GET['do'] : '') {
            case 'view':
                $this->_viewBans();

                break;

            default:
                $this->_addBan();

                break;
        }
    }

    private function _viewBans()
    {
        $bans = $this->entityManager->getRepository('Edaha\Entities\Ban')->getAllBans();
        $this->twigData['bans'] = $bans;
        kxTemplate::output('manage/bans_view', $this->twigData);
    }

    private function _addBan()
    {
        if ('post' == $this->request['action']) {
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
