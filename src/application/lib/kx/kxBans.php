<?php

namespace kx;

use Edaha\Entities\Ban;

/**
 * Class for handling bans and ban function, but probably should be consolidated with the Edaha Bans object.
 */
class kxBans
{
    /**
     * Check if an IP is banned and display the Banned page if so.
     */
    public static function BanCheck(string $ip, string $board = ''): void
    {
        $em = kxOrm::getEntityManager();

        if (!isset($_COOKIE['tc_previousip'])) {
            $_COOKIE['tc_previousip'] = '';
        }

        $bans = $em->getRepository('Edaha\Entities\Ban')->getActiveBansForIp($ip);

        $relevant_bans = [];
        foreach ($bans as $ban) {
            if ($ban->is_global) {
                $relevant_bans[] = $ban;
            } elseif (isset($board) && $ban->isBannedFromBoard($board)) {
                $relevant_bans[] = $ban;
            }
        }

        if (count($relevant_bans) > 0) {
            echo self::DisplayBannedMessage($bans);

            exit;
        }
    }

    /**
     * Ban a user from a board (or boards) and optionally delete every post of theirs.
     *
     * @param int $duration     In secods
     * @param int $allow_read   true lets them read the board but not post, False prevents them from reading and posting
     * @param int $allow_appeal true lets them submit an appeal from the ban page
     */
    public static function BanUser(string $ip, array $board_ids, int $duration, string $reason, int $allow_read, int $allow_appeal, string $notes, int $staff_id, int $delete_all_posts = false): void
    {
        $em = kxOrm::getEntityManager();

        $ban = new Ban(
            ip: $ip,
            reason: $reason,
            allow_read: $allow_read,
            allow_appeal: $allow_appeal,
            expires_at: $expires_at = new \DateTime('now + '.$duration.' seconds'),
            staff_note: $staff_note = $notes,
        );

        foreach ($board_ids as $board_id) {
            $ban->banFromBoard($em->getRepository('Edaha\Entities\Board')->find($board_id));
        }

        $em->persist($ban);

        if ($delete_all_posts) {
            $posts = kxOrm::getEntityManager()->getRepository('Edaha\Entities\Post')->findBy(['ip' => $ip]);

            foreach ($posts as $post) {
                $post->delete();
                $em->remove($post);
            }
        }
    }

    /**
     * Apache-only, implements read bans at the .htaccess level.
     */
    public static function UpdateHtaccess(): void
    {
        $htaccess_contents = file_get_contents(KX_BOARD.'.htaccess');
        $htaccess_contents_preserve = substr($htaccess_contents, 0, strpos($htaccess_contents, '## !KU_BANS:') + 12)."\n";

        $htaccess_contents_bans_iplist = '';
        // $results = $kx_db->GetAll("SELECT `ip` FROM `" . kxEnv::Get('kx:db:prefix') . "banlist` WHERE `allowread` = 0 AND `type` = 0 AND (`expired` =  1) ORDER BY `ip` ASC");
        $results = [];
        if (count($results) > 0) {
            $htaccess_contents_bans_iplist .= 'RewriteCond %{REMOTE_ADDR} (';
            foreach ($results as $line) {
                $htaccess_contents_bans_iplist .= str_replace('.', '\.', md5_decrypt($line['ip'], kxEnv::Get('kx:misc:randomseed'))).'|';
            }
            $htaccess_contents_bans_iplist = substr($htaccess_contents_bans_iplist, 0, -1);
            $htaccess_contents_bans_iplist .= ')$'."\n";
        }
        if ('' != $htaccess_contents_bans_iplist) {
            $htaccess_contents_bans_start = "<IfModule mod_rewrite.c>\nRewriteEngine On\n";
            $htaccess_contents_bans_end = 'RewriteRule !^(banned.php|youarebanned.jpg|favicon.ico|css/site_futaba.css)$ '.kxEnv::Get('kx:paths:boards:folder')."banned.php [L]\n</IfModule>";
        } else {
            $htaccess_contents_bans_start = '';
            $htaccess_contents_bans_end = '';
        }
        $htaccess_contents_new = $htaccess_contents_preserve.$htaccess_contents_bans_start.$htaccess_contents_bans_iplist.$htaccess_contents_bans_end;
        file_put_contents(KX_BOARD.'.htaccess', $htaccess_contents_new);
    }

    /**
     * Return the page which will inform the user a quite unfortunate message.
     *
     * @param array  $bans  Array of Ban objects
     * @param string $board Unused lol
     */
    private static function DisplayBannedMessage(array $bans, string $board = '')
    {
        // Set a cookie with the users current IP address in case they use a proxy to attempt to make another post
        setcookie('tc_previousip', $_SERVER['REMOTE_ADDR'], time() + 604800, kxEnv::Get('kx:paths:boards:folder'));

        require_once KX_ROOT.'/lib/dwoo.php';

        kxTemplate::assign('bans', $bans);

        return $dwoo->get(KX_ROOT.kxEnv::Get('kx:templates:dir').'/banned.html.twig', $twigData);
    }
}
