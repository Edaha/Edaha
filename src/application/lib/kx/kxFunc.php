<?php

namespace kx;

// Static functions that don't fit anywhere else

class kxFunc
{
    /**
     * Returns only alphanumeric characters.
     *
     * @param string $txt   The string to clean up
     * @param string $extra Additional characters to exclude
     */
    public static function alphanum(string $txt, string $extra = ''): string
    {
        if ($extra) {
            $extra = preg_quote($extra, '/');
        }

        return preg_replace('/[^a-zA-Z0-9\-\_'.$extra.']/', '', $txt);
    }

    /**
     * Generates a path for an application, with module if applicable.
     *
     * @return bool|string Directory to app or module (or false if error)
     */
    public static function getAppDir(string $app, string $module = ''): bool|string
    {
        if (empty($app) || !is_string($app)) {
            return false;
        }

        $appFolder = KX_ROOT.'/application/'.$app;
        $modulesFolder = (defined('IN_MANAGE') && IN_MANAGE) ? 'manage' : 'public';

        if ($module) {
            return $appFolder.'/'.$modulesFolder.'/'.$module;
        }

        return $appFolder;
    }

    /**
     * Perform a redirect using either a meta refresh or a direct header.
     */
    public static function doRedirect(string $url, bool $ispost = false, string $file = ''): void
    {
        $headermethod = true;

        if ($headermethod) {
            if ($ispost) {
                header('Location: '.$url);
            } else {
                exit('<meta http-equiv="refresh" content="1;url='.$url.'">');
            }
        } else {
            if ($ispost && '' != $file) {
                echo sprintf(_('%s uploaded.'), $file).' '._('Updating pages.');
            } elseif ($ispost) {
                echo _('Post added.').' '._('Updating pages.'); // TEE COME BACK
            } else {
                echo '---> ---> --->';
            }

            exit('<meta http-equiv="refresh" content="1;url='.$url.'">');
        }
    }

    /**
     * Display an error page and kill the script.
     */
    public static function showError(string $errormsg, string $extended = ''): never
    {
        $twigData['styles'] = explode(':', kxEnv::Get('kx:css:sitestyles'));
        $twigData['errormsg'] = $errormsg;

        if ('' != $extended) {
            $twigData['errormsgext'] = '<br /><div style="text-align: center;font-size: 1.25em;">'.$extended.'</div>';
        }

        kxTemplate::output('error', $twigData);

        exit;
    }

    /**
     * Check if the supplied md5 file hash is currently recorded inside of the database, attached to a non-deleted post.
     *
     * @param mixed $md5
     * @param mixed $boardid
     */
    public static function checkMD5($md5, $boardid)
    {
        // $matches = kxDB::getinstance()->select("posts");
        // $matches->innerJoin("post_files", "", "file_post = post_id AND file_board = board_id");
        // $matches = $matches->fields("posts", array("post_id", "parent_post_id"))
        //   ->condition("board_id", $boardid)
        //   ->condition("is_deleted", 0)
        //   ->condition("file_md5", $md5)
        //   ->range(0, 1)
        //   ->execute()
        //   ->fetchAll();
        $matches = [];
        if (count($matches) > 0) {
            $real_parentid = (0 == $matches[0]->parent_post_id) ? $matches[0]->post_id : $matches[0]->parent_post_id;

            return [$real_parentid, $matches[0]->post_id];
        }

        return false;
    }

    public static function encryptMD5($plain_text, $password, $iv_len = 16)
    {
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16) {
            $plain_text .= str_repeat("\0", 16 - ($n % 16));
        }

        $i = 0;
        $enc_text = self::get_rnd_iv($iv_len);
        $iv = substr($password ^ $enc_text, 0, 512);
        while ($i < $n) {
            $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
            $enc_text .= $block;
            $iv = substr($block.$iv, 0, 512) ^ $password;
            $i += 16;
        }

