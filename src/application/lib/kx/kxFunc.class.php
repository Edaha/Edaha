<?php

/*
 * Static functions that don't fit anywhere else
 */

class kxFunc
{
    /**
     * Cleans input.
     *
     * @param  array   Input data
     * @param  int           Iteration
     * @param mixed $data
     * @param mixed $i
     *
     * @return array Cleaned data
     */
    public static function cleaninput(&$data, $i = 0)
    {
        // Don't parse arrays deeper than 10, as it's most likely someone trying to crash PHP
        if ($i > 10) {
            return;
        }

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                self::cleaninput($data[$k], ++$i);
            } else {
                // Decimal places. Script kiddies might think they can try to access files outside the board
                $v = str_replace('&#46;&#46;/', '../', $v);

                // This litte bugger changes the formatting to be right-to-left, like on a hebrew locale.
                $v = str_replace('&#8238;', '', $v);

                // Null byte characters can mess with formatting as well, so we remove them
                $v = str_replace("\x00", '', $v);
                $v = str_replace(chr('0'), '', $v);
                $v = str_replace("\0", '', $v);

                $data[$k] = $v;
            }
        }
    }

    /**
     * Recursively cleans keys and values and
     * inserts them into the input array.
     *
     * @param  mixed    Input data
     * @param  array    Parsed data
     * @param  int    Iteration
     * @param mixed $data
     * @param mixed $input
     * @param mixed $i
     *
     * @return array Cleaned data
     */
    public static function parseinput(&$data, $input = [], $i = 0)
    {
        if ($i > 10) {
            return $input;
        }

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $input[$k] = self::parseinput($data[$k], [], $i++);
            } else {
                $k = self::cleanInputKey($k);
                $v = self::cleanInputVal($v, false);

                $input[$k] = $v;
            }
        }

        return $input;
    }

    /**
     * Clean up input key.
     *
     * @param  string    Key name
     * @param mixed $key
     *
     * @return string Cleaned key name
     */
    public static function cleanInputKey($key)
    {
        if ('' == $key) {
            return '';
        }

        $key = htmlspecialchars(urldecode($key));
        $key = str_replace('..', '', $key);
        $key = preg_replace('/\\_\\_(.+?)\\_\\_/', '', $key);

        return preg_replace('/^([\\w\\.\\-\\_]+)$/', '$1', $key);
    }

    /**
     * Clean up input data.
     *
     * @param  string    Input
     * @param mixed $txt
     *
     * @return string Cleaned Input
     */
    public static function cleanInputVal($txt)
    {
        if (empty($txt)) {
            return '';
        }

        $search = ['&#032;',
            "\r\n", "\n\r", "\r",
            '&',
            '<!--',
            '-->',
            '<',
            '>',
            "\n",
            '"',
            '<script',
            '$',
            '!',
            "'"];
        $replace = [' ',
            "\n", "\n", "\n",
            '&amp;',
            '&#60;&#33;--',
            '--&#62;',
            '&lt;',
            '&gt;',
            "<br />\n",
            '&quot;',
            '&#60;script',
            '&#036;',
            '&#33;',
            '&#39;'];
        $txt = str_replace($search, $replace, $txt);

        $txt = preg_replace('/&amp;#([0-9]+);/s', '&#\\1;', $txt);

        return preg_replace('/&#(\\d+?)([^\\d;])/i', '&#\\1;\\2', $txt);
    }

    /**
     * Returns only alphanumeric characters.
     *
     * @param  string    Input String
     * @param  string    Additional characters
     * @param mixed $txt
     * @param mixed $extra
     *
     * @return string Parsed string
     */
    public static function alphanum($txt, $extra = '')
    {
        if ($extra) {
            $extra = preg_quote($extra, '/');
        }

        return preg_replace('/[^a-zA-Z0-9\\-\\_'.$extra.']/', '', $txt);
    }

    /**
     * Generates a path for an application, with module if applicable.
     *
     * @param  string    application
     * @param  string    module (optional)
     * @param mixed $app
     * @param mixed $module
     *
     * @return mixed Directory to app or module (or false if error)
     */
    public static function getAppDir($app, $module = '')
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

    // Depending on the configuration, use either a meta refresh or a direct header
    public static function doRedirect($url, $ispost = false, $file = '')
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

    public static function showError($errormsg, $extended = '')
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
    public static function pageCount($boardtype, $numposts)
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
    public static function getFileTypeInfo($filetype)
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
    public static function formatDate($timestamp, $type = 'post', $locale = 'en', $email = '')
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

    public static function formatJapaneseNumbers($input)
    {
        $patterns = ['/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/', '/8/', '/9/', '/0/'];
        $replace = ['１', '２', '３', '４', '５', '６', '７', '８', '９', '０'];

        return preg_replace($patterns, $replace, $input);
    }

    /* <3 coda for this wonderful snippet
    print $contents to $filename by using a temporary file and renaming it */
    public static function outputToFile($filename, $contents, $board)
    {
        $tempfile = tempnam(KX_BOARD.'/'.$board.'/res', 'tmp'); // Create the temporary file
        $fp = fopen($tempfile, 'w');
        fwrite($fp, $contents);
        fclose($fp);
        // If we aren't able to use the rename function, try the alternate method
        if (!@rename($tempfile, $filename)) {
            copy($tempfile, $filename);
            unlink($tempfile);
        }
        chmod($filename, 0o664); // it was created 0600
    }

    public static function getManageSession()
    {
        $_session = (isset(kxEnv::$request['sid'])) ? kxEnv::$request['sid'] : '';

        // Do we have a session at all?
        if (!$_session) {
            return false;
        }
        // So far so good, let's check it
        $session_data = kxOrm::getEntityManager()->getRepository('\Edaha\Entities\UserSession')->findOneBy([
            'sid' => kxEnv::$request['sid'],
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
                'sid' => kxEnv::$request['sid'],
            ]);

            return [
                'user_name' => $session_data->user->username,
            ];
        }
    }

    public static function ConvertBytes($bytes)
    {
        // Thanks to an anonymous user for this
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0); // cleanup to make sure the value is an integer
        $exponent = floor(($bytes ? log($bytes) : 0) / log(1024)); // determine the offset (in powers of 1024) that is required to fit the given byte value
        $exponent = min($exponent, count($units) - 1); // clamp it so it doesn't exceed the maximum identifier in our unit array

        $bytes /= pow(1024, $exponent); // divide our number of bytes by the power granted by our exponent (since our number was >= our power, this gives a value with fractions such as 145.49572, of that unit)

        return round($bytes, 2).$units[$exponent]; // return the rounded fraction (with 2 decimals) and what unit it relates to
    }

    public static function fullBoardList()
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

    public static function visibleBoardList()
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