        return base64_encode($enc_text);
    }

    public static function decryptMD5($enc_text, $password, $iv_len = 16)
    {
        $enc_text = base64_decode($enc_text);
        $n = strlen($enc_text);
        $i = $iv_len;
        $plain_text = '';
        $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
        while ($i < $n) {
            $block = substr($enc_text, $i, 16);
            $plain_text .= $block ^ pack('H*', md5($iv));
            $iv = substr($block.$iv, 0, 512) ^ $password;
            $i += 16;
        }

        return preg_replace('/\x13\x00*$/', '', $plain_text);
    }

    /**
     * Calculate the number of pages which will be needed for the supplied number of posts.
     *
     * @param int $boardtype Board type
     * @param int $numposts  Number of posts
     *
     * @return int Number of pages required
     */
    public static function pageCount(int $boardtype, int $numposts): int
    {
        if (1 == $boardtype) {
            return floor($numposts / kxEnv::Get('kx:display:txtthreads'));
        }
        if (3 == $boardtype) {
            return floor($numposts / 30);
        }

        return floor($numposts / kxEnv::Get('kx:display:imgthreads'));
    }

    /**
     * Gets information about the filetype provided, which is specified in the manage panel.
     *
     * @param string $filetype Filetype
     *
     * @return array Filetype image, width, and height
     */
    public static function getFileTypeInfo(string $filetype): array
    {
        // $results = kxDB::getinstance()->select("filetypes")
        //   ->fields("filetypes", array("type_image", "type_image_width", "type_image_height"))
        //   ->condition("type_ext", $filetype)
        //   ->range(0, 1)
        //   ->execute()
        //   ->fetchAll();
        $results = [];
        if (count($results) > 0) {
            foreach ($results as $line) {
                return [$line->type_image, $line->type_image_width, $line->type_image_height];
            }
        } else {
            // No info was found, return the generic icon
            return ['generic.png', 48, 48];
        }
    }

    #[MigrateToTwig]
    /**
     * Formats a timestamp into a readable date representation, hyperlinking the email if needed.
     *
     * This belongs completely in Twig and templates
     *
     * @param mixed $timestamp
     * @param mixed $type
     * @param mixed $locale
     * @param mixed $email
     */
    public static function formatDate($timestamp, $type = 'post', $locale = 'en', $email = ''): string
    {
        $output = '';
        if ('' != $email) {
            $output .= '<a href="mailto:'.$email.'">';
        }

        if ('post' == $type) {
            if ('ja' == $locale) {
                // Format the timestamp japanese style
                $fulldate = strftime('%Yy%mm%dd(DAYOFWEEK) %HH%MM%SS', $timestamp);
                $dayofweek = strftime('%a', $timestamp);

                // I don't like this method, but I can't rely on PHP's locale settings to do it for me...
                switch ($dayofweek) {
                    case 'Sun':
                        $dayofweek = '&#26085;';

                        break;

                    case 'Mon':
                        $dayofweek = '&#26376;';

                        break;

                    case 'Tue':
                        $dayofweek = '&#28779;';

                        break;

                    case 'Wed':
                        $dayofweek = '&#27700;';

                        break;

                    case 'Thu':
                        $dayofweek = '&#26408;';

                        break;

                    case 'Fri':
                        $dayofweek = '&#37329;';

                        break;

                    case 'Sat':
                        $dayofweek = '&#22303;';

                        break;

                    default:
                        // The date must be in the correct language already, so let's convert it to unicode if it isn't already.
                        $dayofweek = mb_convert_encoding($dayofweek, 'UTF-8', 'JIS, eucjp-win, sjis-win');

                        break;
                }
                $fulldate = self::formatJapaneseNumbers($fulldate);
                // Convert the symbols for year, month, etc to unicode equivalents. We couldn't do this above beause the numbers would be formatted to japanese.
                $fulldate = str_replace(['y', 'm', 'd', 'H', 'M', 'S'], ['&#24180;', '&#26376;', '&#26085;', '&#26178;', '&#20998;', '&#31186;'], $fulldate);
                $fulldate = str_replace('DAYOFWEEK', $dayofweek, $fulldate);

                return $output.$fulldate.(('' != $email) ? ('</a>') : (''));
            }

            // Format the timestamp english style
            return $output.$timestamp.(('' != $email) ? ('</a>') : (''));
        }

        return $output.date('y/m/d(D)H:i', $timestamp).(('' != $email) ? ('</a>') : (''));
    }

    /**
     * Replaces regular ASCII numbers with fullwidth representations.
     */
    public static function formatJapaneseNumbers(string $input): array|string|null
    {
        $patterns = ['/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/', '/8/', '/9/', '/0/'];
        $replace = ['１', '２', '３', '４', '５', '６', '７', '８', '９', '０'];

        return preg_replace($patterns, $replace, $input);
    }

    /**
     * print $contents to $filename by using a temporary file and renaming it.
     *
     * <3 coda for this wonderful snippet
     *
     * @param string $output_path The file to write
     * @param string $contents    The contents of the file to write
     * @param string $board       The board we're writing for
     */
    public static function outputToFile(string $output_path, string $contents, string $board): void
    {
        $tempfile = tempnam(KX_BOARD.'/'.$board.'/res', 'tmp'); // Create the temporary file
        $fp = fopen($tempfile, 'w');
        fwrite($fp, $contents);
        fclose($fp);
        // If we aren't able to use the rename function, try the alternate method
        if (!@rename($tempfile, $output_path)) {
            copy($tempfile, $output_path);
            unlink($tempfile);
        }
        chmod($output_path, 0o664); // it was created 0600
    }

    /**
     * Checks if we have a valid Session.
     */
    public static function getManageSession(): bool
    {
        // So far so good, let's check it
        $session_data = kxOrm::getEntityManager()->getRepository('\Edaha\Entities\UserSession')->findOneBy([
            'sid' => kxEnv::getInstance()->request->get('sid'),
        ]);

        if (empty($session_data)) {
            // No session found
            return false;
        }
        // Alright! Looks good so far. let's do some triple and quadruple checking though.

        // Now, we'll check the IP address to see if it matches the stored one.
        // $first_ip = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3", $session_data[0]->session_ip);
        // $second_ip = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3", $_SERVER['REMOTE_ADDR']);

        // if ($first_ip != $second_ip) {
        //   // Man you just can't win today can you?
        //   return false;
        // }
        // Okay, last one I promise. Is our session expired?
        // if ($session_data[0]->session_last_action < (time() - 60 * 60)) {
        //   // Argh!!
        //   return false;
        // }

        // Congratulations!
        return true;
    }

    /**
     * Get the current manage user's ID and username.
     */
    public static function getManageUser(): ?array
    {
        if (kxFunc::getManageSession()) {
            $session_data = kxOrm::getEntityManager()->getRepository('\Edaha\Entities\UserSession')->findOneBy([
                'sid' => kxEnv::getInstance()->request->get('sid'),
            ]);

            return [
                'user_name' => $session_data->user->username,
            ];
        }

        return [];
    }

    /**
     * Returns a filesize formatted to the largest whole unit.
     *
     * Examples:
     *   ConvertBytes(1) => "1B"
     *   ConvertBytes(1024) => "1KB"
     *   ConvertBytes(1536) => "1.5KB"
     */
    public static function ConvertBytes(int $bytes): string
    {
        // Thanks to an anonymous user for this
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0); // cleanup to make sure the value is an integer
        $exponent = floor(($bytes ? log($bytes) : 0) / log(1024)); // determine the offset (in powers of 1024) that is required to fit the given byte value
        $exponent = min($exponent, count($units) - 1); // clamp it so it doesn't exceed the maximum identifier in our unit array

        $bytes /= pow(1024, $exponent); // divide our number of bytes by the power granted by our exponent (since our number was >= our power, this gives a value with fractions such as 145.49572, of that unit)

        return round($bytes, 2).$units[$exponent]; // return the rounded fraction (with 2 decimals) and what unit it relates to
    }

    /**
     * Returns an array of arrays, where all boards are grouped by their Section.
     *
     * This probably will go away and be replaced with a BoardRepository method
     *
     * @return array right now only returns an empty array
     */
    public static function fullBoardList(): array
    {
        // $sections = kxDB::getInstance()->select("sections")
        //   ->fields("sections")
        //   ->orderBy("section_order")
        //   ->execute()
        //   ->fetchAll();

        // $boards = kxDB::getInstance()->select("boards")
        //   ->fields("boards", array('board_id', 'board_desc'))
        //   ->where("board_section = ?")
        //   ->orderBy("board_order")
        //   ->build();

        // // Add boards to an array within their section
        // foreach ($sections as &$section) {
        //   $boards->execute(array($section->id));
        //   $section->boards = $boards->fetchAll();
        // }

        // // Prepend boards with no section
        // $boards->execute(array(0));
        // return (array_merge($boards->fetchAll(), $sections));
        return [];
    }

    /**
     * Returns an array of arrays similar to fullBoardList, but this time only boards marked as visible.
     *
     * @return array Right now only an empty array
     */
    public static function visibleBoardList(): array
    {
        // $sections = kxDB::getInstance()->select("sections")
        //   ->fields("sections")
        //   ->orderBy("section_order")
        //   ->execute()
        //   ->fetchAll();

        // $boards = kxDB::getInstance()->select("boards")
        //   ->fields("boards", array('board_id', 'board_desc', 'board_name'))
        //   ->where("board_section = ?")
        //   ->orderBy("board_order")
        //   ->build();

        // // Add boards to an array within their section
        // foreach ($sections as &$section) {
        //   $boards->execute(array($section->id));
        //   $section->boards = $boards->fetchAll();
        // }
        return [];
    }

    private static function get_rnd_iv($iv_len)
    {
        $iv = '';
        while ($iv_len-- > 0) {
            $iv .= chr(mt_rand() & 0xFF);
        }

        return $iv;
    }
}
