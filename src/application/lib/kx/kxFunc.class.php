<?php

use Edaha\Entities\Ban;

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
 * Static functions that don't fit anywhere else
 * Last Updated: $Date$

 * @author     $Author$

 * @package    kusaba

 * @version    $Revision$
 *
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

class kxMb
{
    public static $kxCharset;
    private static $_caseFold;
    private static $_codeRanges;
    private static $_codeRange;
    private static $_table;

    /**
     * Convert a string between charsets.
     *
     * @param   string    Input String
     * @param   string    Source char set
     * @param   string    Destination char set
     * @param mixed $string
     * @param mixed $sourceCharset
     * @param mixed $destCharset
     *
     * @return string Converted string
     */
    public static function convertCharset($string, $sourceCharset, $destCharset = 'UTF-8')
    {
        $sourceCharset = strtolower($sourceCharset);
        $destCharset = strtolower($destCharset);

        if ($sourceCharset == $destCharset) {
            return $string;
        }

        if (!is_object(self::$kxCharset)) {
            self::$kxCharset = new kxCharset();

            if (function_exists('mb_convert_encoding')) {
                self::$kxCharset->method = 'mbstrings';
            } elseif (function_exists('iconv')) {
                self::$kxCharset->method = 'iconv';
            } elseif (function_exists('recode_string')) {
                self::$kxCharset->method = 'recode';
            } else {
                self::$kxCharset->method = 'tables';
            }
        }

        $stringConverted = self::$kxCharset->convert($string, $sourceCharset, $destCharset);

        return $stringConverted ? $stringConverted : $string;
    }

    /**
     * mb_strtoupper wrapper.
     *
     * @param   string  Input String
     * @param mixed $text
     *
     * @return string Parsed string
     */
    public static function strtoupper($text)
    {
        if (0 && function_exists('mb_strtoupper')) {
            $encodings = array_map('strtolower', mb_list_encodings());

            if (count($encodings) && in_array(strtolower(kxEnv::get('kx:charset')), $encodings)) {
                return mb_strtoupper($text, strtoupper(kxEnv::get('kx:charset')));
            }
        }
        $convertBack = !self::seemsUtf8($text);
        if ($convertBack) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return strtoupper($text);
            }
            $text = self::convertCharset($text, kxEnv::get('kx:charset'), 'UTF-8');
        }
        $utf8Map = self::utf8($text);

        $length = count($utf8Map);
        $matched = false;
        $replaced = [];
        $upperCase = [];

        for ($i = 0; $i < $length; ++$i) {
            $char = $utf8Map[$i];

            if ($char < 128) {
                $str = strtoupper(chr($char));
                $strlen = strlen($str);
                for ($ii = 0; $ii < $strlen; ++$ii) {
                    $upper = ord(substr($str, $ii, 1));
                }
                $upperCase[] = $upper;
                $matched = true;
            } else {
                $matched = false;
                $keys = self::_findCase($char);
                $keyCount = count($keys);

                if (!empty($keys)) {
                    foreach ($keys as $key => $value) {
                        $matched = false;
                        $replace = 0;
                        if ($length > 1 && count($keys[$key]['lower']) > 1) {
                            $j = 0;

                            for ($ii = 0, $count = count($keys[$key]['lower']); $ii < $count; ++$ii) {
                                $nextChar = $utf8Map[$i + $ii];

                                if (isset($nextChar) && ($nextChar == $keys[$key]['lower'][$j + $ii])) {
                                    ++$replace;
                                }
                            }
                            if ($replace == $count) {
                                $upperCase[] = $keys[$key]['upper'];
                                $replaced = array_merge($replaced, array_values($keys[$key]['lower']));
                                $matched = true;

                                break;
                            }
                        } elseif ($length > 1 && $keyCount > 1) {
                            $j = 0;
                            for ($ii = 1; $ii < $keyCount; ++$ii) {
                                $nextChar = $utf8Map[$i + $ii - 1];

                                if (in_array($nextChar, $keys[$ii]['lower'])) {
                                    for ($jj = 0, $count = count($keys[$ii]['lower']); $jj < $count; ++$jj) {
                                        $nextChar = $utf8Map[$i + $jj];

                                        if (isset($nextChar) && ($nextChar == $keys[$ii]['lower'][$j + $jj])) {
                                            ++$replace;
                                        }
                                    }
                                    if ($replace == $count) {
                                        $upperCase[] = $keys[$ii]['upper'];
                                        $replaced = array_merge($replaced, array_values($keys[$ii]['lower']));
                                        $matched = true;

                                        break 2;
                                    }
                                }
                            }
                        }
                        if ($keys[$key]['lower'][0] == $char) {
                            $upperCase[] = $keys[$key]['upper'];
                            $matched = true;

                            break;
                        }
                    }
                }
            }
            if (false === $matched && !in_array($char, $replaced, true)) {
                $upperCase[] = $char;
            }
        }

        if ($convertBack) {
            return self::convertCharset(self::ascii($upperCase), 'UTF-8', kxEnv::get('kx:charset'));
        }

        return self::ascii($upperCase);
    }

    /**
     * mb_strtolower wrapper.
     *
     * @param   string  Input String
     * @param mixed $text
     *
     * @return string Parsed string
     */
    public static function strtolower($text)
    {
        if (function_exists('mb_strtolower')) {
            $encodings = array_map('strtolower', mb_list_encodings());

            if (count($encodings) && in_array(strtolower(kxEnv::get('kx:charset')), $encodings)) {
                return mb_strtolower($text, strtoupper(kxEnv::get('kx:charset')));
            }
        }
        $convertBack = self::seemsUtf8($text);
        if (!$convertBack) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return strtolower($text);
            }
            $text = convertCharset($text, kxEnv::get('kx:charset'), 'UTF-8');
        }
        $utf8Map = self::utf8($text);
        $length = count($utf8Map);
        $lowerCase = [];
        $matched = false;

        for ($i = 0; $i < $length; ++$i) {
            $char = $utf8Map[$i];

            if ($char < 128) {
                $str = strtolower(chr($char));
                $strlen = strlen($str);
                for ($ii = 0; $ii < $strlen; ++$ii) {
                    $lower = ord(substr($str, $ii, 1));
                }
                $lowerCase[] = $lower;
                $matched = true;
            } else {
                $matched = false;
                $keys = self::_findCase($char, 'upper');

                if (!empty($keys)) {
                    foreach ($keys as $key => $value) {
                        if ($keys[$key]['upper'] == $char && 1 === count($keys[$key]['lower'][0])) {
                            $lowerCase[] = $keys[$key]['lower'][0];
                            $matched = true;

                            break;
                        }
                    }
                }
            }
            if (false === $matched) {
                $lowerCase[] = $char;
            }
        }
        if ($convertBack) {
            return self::convertCharset(self::ascii($value), 'UTF-8', kxEnv::get('kx:charset'));
        }

        return self::ascii($value);
    }

    /**
     * mb_substr wrapper.
     *
     * @param  string  Input String
     * @param  int  Desired min. length
     * @param mixed      $text
     * @param mixed      $start
     * @param null|mixed $length
     *
     * @return string Parsed string
     */
    public static function substr($text, $start, $length = null)
    {
        if (0 === $start && null === $length) {
            return $text;
        }

        if (function_exists('mb_substr')) {
            $encodings = array_map('strtolower', mb_list_encodings());

            if (count($encodings) && in_array(strtolower(kxEnv::get('kx:charset')), $encodings)) {
                return $length ? mb_substr($text, $start, $length) : mb_substr($text, $start);
            }
        }
        $convertBack = false;
        if (!self::seemsUtf8($text)) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return $length ? substr($text, $start, $length) : substr($text, $start);
            }
            $convertBack = true;
            $text = convertCharset($text, kxEnv::get('kx:charset'), 'UTF-8');
        }

        $text = self::utf8($text);
        $stringCount = count($text);

        if ($start < 0) {
            $start = self::strlen($text) + $start;
        }

        for ($i = 1; $i <= $start; ++$i) {
            unset($text[$i - 1]);
        }

        if (null === $length || count($text) < $length) {
            if ($convertBack) {
                return self::convertCharset(self::ascii($text), 'UTF-8', kxEnv::get('kx:charset'));
            }

            return self::ascii($text);
        }

        $text = array_values($text);

        $value = [];
        if ($length < 0) {
            $text = array_reverse($text);
            $legnth = abs($length);
            for ($i = 0; $i <= $length; ++$i) {
                unset($text[$i - 1]);
            }
            $text = array_reverse($text);
            $value = $text;
        } else {
            for ($i = 0; $i < $length; ++$i) {
                $value[] = $text[$i];
            }
        }

        if ($convertBack) {
            return self::convertCharset(self::ascii($value), 'UTF-8', kxEnv::get('kx:charset'));
        }

        return self::ascii($value);
    }

    /**
     * mb_strlen wrapper.
     *
     * @param   string    Input String
     * @param mixed $text
     *
     * @return int String length
     */
    public static function strlen($text)
    {
        if (function_exists('mb_strlen')) {
            $encodings = array_map('strtolower', mb_list_encodings());

            if (count($encodings) && in_array(strtolower(kxEnv::get('kx:charset')), $encodings)) {
                return mb_strlen($text, strtoupper(kxEnv::get('kx:charset')));
            }
        }
        if (!self::seemsUtf8($text)) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return strlen($text);
            }
            $text = convertCharset($text, kxEnv::get('kx:charset'), 'UTF-8');
        }
        if (self::checkMultibyte($text)) {
            $text = self::utf8($text);

            return count($text);
        }

        return strlen($text);
    }

    /**
     * mb_stripos wrapper.
     *
     * @param   string  Input haystack
     * @param   string  Input needle
     * @param   int  D
     * @param mixed $haystack
     * @param mixed $needle
     * @param mixed $offset
     *
     * @return string Parsed string
     *
     * @since  2.0
     */
    public static function stripos($haystack, $needle, $offset = 0)
    {
        if (function_exists('mb_stripos')) {
            $encodings = mb_list_encodings();

            if (count($encodings) && in_array(strtolower(kxEnv::get('kx:charset')), $encodings)) {
                return mb_stripos($haystack, $needle, $offset, strtoupper(kxEnv::get('kx:charset')));
            }
        }
        if (!self::seemsUtf8($haystack)) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return stripos($haystack, $needle, $offset);
            }
            $text = convertCharset($haystack, kxEnv::get('kx:charset'), 'UTF-8');
        }
        if (!self::seemsUtf8($needle)) {
            if ('UTF-8' == strtoupper(kxEnv::get('kx:charset'))) {
                return stripos($haystack, $needle, $offset);
            }
            $text = convertCharset($needle, kxEnv::get('kx:charset'), 'UTF-8');
        }
        if (self::checkMultibyte($haystack)) {
            $haystack = self::strtoupper($haystack);
            $needle = self::strtoupper($needle);

            return self::strpos($haystack, $needle, $offset);
        }

        return stripos($haystack, $needle, $offset);
    }

    /**
     * Converts a multibyte character string
     * to the decimal value of the character.
     *
     * @param multibyte string $string
     *
     * @return array
     *
     * @static
     */
    public static function utf8($string)
    {
        $map = [];

        $values = [];
        $find = 1;
        $length = strlen($string);

        for ($i = 0; $i < $length; ++$i) {
            $value = ord($string[$i]);

            if ($value < 128) {
                $map[] = $value;
            } else {
                if (empty($values)) {
                    if ($value >= 252) {
                        $find = 6;
                    } elseif ($value >= 248) {
                        $find = 5;
                    } elseif ($value >= 240) {
                        $find = 4;
                    } elseif ($value >= 224) {
                        $find = 3;
                    } elseif ($value >= 192) {
                        $find = 2;
                    }
                }
                $values[] = $value;

                if (count($values) === $find) {
                    switch ($find) {
                        case 2:
                            $map[] = ($values[0] - 192) * 64 + ($values[1] - 128);

                            break;

                        case 3:
                            $map[] = ($values[0] - 224) * 4096 + ($values[1] - 128) * 64 + ($values[2] - 128);

                            break;

                        case 4:
                            $map[] = ($values[0] - 240) * 262144 + ($values[1] - 128) * 4096 + ($values[2] - 128) * 64 + ($values[3] - 128);

                            break;

                        case 5:
                            $map[] = ($values[0] - 248) * 16777216 + ($values[1] - 128) * 262144 + ($values[2] - 128) * 4096 + ($values[3] - 128) * 64 + ($values[4] - 128);

                            break;

                        case 6:
                            $map[] = ($values[0] - 252) * 1073741824 + ($values[1] - 128) * 16777216 + ($values[2] - 128) * 262144 + ($values[3] - 128) * 4096 + ($values[4] - 128) * 64 + ($values[5] - 128);

                            break;
                    }
                    $values = [];
                    $find = 1;
                }
            }
        }

        return $map;
    }

    /**
     * Converts the decimal value of a multibyte character string
     * to a string.
     *
     * @param array $array
     *
     * @return string
     *
     * @static
     */
    public static function ascii($array)
    {
        $ascii = '';

        foreach ($array as $utf8) {
            if ($utf8 < 128) {
                $ascii .= chr($utf8);
            } elseif ($utf8 < 2048) {
                $ascii .= chr(192 + (($utf8 - ($utf8 % 64)) / 64));
                $ascii .= chr(128 + ($utf8 % 64));
            } elseif ($utf8 < 65536) {
                $ascii .= chr(224 + (($utf8 - ($utf8 % 4096)) / 4096));
                $ascii .= chr(128 + ((($utf8 % 4096) - ($utf8 % 64)) / 64));
                $ascii .= chr(128 + ($utf8 % 64));
            } elseif ($utf8 < 2097152) {
                $ascii .= chr(224 + (($utf8 - ($utf8 % 262144)) / 262144));
                $ascii .= chr(128 + ((($utf8 % 262144) - ($utf8 % 4096)) / 4096));
                $ascii .= chr(128 + ((($utf8 % 4096) - ($utf8 % 64)) / 64));
                $ascii .= chr(128 + ($utf8 % 64));
            }
        }

        return $ascii;
    }

    /**
     * Check the $string for multibyte characters.
     *
     * @param string $string value to test
     *
     * @return bool
     *
     * @static
     */
    public static function checkMultibyte($string)
    {
        $length = strlen($string);

        for ($i = 0; $i < $length; ++$i) {
            $value = ord($string[$i]);
            if ($value > 128) {
                return true;
            }
        }

        return false;
    }

    public static function seemsUtf8($string)
    {
        for ($i = 0; $i < strlen($string); ++$i) {
            if (ord($string[$i]) < 0x80) {
                continue;
            }
            // 0bbbbbbb
            if ((ord($string[$i]) & 0xE0) == 0xC0) {
                $n = 1;
            }
            // 110bbbbb
            elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
                $n = 2;
            }
            // 1110bbbb
            elseif ((ord($string[$i]) & 0xF8) == 0xF0) {
                $n = 3;
            }
            // 11110bbb
            elseif ((ord($string[$i]) & 0xFC) == 0xF8) {
                $n = 4;
            }
            // 111110bb
            elseif ((ord($string[$i]) & 0xFE) == 0xFC) {
                $n = 5;
            }
            // 1111110b
            else {
                return false;
            }
            // Does not match any model
            for ($j = 0; $j < $n; ++$j) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Find the related code folding values for $char.
     *
     * @param int    $char decimal value of character
     * @param string $type
     *
     * @return array
     */
    private static function _findCase($char, $type = 'lower')
    {
        $value = false;
        $found = [];
        if (!isset(self::$_codeRange[$char])) {
            $range = self::_codepoint($char);
            if (false === $range) {
                return null;
            }
            self::$_caseFold[$range] = self::_getRange($range);
        }

        if (!self::$_codeRange[$char]) {
            return null;
        }
        self::$_table = self::$_codeRange[$char];
        $count = count(self::$_caseFold[self::$_table]);

        for ($i = 0; $i < $count; ++$i) {
            if ('lower' === $type && self::$_caseFold[self::$_table][$i][$type][0] === $char) {
                $found[] = self::$_caseFold[self::$_table][$i];
            } elseif ('upper' === $type && self::$_caseFold[self::$_table][$i][$type] === $char) {
                $found[] = self::$_caseFold[self::$_table][$i];
            }
        }

        return $found;
    }

    private static function _codepoint($decimal)
    {
        if ($decimal > 128 && $decimal < 256) {
            $return = '0080_00ff'; // Latin-1 Supplement
        } elseif ($decimal < 384) {
            $return = '0100_017f'; // Latin Extended-A
        } elseif ($decimal < 592) {
            $return = '0180_024f'; // Latin Extended-B
        } elseif ($decimal < 688) {
            $return = '0250_02af'; // IPA Extensions
        } elseif ($decimal >= 880 && $decimal < 1024) {
            $return = '0370_03ff'; // Greek and Coptic
        } elseif ($decimal < 1280) {
            $return = '0400_04ff'; // Cyrillic
        } elseif ($decimal < 1328) {
            $return = '0500_052f'; // Cyrillic Supplement
        } elseif ($decimal < 1424) {
            $return = '0530_058f'; // Armenian
        } elseif ($decimal >= 7680 && $decimal < 7936) {
            $return = '1e00_1eff'; // Latin Extended Additional
        } elseif ($decimal < 8192) {
            $return = '1f00_1fff'; // Greek Extended
        } elseif ($decimal >= 8448 && $decimal < 8528) {
            $return = '2100_214f'; // Letterlike Symbols
        } elseif ($decimal < 8592) {
            $return = '2150_218f'; // Number Forms
        } elseif ($decimal >= 9312 && $decimal < 9472) {
            $return = '2460_24ff'; // Enclosed Alphanumerics
        } elseif ($decimal >= 11264 && $decimal < 11360) {
            $return = '2c00_2c5f'; // Glagolitic
        } elseif ($decimal < 11392) {
            $return = '2c60_2c7f'; // Latin Extended-C
        } elseif ($decimal < 11520) {
            $return = '2c80_2cff'; // Coptic
        } elseif ($decimal >= 65280 && $decimal < 65520) {
            $return = 'ff00_ffef'; // Halfwidth and Fullwidth Forms
        } else {
            $return = false;
        }
        self::$_codeRange[$decimal] = $return;

        return $return;
    }

    private static function _getRange($range)
    {
        if (empty(self::$_codeRanges[$range])) {
            /*
             * The upper field is the decimal value of the upper case character
             *
             * The lower filed is an array of the decimal values that form the lower case version of a character.
             *
             *  The status field is:
             * C: common case folding, common mappings shared by both simple and full mappings.
             * F: full case folding, mappings that cause strings to grow in length. Multiple characters are separated by spaces.
             * S: simple case folding, mappings to single characters where different from F.
             * T: special case for uppercase I and dotted uppercase I
             *   - For non-Turkic languages, this mapping is normally not used.
             *   - For Turkic languages (tr, az), this mapping can be used instead of the normal mapping for these characters.
             *     Note that the Turkic mappings do not maintain canonical equivalence without additional processing.
             *     See the discussions of case mapping in the Unicode Standard for more information.
             */
            switch ($range) {
                case '0080_00ff':
                    self::$_codeRanges['0080_00ff'] = [
                        ['upper' => 181, 'status' => 'C', 'lower' => [956]],
                        ['upper' => 924, 'status' => 'C', 'lower' => [181]],
                        ['upper' => 192, 'status' => 'C', 'lower' => [224]], // LATIN CAPITAL LETTER A WITH GRAVE
                        ['upper' => 193, 'status' => 'C', 'lower' => [225]], // LATIN CAPITAL LETTER A WITH ACUTE
                        ['upper' => 194, 'status' => 'C', 'lower' => [226]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX
                        ['upper' => 195, 'status' => 'C', 'lower' => [227]], // LATIN CAPITAL LETTER A WITH TILDE
                        ['upper' => 196, 'status' => 'C', 'lower' => [228]], // LATIN CAPITAL LETTER A WITH DIAERESIS
                        ['upper' => 197, 'status' => 'C', 'lower' => [229]], // LATIN CAPITAL LETTER A WITH RING ABOVE
                        ['upper' => 198, 'status' => 'C', 'lower' => [230]], // LATIN CAPITAL LETTER AE
                        ['upper' => 199, 'status' => 'C', 'lower' => [231]], // LATIN CAPITAL LETTER C WITH CEDILLA
                        ['upper' => 200, 'status' => 'C', 'lower' => [232]], // LATIN CAPITAL LETTER E WITH GRAVE
                        ['upper' => 201, 'status' => 'C', 'lower' => [233]], // LATIN CAPITAL LETTER E WITH ACUTE
                        ['upper' => 202, 'status' => 'C', 'lower' => [234]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX
                        ['upper' => 203, 'status' => 'C', 'lower' => [235]], // LATIN CAPITAL LETTER E WITH DIAERESIS
                        ['upper' => 204, 'status' => 'C', 'lower' => [236]], // LATIN CAPITAL LETTER I WITH GRAVE
                        ['upper' => 205, 'status' => 'C', 'lower' => [237]], // LATIN CAPITAL LETTER I WITH ACUTE
                        ['upper' => 206, 'status' => 'C', 'lower' => [238]], // LATIN CAPITAL LETTER I WITH CIRCUMFLEX
                        ['upper' => 207, 'status' => 'C', 'lower' => [239]], // LATIN CAPITAL LETTER I WITH DIAERESIS
                        ['upper' => 208, 'status' => 'C', 'lower' => [240]], // LATIN CAPITAL LETTER ETH
                        ['upper' => 209, 'status' => 'C', 'lower' => [241]], // LATIN CAPITAL LETTER N WITH TILDE
                        ['upper' => 210, 'status' => 'C', 'lower' => [242]], // LATIN CAPITAL LETTER O WITH GRAVE
                        ['upper' => 211, 'status' => 'C', 'lower' => [243]], // LATIN CAPITAL LETTER O WITH ACUTE
                        ['upper' => 212, 'status' => 'C', 'lower' => [244]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX
                        ['upper' => 213, 'status' => 'C', 'lower' => [245]], // LATIN CAPITAL LETTER O WITH TILDE
                        ['upper' => 214, 'status' => 'C', 'lower' => [246]], // LATIN CAPITAL LETTER O WITH DIAERESIS
                        ['upper' => 216, 'status' => 'C', 'lower' => [248]], // LATIN CAPITAL LETTER O WITH STROKE
                        ['upper' => 217, 'status' => 'C', 'lower' => [249]], // LATIN CAPITAL LETTER U WITH GRAVE
                        ['upper' => 218, 'status' => 'C', 'lower' => [250]], // LATIN CAPITAL LETTER U WITH ACUTE
                        ['upper' => 219, 'status' => 'C', 'lower' => [251]], // LATIN CAPITAL LETTER U WITH CIRCUMFLEX
                        ['upper' => 220, 'status' => 'C', 'lower' => [252]], // LATIN CAPITAL LETTER U WITH DIAERESIS
                        ['upper' => 221, 'status' => 'C', 'lower' => [253]], // LATIN CAPITAL LETTER Y WITH ACUTE
                        ['upper' => 222, 'status' => 'C', 'lower' => [254]], // LATIN CAPITAL LETTER THORN
                        ['upper' => 223, 'status' => 'F', 'lower' => [115, 115]], // LATIN SMALL LETTER SHARP S
                    ];

                    break;

                case '0100_017f':
                    self::$_codeRanges['0100_017f'] = [
                        ['upper' => 256, 'status' => 'C', 'lower' => [257]], // LATIN CAPITAL LETTER A WITH MACRON
                        ['upper' => 258, 'status' => 'C', 'lower' => [259]], // LATIN CAPITAL LETTER A WITH BREVE
                        ['upper' => 260, 'status' => 'C', 'lower' => [261]], // LATIN CAPITAL LETTER A WITH OGONEK
                        ['upper' => 262, 'status' => 'C', 'lower' => [263]], // LATIN CAPITAL LETTER C WITH ACUTE
                        ['upper' => 264, 'status' => 'C', 'lower' => [265]], // LATIN CAPITAL LETTER C WITH CIRCUMFLEX
                        ['upper' => 266, 'status' => 'C', 'lower' => [267]], // LATIN CAPITAL LETTER C WITH DOT ABOVE
                        ['upper' => 268, 'status' => 'C', 'lower' => [269]], // LATIN CAPITAL LETTER C WITH CARON
                        ['upper' => 270, 'status' => 'C', 'lower' => [271]], // LATIN CAPITAL LETTER D WITH CARON
                        ['upper' => 272, 'status' => 'C', 'lower' => [273]], // LATIN CAPITAL LETTER D WITH STROKE
                        ['upper' => 274, 'status' => 'C', 'lower' => [275]], // LATIN CAPITAL LETTER E WITH MACRON
                        ['upper' => 276, 'status' => 'C', 'lower' => [277]], // LATIN CAPITAL LETTER E WITH BREVE
                        ['upper' => 278, 'status' => 'C', 'lower' => [279]], // LATIN CAPITAL LETTER E WITH DOT ABOVE
                        ['upper' => 280, 'status' => 'C', 'lower' => [281]], // LATIN CAPITAL LETTER E WITH OGONEK
                        ['upper' => 282, 'status' => 'C', 'lower' => [283]], // LATIN CAPITAL LETTER E WITH CARON
                        ['upper' => 284, 'status' => 'C', 'lower' => [285]], // LATIN CAPITAL LETTER G WITH CIRCUMFLEX
                        ['upper' => 286, 'status' => 'C', 'lower' => [287]], // LATIN CAPITAL LETTER G WITH BREVE
                        ['upper' => 288, 'status' => 'C', 'lower' => [289]], // LATIN CAPITAL LETTER G WITH DOT ABOVE
                        ['upper' => 290, 'status' => 'C', 'lower' => [291]], // LATIN CAPITAL LETTER G WITH CEDILLA
                        ['upper' => 292, 'status' => 'C', 'lower' => [293]], // LATIN CAPITAL LETTER H WITH CIRCUMFLEX
                        ['upper' => 294, 'status' => 'C', 'lower' => [295]], // LATIN CAPITAL LETTER H WITH STROKE
                        ['upper' => 296, 'status' => 'C', 'lower' => [297]], // LATIN CAPITAL LETTER I WITH TILDE
                        ['upper' => 298, 'status' => 'C', 'lower' => [299]], // LATIN CAPITAL LETTER I WITH MACRON
                        ['upper' => 300, 'status' => 'C', 'lower' => [301]], // LATIN CAPITAL LETTER I WITH BREVE
                        ['upper' => 302, 'status' => 'C', 'lower' => [303]], // LATIN CAPITAL LETTER I WITH OGONEK
                        ['upper' => 304, 'status' => 'F', 'lower' => [105, 775]], // LATIN CAPITAL LETTER I WITH DOT ABOVE
                        ['upper' => 304, 'status' => 'T', 'lower' => [105]], // LATIN CAPITAL LETTER I WITH DOT ABOVE
                        ['upper' => 306, 'status' => 'C', 'lower' => [307]], // LATIN CAPITAL LIGATURE IJ
                        ['upper' => 308, 'status' => 'C', 'lower' => [309]], // LATIN CAPITAL LETTER J WITH CIRCUMFLEX
                        ['upper' => 310, 'status' => 'C', 'lower' => [311]], // LATIN CAPITAL LETTER K WITH CEDILLA
                        ['upper' => 313, 'status' => 'C', 'lower' => [314]], // LATIN CAPITAL LETTER L WITH ACUTE
                        ['upper' => 315, 'status' => 'C', 'lower' => [316]], // LATIN CAPITAL LETTER L WITH CEDILLA
                        ['upper' => 317, 'status' => 'C', 'lower' => [318]], // LATIN CAPITAL LETTER L WITH CARON
                        ['upper' => 319, 'status' => 'C', 'lower' => [320]], // LATIN CAPITAL LETTER L WITH MIDDLE DOT
                        ['upper' => 321, 'status' => 'C', 'lower' => [322]], // LATIN CAPITAL LETTER L WITH STROKE
                        ['upper' => 323, 'status' => 'C', 'lower' => [324]], // LATIN CAPITAL LETTER N WITH ACUTE
                        ['upper' => 325, 'status' => 'C', 'lower' => [326]], // LATIN CAPITAL LETTER N WITH CEDILLA
                        ['upper' => 327, 'status' => 'C', 'lower' => [328]], // LATIN CAPITAL LETTER N WITH CARON
                        ['upper' => 329, 'status' => 'F', 'lower' => [700, 110]], // LATIN SMALL LETTER N PRECEDED BY APOSTROPHE
                        ['upper' => 330, 'status' => 'C', 'lower' => [331]], // LATIN CAPITAL LETTER ENG
                        ['upper' => 332, 'status' => 'C', 'lower' => [333]], // LATIN CAPITAL LETTER O WITH MACRON
                        ['upper' => 334, 'status' => 'C', 'lower' => [335]], // LATIN CAPITAL LETTER O WITH BREVE
                        ['upper' => 336, 'status' => 'C', 'lower' => [337]], // LATIN CAPITAL LETTER O WITH DOUBLE ACUTE
                        ['upper' => 338, 'status' => 'C', 'lower' => [339]], // LATIN CAPITAL LIGATURE OE
                        ['upper' => 340, 'status' => 'C', 'lower' => [341]], // LATIN CAPITAL LETTER R WITH ACUTE
                        ['upper' => 342, 'status' => 'C', 'lower' => [343]], // LATIN CAPITAL LETTER R WITH CEDILLA
                        ['upper' => 344, 'status' => 'C', 'lower' => [345]], // LATIN CAPITAL LETTER R WITH CARON
                        ['upper' => 346, 'status' => 'C', 'lower' => [347]], // LATIN CAPITAL LETTER S WITH ACUTE
                        ['upper' => 348, 'status' => 'C', 'lower' => [349]], // LATIN CAPITAL LETTER S WITH CIRCUMFLEX
                        ['upper' => 350, 'status' => 'C', 'lower' => [351]], // LATIN CAPITAL LETTER S WITH CEDILLA
                        ['upper' => 352, 'status' => 'C', 'lower' => [353]], // LATIN CAPITAL LETTER S WITH CARON
                        ['upper' => 354, 'status' => 'C', 'lower' => [355]], // LATIN CAPITAL LETTER T WITH CEDILLA
                        ['upper' => 356, 'status' => 'C', 'lower' => [357]], // LATIN CAPITAL LETTER T WITH CARON
                        ['upper' => 358, 'status' => 'C', 'lower' => [359]], // LATIN CAPITAL LETTER T WITH STROKE
                        ['upper' => 360, 'status' => 'C', 'lower' => [361]], // LATIN CAPITAL LETTER U WITH TILDE
                        ['upper' => 362, 'status' => 'C', 'lower' => [363]], // LATIN CAPITAL LETTER U WITH MACRON
                        ['upper' => 364, 'status' => 'C', 'lower' => [365]], // LATIN CAPITAL LETTER U WITH BREVE
                        ['upper' => 366, 'status' => 'C', 'lower' => [367]], // LATIN CAPITAL LETTER U WITH RING ABOVE
                        ['upper' => 368, 'status' => 'C', 'lower' => [369]], // LATIN CAPITAL LETTER U WITH DOUBLE ACUTE
                        ['upper' => 370, 'status' => 'C', 'lower' => [371]], // LATIN CAPITAL LETTER U WITH OGONEK
                        ['upper' => 372, 'status' => 'C', 'lower' => [373]], // LATIN CAPITAL LETTER W WITH CIRCUMFLEX
                        ['upper' => 374, 'status' => 'C', 'lower' => [375]], // LATIN CAPITAL LETTER Y WITH CIRCUMFLEX
                        ['upper' => 376, 'status' => 'C', 'lower' => [255]], // LATIN CAPITAL LETTER Y WITH DIAERESIS
                        ['upper' => 377, 'status' => 'C', 'lower' => [378]], // LATIN CAPITAL LETTER Z WITH ACUTE
                        ['upper' => 379, 'status' => 'C', 'lower' => [380]], // LATIN CAPITAL LETTER Z WITH DOT ABOVE
                        ['upper' => 381, 'status' => 'C', 'lower' => [382]], // LATIN CAPITAL LETTER Z WITH CARON
                        ['upper' => 383, 'status' => 'C', 'lower' => [115]], // LATIN SMALL LETTER LONG S
                    ];

                    break;

                case '0180_024f':
                    self::$_codeRanges['0180_024f'] = [
                        ['upper' => 385, 'status' => 'C', 'lower' => [595]], // LATIN CAPITAL LETTER B WITH HOOK
                        ['upper' => 386, 'status' => 'C', 'lower' => [387]], // LATIN CAPITAL LETTER B WITH TOPBAR
                        ['upper' => 388, 'status' => 'C', 'lower' => [389]], // LATIN CAPITAL LETTER TONE SIX
                        ['upper' => 390, 'status' => 'C', 'lower' => [596]], // LATIN CAPITAL LETTER OPEN O
                        ['upper' => 391, 'status' => 'C', 'lower' => [392]], // LATIN CAPITAL LETTER C WITH HOOK
                        ['upper' => 393, 'status' => 'C', 'lower' => [598]], // LATIN CAPITAL LETTER AFRICAN D
                        ['upper' => 394, 'status' => 'C', 'lower' => [599]], // LATIN CAPITAL LETTER D WITH HOOK
                        ['upper' => 395, 'status' => 'C', 'lower' => [396]], // LATIN CAPITAL LETTER D WITH TOPBAR
                        ['upper' => 398, 'status' => 'C', 'lower' => [477]], // LATIN CAPITAL LETTER REVERSED E
                        ['upper' => 399, 'status' => 'C', 'lower' => [601]], // LATIN CAPITAL LETTER SCHWA
                        ['upper' => 400, 'status' => 'C', 'lower' => [603]], // LATIN CAPITAL LETTER OPEN E
                        ['upper' => 401, 'status' => 'C', 'lower' => [402]], // LATIN CAPITAL LETTER F WITH HOOK
                        ['upper' => 403, 'status' => 'C', 'lower' => [608]], // LATIN CAPITAL LETTER G WITH HOOK
                        ['upper' => 404, 'status' => 'C', 'lower' => [611]], // LATIN CAPITAL LETTER GAMMA
                        ['upper' => 406, 'status' => 'C', 'lower' => [617]], // LATIN CAPITAL LETTER IOTA
                        ['upper' => 407, 'status' => 'C', 'lower' => [616]], // LATIN CAPITAL LETTER I WITH STROKE
                        ['upper' => 408, 'status' => 'C', 'lower' => [409]], // LATIN CAPITAL LETTER K WITH HOOK
                        ['upper' => 412, 'status' => 'C', 'lower' => [623]], // LATIN CAPITAL LETTER TURNED M
                        ['upper' => 413, 'status' => 'C', 'lower' => [626]], // LATIN CAPITAL LETTER N WITH LEFT HOOK
                        ['upper' => 415, 'status' => 'C', 'lower' => [629]], // LATIN CAPITAL LETTER O WITH MIDDLE TILDE
                        ['upper' => 416, 'status' => 'C', 'lower' => [417]], // LATIN CAPITAL LETTER O WITH HORN
                        ['upper' => 418, 'status' => 'C', 'lower' => [419]], // LATIN CAPITAL LETTER OI
                        ['upper' => 420, 'status' => 'C', 'lower' => [421]], // LATIN CAPITAL LETTER P WITH HOOK
                        ['upper' => 422, 'status' => 'C', 'lower' => [640]], // LATIN LETTER YR
                        ['upper' => 423, 'status' => 'C', 'lower' => [424]], // LATIN CAPITAL LETTER TONE TWO
                        ['upper' => 425, 'status' => 'C', 'lower' => [643]], // LATIN CAPITAL LETTER ESH
                        ['upper' => 428, 'status' => 'C', 'lower' => [429]], // LATIN CAPITAL LETTER T WITH HOOK
                        ['upper' => 430, 'status' => 'C', 'lower' => [648]], // LATIN CAPITAL LETTER T WITH RETROFLEX HOOK
                        ['upper' => 431, 'status' => 'C', 'lower' => [432]], // LATIN CAPITAL LETTER U WITH HORN
                        ['upper' => 433, 'status' => 'C', 'lower' => [650]], // LATIN CAPITAL LETTER UPSILON
                        ['upper' => 434, 'status' => 'C', 'lower' => [651]], // LATIN CAPITAL LETTER V WITH HOOK
                        ['upper' => 435, 'status' => 'C', 'lower' => [436]], // LATIN CAPITAL LETTER Y WITH HOOK
                        ['upper' => 437, 'status' => 'C', 'lower' => [438]], // LATIN CAPITAL LETTER Z WITH STROKE
                        ['upper' => 439, 'status' => 'C', 'lower' => [658]], // LATIN CAPITAL LETTER EZH
                        ['upper' => 440, 'status' => 'C', 'lower' => [441]], // LATIN CAPITAL LETTER EZH REVERSED
                        ['upper' => 444, 'status' => 'C', 'lower' => [445]], // LATIN CAPITAL LETTER TONE FIVE
                        ['upper' => 452, 'status' => 'C', 'lower' => [454]], // LATIN CAPITAL LETTER DZ WITH CARON
                        ['upper' => 453, 'status' => 'C', 'lower' => [454]], // LATIN CAPITAL LETTER D WITH SMALL LETTER Z WITH CARON
                        ['upper' => 455, 'status' => 'C', 'lower' => [457]], // LATIN CAPITAL LETTER LJ
                        ['upper' => 456, 'status' => 'C', 'lower' => [457]], // LATIN CAPITAL LETTER L WITH SMALL LETTER J
                        ['upper' => 458, 'status' => 'C', 'lower' => [460]], // LATIN CAPITAL LETTER NJ
                        ['upper' => 459, 'status' => 'C', 'lower' => [460]], // LATIN CAPITAL LETTER N WITH SMALL LETTER J
                        ['upper' => 461, 'status' => 'C', 'lower' => [462]], // LATIN CAPITAL LETTER A WITH CARON
                        ['upper' => 463, 'status' => 'C', 'lower' => [464]], // LATIN CAPITAL LETTER I WITH CARON
                        ['upper' => 465, 'status' => 'C', 'lower' => [466]], // LATIN CAPITAL LETTER O WITH CARON
                        ['upper' => 467, 'status' => 'C', 'lower' => [468]], // LATIN CAPITAL LETTER U WITH CARON
                        ['upper' => 469, 'status' => 'C', 'lower' => [470]], // LATIN CAPITAL LETTER U WITH DIAERESIS AND MACRON
                        ['upper' => 471, 'status' => 'C', 'lower' => [472]], // LATIN CAPITAL LETTER U WITH DIAERESIS AND ACUTE
                        ['upper' => 473, 'status' => 'C', 'lower' => [474]], // LATIN CAPITAL LETTER U WITH DIAERESIS AND CARON
                        ['upper' => 475, 'status' => 'C', 'lower' => [476]], // LATIN CAPITAL LETTER U WITH DIAERESIS AND GRAVE
                        ['upper' => 478, 'status' => 'C', 'lower' => [479]], // LATIN CAPITAL LETTER A WITH DIAERESIS AND MACRON
                        ['upper' => 480, 'status' => 'C', 'lower' => [481]], // LATIN CAPITAL LETTER A WITH DOT ABOVE AND MACRON
                        ['upper' => 482, 'status' => 'C', 'lower' => [483]], // LATIN CAPITAL LETTER AE WITH MACRON
                        ['upper' => 484, 'status' => 'C', 'lower' => [485]], // LATIN CAPITAL LETTER G WITH STROKE
                        ['upper' => 486, 'status' => 'C', 'lower' => [487]], // LATIN CAPITAL LETTER G WITH CARON
                        ['upper' => 488, 'status' => 'C', 'lower' => [489]], // LATIN CAPITAL LETTER K WITH CARON
                        ['upper' => 490, 'status' => 'C', 'lower' => [491]], // LATIN CAPITAL LETTER O WITH OGONEK
                        ['upper' => 492, 'status' => 'C', 'lower' => [493]], // LATIN CAPITAL LETTER O WITH OGONEK AND MACRON
                        ['upper' => 494, 'status' => 'C', 'lower' => [495]], // LATIN CAPITAL LETTER EZH WITH CARON
                        ['upper' => 496, 'status' => 'F', 'lower' => [106, 780]], // LATIN SMALL LETTER J WITH CARON
                        ['upper' => 497, 'status' => 'C', 'lower' => [499]], // LATIN CAPITAL LETTER DZ
                        ['upper' => 498, 'status' => 'C', 'lower' => [499]], // LATIN CAPITAL LETTER D WITH SMALL LETTER Z
                        ['upper' => 500, 'status' => 'C', 'lower' => [501]], // LATIN CAPITAL LETTER G WITH ACUTE
                        ['upper' => 502, 'status' => 'C', 'lower' => [405]], // LATIN CAPITAL LETTER HWAIR
                        ['upper' => 503, 'status' => 'C', 'lower' => [447]], // LATIN CAPITAL LETTER WYNN
                        ['upper' => 504, 'status' => 'C', 'lower' => [505]], // LATIN CAPITAL LETTER N WITH GRAVE
                        ['upper' => 506, 'status' => 'C', 'lower' => [507]], // LATIN CAPITAL LETTER A WITH RING ABOVE AND ACUTE
                        ['upper' => 508, 'status' => 'C', 'lower' => [509]], // LATIN CAPITAL LETTER AE WITH ACUTE
                        ['upper' => 510, 'status' => 'C', 'lower' => [511]], // LATIN CAPITAL LETTER O WITH STROKE AND ACUTE
                        ['upper' => 512, 'status' => 'C', 'lower' => [513]], // LATIN CAPITAL LETTER A WITH DOUBLE GRAVE
                        ['upper' => 514, 'status' => 'C', 'lower' => [515]], // LATIN CAPITAL LETTER A WITH INVERTED BREVE
                        ['upper' => 516, 'status' => 'C', 'lower' => [517]], // LATIN CAPITAL LETTER E WITH DOUBLE GRAVE
                        ['upper' => 518, 'status' => 'C', 'lower' => [519]], // LATIN CAPITAL LETTER E WITH INVERTED BREVE
                        ['upper' => 520, 'status' => 'C', 'lower' => [521]], // LATIN CAPITAL LETTER I WITH DOUBLE GRAVE
                        ['upper' => 522, 'status' => 'C', 'lower' => [523]], // LATIN CAPITAL LETTER I WITH INVERTED BREVE
                        ['upper' => 524, 'status' => 'C', 'lower' => [525]], // LATIN CAPITAL LETTER O WITH DOUBLE GRAVE
                        ['upper' => 526, 'status' => 'C', 'lower' => [527]], // LATIN CAPITAL LETTER O WITH INVERTED BREVE
                        ['upper' => 528, 'status' => 'C', 'lower' => [529]], // LATIN CAPITAL LETTER R WITH DOUBLE GRAVE
                        ['upper' => 530, 'status' => 'C', 'lower' => [531]], // LATIN CAPITAL LETTER R WITH INVERTED BREVE
                        ['upper' => 532, 'status' => 'C', 'lower' => [533]], // LATIN CAPITAL LETTER U WITH DOUBLE GRAVE
                        ['upper' => 534, 'status' => 'C', 'lower' => [535]], // LATIN CAPITAL LETTER U WITH INVERTED BREVE
                        ['upper' => 536, 'status' => 'C', 'lower' => [537]], // LATIN CAPITAL LETTER S WITH COMMA BELOW
                        ['upper' => 538, 'status' => 'C', 'lower' => [539]], // LATIN CAPITAL LETTER T WITH COMMA BELOW
                        ['upper' => 540, 'status' => 'C', 'lower' => [541]], // LATIN CAPITAL LETTER YOGH
                        ['upper' => 542, 'status' => 'C', 'lower' => [543]], // LATIN CAPITAL LETTER H WITH CARON
                        ['upper' => 544, 'status' => 'C', 'lower' => [414]], // LATIN CAPITAL LETTER N WITH LONG RIGHT LEG
                        ['upper' => 546, 'status' => 'C', 'lower' => [547]], // LATIN CAPITAL LETTER OU
                        ['upper' => 548, 'status' => 'C', 'lower' => [549]], // LATIN CAPITAL LETTER Z WITH HOOK
                        ['upper' => 550, 'status' => 'C', 'lower' => [551]], // LATIN CAPITAL LETTER A WITH DOT ABOVE
                        ['upper' => 552, 'status' => 'C', 'lower' => [553]], // LATIN CAPITAL LETTER E WITH CEDILLA
                        ['upper' => 554, 'status' => 'C', 'lower' => [555]], // LATIN CAPITAL LETTER O WITH DIAERESIS AND MACRON
                        ['upper' => 556, 'status' => 'C', 'lower' => [557]], // LATIN CAPITAL LETTER O WITH TILDE AND MACRON
                        ['upper' => 558, 'status' => 'C', 'lower' => [559]], // LATIN CAPITAL LETTER O WITH DOT ABOVE
                        ['upper' => 560, 'status' => 'C', 'lower' => [561]], // LATIN CAPITAL LETTER O WITH DOT ABOVE AND MACRON
                        ['upper' => 562, 'status' => 'C', 'lower' => [563]], // LATIN CAPITAL LETTER Y WITH MACRON
                        ['upper' => 570, 'status' => 'C', 'lower' => [11365]], // LATIN CAPITAL LETTER A WITH STROKE
                        ['upper' => 571, 'status' => 'C', 'lower' => [572]], // LATIN CAPITAL LETTER C WITH STROKE
                        ['upper' => 573, 'status' => 'C', 'lower' => [410]], // LATIN CAPITAL LETTER L WITH BAR
                        ['upper' => 574, 'status' => 'C', 'lower' => [11366]], // LATIN CAPITAL LETTER T WITH DIAGONAL STROKE
                        ['upper' => 577, 'status' => 'C', 'lower' => [578]], // LATIN CAPITAL LETTER GLOTTAL STOP
                        ['upper' => 579, 'status' => 'C', 'lower' => [384]], // LATIN CAPITAL LETTER B WITH STROKE
                        ['upper' => 580, 'status' => 'C', 'lower' => [649]], // LATIN CAPITAL LETTER U BAR
                        ['upper' => 581, 'status' => 'C', 'lower' => [652]], // LATIN CAPITAL LETTER TURNED V
                        ['upper' => 582, 'status' => 'C', 'lower' => [583]], // LATIN CAPITAL LETTER E WITH STROKE
                        ['upper' => 584, 'status' => 'C', 'lower' => [585]], // LATIN CAPITAL LETTER J WITH STROKE
                        ['upper' => 586, 'status' => 'C', 'lower' => [587]], // LATIN CAPITAL LETTER SMALL Q WITH HOOK TAIL
                        ['upper' => 588, 'status' => 'C', 'lower' => [589]], // LATIN CAPITAL LETTER R WITH STROKE
                        ['upper' => 590, 'status' => 'C', 'lower' => [591]], // LATIN CAPITAL LETTER Y WITH STROKE
                    ];

                    break;

                case '0250_02af':
                    self::$_codeRanges['0250_02af'] = [
                        ['upper' => 422, 'status' => 'C', 'lower' => [640]],
                    ];

                    break;

                case '0370_03ff':
                    self::$_codeRanges['0370_03ff'] = [
                        ['upper' => 902, 'status' => 'C', 'lower' => [940]], // GREEK CAPITAL LETTER ALPHA WITH TONOS
                        ['upper' => 904, 'status' => 'C', 'lower' => [941]], // GREEK CAPITAL LETTER EPSILON WITH TONOS
                        ['upper' => 905, 'status' => 'C', 'lower' => [942]], // GREEK CAPITAL LETTER ETA WITH TONOS
                        ['upper' => 906, 'status' => 'C', 'lower' => [943]], // GREEK CAPITAL LETTER IOTA WITH TONOS
                        ['upper' => 908, 'status' => 'C', 'lower' => [972]], // GREEK CAPITAL LETTER OMICRON WITH TONOS
                        ['upper' => 910, 'status' => 'C', 'lower' => [973]], // GREEK CAPITAL LETTER UPSILON WITH TONOS
                        ['upper' => 911, 'status' => 'C', 'lower' => [974]], // GREEK CAPITAL LETTER OMEGA WITH TONOS
                        ['upper' => 913, 'status' => 'C', 'lower' => [945]], // GREEK CAPITAL LETTER ALPHA
                        ['upper' => 914, 'status' => 'C', 'lower' => [946]], // GREEK CAPITAL LETTER BETA
                        ['upper' => 915, 'status' => 'C', 'lower' => [947]], // GREEK CAPITAL LETTER GAMMA
                        ['upper' => 916, 'status' => 'C', 'lower' => [948]], // GREEK CAPITAL LETTER DELTA
                        ['upper' => 917, 'status' => 'C', 'lower' => [949]], // GREEK CAPITAL LETTER EPSILON
                        ['upper' => 918, 'status' => 'C', 'lower' => [950]], // GREEK CAPITAL LETTER ZETA
                        ['upper' => 919, 'status' => 'C', 'lower' => [951]], // GREEK CAPITAL LETTER ETA
                        ['upper' => 920, 'status' => 'C', 'lower' => [952]], // GREEK CAPITAL LETTER THETA
                        ['upper' => 921, 'status' => 'C', 'lower' => [953]], // GREEK CAPITAL LETTER IOTA
                        ['upper' => 922, 'status' => 'C', 'lower' => [954]], // GREEK CAPITAL LETTER KAPPA
                        ['upper' => 923, 'status' => 'C', 'lower' => [955]], // GREEK CAPITAL LETTER LAMDA
                        ['upper' => 924, 'status' => 'C', 'lower' => [956]], // GREEK CAPITAL LETTER MU
                        ['upper' => 925, 'status' => 'C', 'lower' => [957]], // GREEK CAPITAL LETTER NU
                        ['upper' => 926, 'status' => 'C', 'lower' => [958]], // GREEK CAPITAL LETTER XI
                        ['upper' => 927, 'status' => 'C', 'lower' => [959]], // GREEK CAPITAL LETTER OMICRON
                        ['upper' => 928, 'status' => 'C', 'lower' => [960]], // GREEK CAPITAL LETTER PI
                        ['upper' => 929, 'status' => 'C', 'lower' => [961]], // GREEK CAPITAL LETTER RHO
                        ['upper' => 931, 'status' => 'C', 'lower' => [963]], // GREEK CAPITAL LETTER SIGMA
                        ['upper' => 932, 'status' => 'C', 'lower' => [964]], // GREEK CAPITAL LETTER TAU
                        ['upper' => 933, 'status' => 'C', 'lower' => [965]], // GREEK CAPITAL LETTER UPSILON
                        ['upper' => 934, 'status' => 'C', 'lower' => [966]], // GREEK CAPITAL LETTER PHI
                        ['upper' => 935, 'status' => 'C', 'lower' => [967]], // GREEK CAPITAL LETTER CHI
                        ['upper' => 936, 'status' => 'C', 'lower' => [968]], // GREEK CAPITAL LETTER PSI
                        ['upper' => 937, 'status' => 'C', 'lower' => [969]], // GREEK CAPITAL LETTER OMEGA
                        ['upper' => 938, 'status' => 'C', 'lower' => [970]], // GREEK CAPITAL LETTER IOTA WITH DIALYTIKA
                        ['upper' => 939, 'status' => 'C', 'lower' => [971]], // GREEK CAPITAL LETTER UPSILON WITH DIALYTIKA
                        ['upper' => 944, 'status' => 'F', 'lower' => [965, 776, 769]], // GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND TONOS
                        ['upper' => 962, 'status' => 'C', 'lower' => [963]], // GREEK SMALL LETTER FINAL SIGMA
                        ['upper' => 976, 'status' => 'C', 'lower' => [946]], // GREEK BETA SYMBOL
                        ['upper' => 977, 'status' => 'C', 'lower' => [952]], // GREEK THETA SYMBOL
                        ['upper' => 981, 'status' => 'C', 'lower' => [966]], // GREEK PHI SYMBOL
                        ['upper' => 982, 'status' => 'C', 'lower' => [960]], // GREEK PI SYMBOL
                        ['upper' => 984, 'status' => 'C', 'lower' => [985]], // GREEK LETTER ARCHAIC KOPPA
                        ['upper' => 986, 'status' => 'C', 'lower' => [987]], // GREEK LETTER STIGMA
                        ['upper' => 988, 'status' => 'C', 'lower' => [989]], // GREEK LETTER DIGAMMA
                        ['upper' => 990, 'status' => 'C', 'lower' => [991]], // GREEK LETTER KOPPA
                        ['upper' => 992, 'status' => 'C', 'lower' => [993]], // GREEK LETTER SAMPI
                        ['upper' => 994, 'status' => 'C', 'lower' => [995]], // COPTIC CAPITAL LETTER SHEI
                        ['upper' => 996, 'status' => 'C', 'lower' => [997]], // COPTIC CAPITAL LETTER FEI
                        ['upper' => 998, 'status' => 'C', 'lower' => [999]], // COPTIC CAPITAL LETTER KHEI
                        ['upper' => 1000, 'status' => 'C', 'lower' => [1001]], // COPTIC CAPITAL LETTER HORI
                        ['upper' => 1002, 'status' => 'C', 'lower' => [1003]], // COPTIC CAPITAL LETTER GANGIA
                        ['upper' => 1004, 'status' => 'C', 'lower' => [1005]], // COPTIC CAPITAL LETTER SHIMA
                        ['upper' => 1006, 'status' => 'C', 'lower' => [1007]], // COPTIC CAPITAL LETTER DEI
                        ['upper' => 1008, 'status' => 'C', 'lower' => [954]], // GREEK KAPPA SYMBOL
                        ['upper' => 1009, 'status' => 'C', 'lower' => [961]], // GREEK RHO SYMBOL
                        ['upper' => 1012, 'status' => 'C', 'lower' => [952]], // GREEK CAPITAL THETA SYMBOL
                        ['upper' => 1013, 'status' => 'C', 'lower' => [949]], // GREEK LUNATE EPSILON SYMBOL
                        ['upper' => 1015, 'status' => 'C', 'lower' => [1016]], // GREEK CAPITAL LETTER SHO
                        ['upper' => 1017, 'status' => 'C', 'lower' => [1010]], // GREEK CAPITAL LUNATE SIGMA SYMBOL
                        ['upper' => 1018, 'status' => 'C', 'lower' => [1019]], // GREEK CAPITAL LETTER SAN
                        ['upper' => 1021, 'status' => 'C', 'lower' => [891]], // GREEK CAPITAL REVERSED LUNATE SIGMA SYMBOL
                        ['upper' => 1022, 'status' => 'C', 'lower' => [892]], // GREEK CAPITAL DOTTED LUNATE SIGMA SYMBOL
                        ['upper' => 1023, 'status' => 'C', 'lower' => [893]], // GREEK CAPITAL REVERSED DOTTED LUNATE SIGMA SYMBOL
                    ];

                    break;

                case '0400_04ff':
                    self::$_codeRanges['0400_04ff'] = [
                        ['upper' => 1024, 'status' => 'C', 'lower' => [1104]], // CYRILLIC CAPITAL LETTER IE WITH GRAVE
                        ['upper' => 1025, 'status' => 'C', 'lower' => [1105]], // CYRILLIC CAPITAL LETTER IO
                        ['upper' => 1026, 'status' => 'C', 'lower' => [1106]], // CYRILLIC CAPITAL LETTER DJE
                        ['upper' => 1027, 'status' => 'C', 'lower' => [1107]], // CYRILLIC CAPITAL LETTER GJE
                        ['upper' => 1028, 'status' => 'C', 'lower' => [1108]], // CYRILLIC CAPITAL LETTER UKRAINIAN IE
                        ['upper' => 1029, 'status' => 'C', 'lower' => [1109]], // CYRILLIC CAPITAL LETTER DZE
                        ['upper' => 1030, 'status' => 'C', 'lower' => [1110]], // CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I
                        ['upper' => 1031, 'status' => 'C', 'lower' => [1111]], // CYRILLIC CAPITAL LETTER YI
                        ['upper' => 1032, 'status' => 'C', 'lower' => [1112]], // CYRILLIC CAPITAL LETTER JE
                        ['upper' => 1033, 'status' => 'C', 'lower' => [1113]], // CYRILLIC CAPITAL LETTER LJE
                        ['upper' => 1034, 'status' => 'C', 'lower' => [1114]], // CYRILLIC CAPITAL LETTER NJE
                        ['upper' => 1035, 'status' => 'C', 'lower' => [1115]], // CYRILLIC CAPITAL LETTER TSHE
                        ['upper' => 1036, 'status' => 'C', 'lower' => [1116]], // CYRILLIC CAPITAL LETTER KJE
                        ['upper' => 1037, 'status' => 'C', 'lower' => [1117]], // CYRILLIC CAPITAL LETTER I WITH GRAVE
                        ['upper' => 1038, 'status' => 'C', 'lower' => [1118]], // CYRILLIC CAPITAL LETTER SHORT U
                        ['upper' => 1039, 'status' => 'C', 'lower' => [1119]], // CYRILLIC CAPITAL LETTER DZHE
                        ['upper' => 1040, 'status' => 'C', 'lower' => [1072]], // CYRILLIC CAPITAL LETTER A
                        ['upper' => 1041, 'status' => 'C', 'lower' => [1073]], // CYRILLIC CAPITAL LETTER BE
                        ['upper' => 1042, 'status' => 'C', 'lower' => [1074]], // CYRILLIC CAPITAL LETTER VE
                        ['upper' => 1043, 'status' => 'C', 'lower' => [1075]], // CYRILLIC CAPITAL LETTER GHE
                        ['upper' => 1044, 'status' => 'C', 'lower' => [1076]], // CYRILLIC CAPITAL LETTER DE
                        ['upper' => 1045, 'status' => 'C', 'lower' => [1077]], // CYRILLIC CAPITAL LETTER IE
                        ['upper' => 1046, 'status' => 'C', 'lower' => [1078]], // CYRILLIC CAPITAL LETTER ZHE
                        ['upper' => 1047, 'status' => 'C', 'lower' => [1079]], // CYRILLIC CAPITAL LETTER ZE
                        ['upper' => 1048, 'status' => 'C', 'lower' => [1080]], // CYRILLIC CAPITAL LETTER I
                        ['upper' => 1049, 'status' => 'C', 'lower' => [1081]], // CYRILLIC CAPITAL LETTER SHORT I
                        ['upper' => 1050, 'status' => 'C', 'lower' => [1082]], // CYRILLIC CAPITAL LETTER KA
                        ['upper' => 1051, 'status' => 'C', 'lower' => [1083]], // CYRILLIC CAPITAL LETTER EL
                        ['upper' => 1052, 'status' => 'C', 'lower' => [1084]], // CYRILLIC CAPITAL LETTER EM
                        ['upper' => 1053, 'status' => 'C', 'lower' => [1085]], // CYRILLIC CAPITAL LETTER EN
                        ['upper' => 1054, 'status' => 'C', 'lower' => [1086]], // CYRILLIC CAPITAL LETTER O
                        ['upper' => 1055, 'status' => 'C', 'lower' => [1087]], // CYRILLIC CAPITAL LETTER PE
                        ['upper' => 1056, 'status' => 'C', 'lower' => [1088]], // CYRILLIC CAPITAL LETTER ER
                        ['upper' => 1057, 'status' => 'C', 'lower' => [1089]], // CYRILLIC CAPITAL LETTER ES
                        ['upper' => 1058, 'status' => 'C', 'lower' => [1090]], // CYRILLIC CAPITAL LETTER TE
                        ['upper' => 1059, 'status' => 'C', 'lower' => [1091]], // CYRILLIC CAPITAL LETTER U
                        ['upper' => 1060, 'status' => 'C', 'lower' => [1092]], // CYRILLIC CAPITAL LETTER EF
                        ['upper' => 1061, 'status' => 'C', 'lower' => [1093]], // CYRILLIC CAPITAL LETTER HA
                        ['upper' => 1062, 'status' => 'C', 'lower' => [1094]], // CYRILLIC CAPITAL LETTER TSE
                        ['upper' => 1063, 'status' => 'C', 'lower' => [1095]], // CYRILLIC CAPITAL LETTER CHE
                        ['upper' => 1064, 'status' => 'C', 'lower' => [1096]], // CYRILLIC CAPITAL LETTER SHA
                        ['upper' => 1065, 'status' => 'C', 'lower' => [1097]], // CYRILLIC CAPITAL LETTER SHCHA
                        ['upper' => 1066, 'status' => 'C', 'lower' => [1098]], // CYRILLIC CAPITAL LETTER HARD SIGN
                        ['upper' => 1067, 'status' => 'C', 'lower' => [1099]], // CYRILLIC CAPITAL LETTER YERU
                        ['upper' => 1068, 'status' => 'C', 'lower' => [1100]], // CYRILLIC CAPITAL LETTER SOFT SIGN
                        ['upper' => 1069, 'status' => 'C', 'lower' => [1101]], // CYRILLIC CAPITAL LETTER E
                        ['upper' => 1070, 'status' => 'C', 'lower' => [1102]], // CYRILLIC CAPITAL LETTER YU
                        ['upper' => 1071, 'status' => 'C', 'lower' => [1103]], // CYRILLIC CAPITAL LETTER YA
                        ['upper' => 1120, 'status' => 'C', 'lower' => [1121]], // CYRILLIC CAPITAL LETTER OMEGA
                        ['upper' => 1122, 'status' => 'C', 'lower' => [1123]], // CYRILLIC CAPITAL LETTER YAT
                        ['upper' => 1124, 'status' => 'C', 'lower' => [1125]], // CYRILLIC CAPITAL LETTER IOTIFIED E
                        ['upper' => 1126, 'status' => 'C', 'lower' => [1127]], // CYRILLIC CAPITAL LETTER LITTLE YUS
                        ['upper' => 1128, 'status' => 'C', 'lower' => [1129]], // CYRILLIC CAPITAL LETTER IOTIFIED LITTLE YUS
                        ['upper' => 1130, 'status' => 'C', 'lower' => [1131]], // CYRILLIC CAPITAL LETTER BIG YUS
                        ['upper' => 1132, 'status' => 'C', 'lower' => [1133]], // CYRILLIC CAPITAL LETTER IOTIFIED BIG YUS
                        ['upper' => 1134, 'status' => 'C', 'lower' => [1135]], // CYRILLIC CAPITAL LETTER KSI
                        ['upper' => 1136, 'status' => 'C', 'lower' => [1137]], // CYRILLIC CAPITAL LETTER PSI
                        ['upper' => 1138, 'status' => 'C', 'lower' => [1139]], // CYRILLIC CAPITAL LETTER FITA
                        ['upper' => 1140, 'status' => 'C', 'lower' => [1141]], // CYRILLIC CAPITAL LETTER IZHITSA
                        ['upper' => 1142, 'status' => 'C', 'lower' => [1143]], // CYRILLIC CAPITAL LETTER IZHITSA WITH DOUBLE GRAVE ACCENT
                        ['upper' => 1144, 'status' => 'C', 'lower' => [1145]], // CYRILLIC CAPITAL LETTER UK
                        ['upper' => 1146, 'status' => 'C', 'lower' => [1147]], // CYRILLIC CAPITAL LETTER ROUND OMEGA
                        ['upper' => 1148, 'status' => 'C', 'lower' => [1149]], // CYRILLIC CAPITAL LETTER OMEGA WITH TITLO
                        ['upper' => 1150, 'status' => 'C', 'lower' => [1151]], // CYRILLIC CAPITAL LETTER OT
                        ['upper' => 1152, 'status' => 'C', 'lower' => [1153]], // CYRILLIC CAPITAL LETTER KOPPA
                        ['upper' => 1162, 'status' => 'C', 'lower' => [1163]], // CYRILLIC CAPITAL LETTER SHORT I WITH TAIL
                        ['upper' => 1164, 'status' => 'C', 'lower' => [1165]], // CYRILLIC CAPITAL LETTER SEMISOFT SIGN
                        ['upper' => 1166, 'status' => 'C', 'lower' => [1167]], // CYRILLIC CAPITAL LETTER ER WITH TICK
                        ['upper' => 1168, 'status' => 'C', 'lower' => [1169]], // CYRILLIC CAPITAL LETTER GHE WITH UPTURN
                        ['upper' => 1170, 'status' => 'C', 'lower' => [1171]], // CYRILLIC CAPITAL LETTER GHE WITH STROKE
                        ['upper' => 1172, 'status' => 'C', 'lower' => [1173]], // CYRILLIC CAPITAL LETTER GHE WITH MIDDLE HOOK
                        ['upper' => 1174, 'status' => 'C', 'lower' => [1175]], // CYRILLIC CAPITAL LETTER ZHE WITH DESCENDER
                        ['upper' => 1176, 'status' => 'C', 'lower' => [1177]], // CYRILLIC CAPITAL LETTER ZE WITH DESCENDER
                        ['upper' => 1178, 'status' => 'C', 'lower' => [1179]], // CYRILLIC CAPITAL LETTER KA WITH DESCENDER
                        ['upper' => 1180, 'status' => 'C', 'lower' => [1181]], // CYRILLIC CAPITAL LETTER KA WITH VERTICAL STROKE
                        ['upper' => 1182, 'status' => 'C', 'lower' => [1183]], // CYRILLIC CAPITAL LETTER KA WITH STROKE
                        ['upper' => 1184, 'status' => 'C', 'lower' => [1185]], // CYRILLIC CAPITAL LETTER BASHKIR KA
                        ['upper' => 1186, 'status' => 'C', 'lower' => [1187]], // CYRILLIC CAPITAL LETTER EN WITH DESCENDER
                        ['upper' => 1188, 'status' => 'C', 'lower' => [1189]], // CYRILLIC CAPITAL LIGATURE EN GHE
                        ['upper' => 1190, 'status' => 'C', 'lower' => [1191]], // CYRILLIC CAPITAL LETTER PE WITH MIDDLE HOOK
                        ['upper' => 1192, 'status' => 'C', 'lower' => [1193]], // CYRILLIC CAPITAL LETTER ABKHASIAN HA
                        ['upper' => 1194, 'status' => 'C', 'lower' => [1195]], // CYRILLIC CAPITAL LETTER ES WITH DESCENDER
                        ['upper' => 1196, 'status' => 'C', 'lower' => [1197]], // CYRILLIC CAPITAL LETTER TE WITH DESCENDER
                        ['upper' => 1198, 'status' => 'C', 'lower' => [1199]], // CYRILLIC CAPITAL LETTER STRAIGHT U
                        ['upper' => 1200, 'status' => 'C', 'lower' => [1201]], // CYRILLIC CAPITAL LETTER STRAIGHT U WITH STROKE
                        ['upper' => 1202, 'status' => 'C', 'lower' => [1203]], // CYRILLIC CAPITAL LETTER HA WITH DESCENDER
                        ['upper' => 1204, 'status' => 'C', 'lower' => [1205]], // CYRILLIC CAPITAL LIGATURE TE TSE
                        ['upper' => 1206, 'status' => 'C', 'lower' => [1207]], // CYRILLIC CAPITAL LETTER CHE WITH DESCENDER
                        ['upper' => 1208, 'status' => 'C', 'lower' => [1209]], // CYRILLIC CAPITAL LETTER CHE WITH VERTICAL STROKE
                        ['upper' => 1210, 'status' => 'C', 'lower' => [1211]], // CYRILLIC CAPITAL LETTER SHHA
                        ['upper' => 1212, 'status' => 'C', 'lower' => [1213]], // CYRILLIC CAPITAL LETTER ABKHASIAN CHE
                        ['upper' => 1214, 'status' => 'C', 'lower' => [1215]], // CYRILLIC CAPITAL LETTER ABKHASIAN CHE WITH DESCENDER
                        ['upper' => 1216, 'status' => 'C', 'lower' => [1231]], // CYRILLIC LETTER PALOCHKA
                        ['upper' => 1217, 'status' => 'C', 'lower' => [1218]], // CYRILLIC CAPITAL LETTER ZHE WITH BREVE
                        ['upper' => 1219, 'status' => 'C', 'lower' => [1220]], // CYRILLIC CAPITAL LETTER KA WITH HOOK
                        ['upper' => 1221, 'status' => 'C', 'lower' => [1222]], // CYRILLIC CAPITAL LETTER EL WITH TAIL
                        ['upper' => 1223, 'status' => 'C', 'lower' => [1224]], // CYRILLIC CAPITAL LETTER EN WITH HOOK
                        ['upper' => 1225, 'status' => 'C', 'lower' => [1226]], // CYRILLIC CAPITAL LETTER EN WITH TAIL
                        ['upper' => 1227, 'status' => 'C', 'lower' => [1228]], // CYRILLIC CAPITAL LETTER KHAKASSIAN CHE
                        ['upper' => 1229, 'status' => 'C', 'lower' => [1230]], // CYRILLIC CAPITAL LETTER EM WITH TAIL
                        ['upper' => 1232, 'status' => 'C', 'lower' => [1233]], // CYRILLIC CAPITAL LETTER A WITH BREVE
                        ['upper' => 1234, 'status' => 'C', 'lower' => [1235]], // CYRILLIC CAPITAL LETTER A WITH DIAERESIS
                        ['upper' => 1236, 'status' => 'C', 'lower' => [1237]], // CYRILLIC CAPITAL LIGATURE A IE
                        ['upper' => 1238, 'status' => 'C', 'lower' => [1239]], // CYRILLIC CAPITAL LETTER IE WITH BREVE
                        ['upper' => 1240, 'status' => 'C', 'lower' => [1241]], // CYRILLIC CAPITAL LETTER SCHWA
                        ['upper' => 1242, 'status' => 'C', 'lower' => [1243]], // CYRILLIC CAPITAL LETTER SCHWA WITH DIAERESIS
                        ['upper' => 1244, 'status' => 'C', 'lower' => [1245]], // CYRILLIC CAPITAL LETTER ZHE WITH DIAERESIS
                        ['upper' => 1246, 'status' => 'C', 'lower' => [1247]], // CYRILLIC CAPITAL LETTER ZE WITH DIAERESIS
                        ['upper' => 1248, 'status' => 'C', 'lower' => [1249]], // CYRILLIC CAPITAL LETTER ABKHASIAN DZE
                        ['upper' => 1250, 'status' => 'C', 'lower' => [1251]], // CYRILLIC CAPITAL LETTER I WITH MACRON
                        ['upper' => 1252, 'status' => 'C', 'lower' => [1253]], // CYRILLIC CAPITAL LETTER I WITH DIAERESIS
                        ['upper' => 1254, 'status' => 'C', 'lower' => [1255]], // CYRILLIC CAPITAL LETTER O WITH DIAERESIS
                        ['upper' => 1256, 'status' => 'C', 'lower' => [1257]], // CYRILLIC CAPITAL LETTER BARRED O
                        ['upper' => 1258, 'status' => 'C', 'lower' => [1259]], // CYRILLIC CAPITAL LETTER BARRED O WITH DIAERESIS
                        ['upper' => 1260, 'status' => 'C', 'lower' => [1261]], // CYRILLIC CAPITAL LETTER E WITH DIAERESIS
                        ['upper' => 1262, 'status' => 'C', 'lower' => [1263]], // CYRILLIC CAPITAL LETTER U WITH MACRON
                        ['upper' => 1264, 'status' => 'C', 'lower' => [1265]], // CYRILLIC CAPITAL LETTER U WITH DIAERESIS
                        ['upper' => 1266, 'status' => 'C', 'lower' => [1267]], // CYRILLIC CAPITAL LETTER U WITH DOUBLE ACUTE
                        ['upper' => 1268, 'status' => 'C', 'lower' => [1269]], // CYRILLIC CAPITAL LETTER CHE WITH DIAERESIS
                        ['upper' => 1270, 'status' => 'C', 'lower' => [1271]], // CYRILLIC CAPITAL LETTER GHE WITH DESCENDER
                        ['upper' => 1272, 'status' => 'C', 'lower' => [1273]], // CYRILLIC CAPITAL LETTER YERU WITH DIAERESIS
                        ['upper' => 1274, 'status' => 'C', 'lower' => [1275]], // CYRILLIC CAPITAL LETTER GHE WITH STROKE AND HOOK
                        ['upper' => 1276, 'status' => 'C', 'lower' => [1277]], // CYRILLIC CAPITAL LETTER HA WITH HOOK
                        ['upper' => 1278, 'status' => 'C', 'lower' => [1279]], // CYRILLIC CAPITAL LETTER HA WITH STROKE
                    ];

                    break;

                case '0500_052f':
                    self::$_codeRanges['0500_052f'] = [
                        ['upper' => 1280, 'status' => 'C', 'lower' => [1281]], // CYRILLIC CAPITAL LETTER KOMI DE
                        ['upper' => 1282, 'status' => 'C', 'lower' => [1283]], // CYRILLIC CAPITAL LETTER KOMI DJE
                        ['upper' => 1284, 'status' => 'C', 'lower' => [1285]], // CYRILLIC CAPITAL LETTER KOMI ZJE
                        ['upper' => 1286, 'status' => 'C', 'lower' => [1287]], // CYRILLIC CAPITAL LETTER KOMI DZJE
                        ['upper' => 1288, 'status' => 'C', 'lower' => [1289]], // CYRILLIC CAPITAL LETTER KOMI LJE
                        ['upper' => 1290, 'status' => 'C', 'lower' => [1291]], // CYRILLIC CAPITAL LETTER KOMI NJE
                        ['upper' => 1292, 'status' => 'C', 'lower' => [1293]], // CYRILLIC CAPITAL LETTER KOMI SJE
                        ['upper' => 1294, 'status' => 'C', 'lower' => [1295]], // CYRILLIC CAPITAL LETTER KOMI TJE
                        ['upper' => 1296, 'status' => 'C', 'lower' => [1297]], // CYRILLIC CAPITAL LETTER ZE
                        ['upper' => 1298, 'status' => 'C', 'lower' => [1299]], // CYRILLIC CAPITAL LETTER El with hook
                    ];

                    break;

                case '0530_058f':
                    self::$_codeRanges['0530_058f'] = [
                        ['upper' => 1329, 'status' => 'C', 'lower' => [1377]], // ARMENIAN CAPITAL LETTER AYB
                        ['upper' => 1330, 'status' => 'C', 'lower' => [1378]], // ARMENIAN CAPITAL LETTER BEN
                        ['upper' => 1331, 'status' => 'C', 'lower' => [1379]], // ARMENIAN CAPITAL LETTER GIM
                        ['upper' => 1332, 'status' => 'C', 'lower' => [1380]], // ARMENIAN CAPITAL LETTER DA
                        ['upper' => 1333, 'status' => 'C', 'lower' => [1381]], // ARMENIAN CAPITAL LETTER ECH
                        ['upper' => 1334, 'status' => 'C', 'lower' => [1382]], // ARMENIAN CAPITAL LETTER ZA
                        ['upper' => 1335, 'status' => 'C', 'lower' => [1383]], // ARMENIAN CAPITAL LETTER EH
                        ['upper' => 1336, 'status' => 'C', 'lower' => [1384]], // ARMENIAN CAPITAL LETTER ET
                        ['upper' => 1337, 'status' => 'C', 'lower' => [1385]], // ARMENIAN CAPITAL LETTER TO
                        ['upper' => 1338, 'status' => 'C', 'lower' => [1386]], // ARMENIAN CAPITAL LETTER ZHE
                        ['upper' => 1339, 'status' => 'C', 'lower' => [1387]], // ARMENIAN CAPITAL LETTER INI
                        ['upper' => 1340, 'status' => 'C', 'lower' => [1388]], // ARMENIAN CAPITAL LETTER LIWN
                        ['upper' => 1341, 'status' => 'C', 'lower' => [1389]], // ARMENIAN CAPITAL LETTER XEH
                        ['upper' => 1342, 'status' => 'C', 'lower' => [1390]], // ARMENIAN CAPITAL LETTER CA
                        ['upper' => 1343, 'status' => 'C', 'lower' => [1391]], // ARMENIAN CAPITAL LETTER KEN
                        ['upper' => 1344, 'status' => 'C', 'lower' => [1392]], // ARMENIAN CAPITAL LETTER HO
                        ['upper' => 1345, 'status' => 'C', 'lower' => [1393]], // ARMENIAN CAPITAL LETTER JA
                        ['upper' => 1346, 'status' => 'C', 'lower' => [1394]], // ARMENIAN CAPITAL LETTER GHAD
                        ['upper' => 1347, 'status' => 'C', 'lower' => [1395]], // ARMENIAN CAPITAL LETTER CHEH
                        ['upper' => 1348, 'status' => 'C', 'lower' => [1396]], // ARMENIAN CAPITAL LETTER MEN
                        ['upper' => 1349, 'status' => 'C', 'lower' => [1397]], // ARMENIAN CAPITAL LETTER YI
                        ['upper' => 1350, 'status' => 'C', 'lower' => [1398]], // ARMENIAN CAPITAL LETTER NOW
                        ['upper' => 1351, 'status' => 'C', 'lower' => [1399]], // ARMENIAN CAPITAL LETTER SHA
                        ['upper' => 1352, 'status' => 'C', 'lower' => [1400]], // ARMENIAN CAPITAL LETTER VO
                        ['upper' => 1353, 'status' => 'C', 'lower' => [1401]], // ARMENIAN CAPITAL LETTER CHA
                        ['upper' => 1354, 'status' => 'C', 'lower' => [1402]], // ARMENIAN CAPITAL LETTER PEH
                        ['upper' => 1355, 'status' => 'C', 'lower' => [1403]], // ARMENIAN CAPITAL LETTER JHEH
                        ['upper' => 1356, 'status' => 'C', 'lower' => [1404]], // ARMENIAN CAPITAL LETTER RA
                        ['upper' => 1357, 'status' => 'C', 'lower' => [1405]], // ARMENIAN CAPITAL LETTER SEH
                        ['upper' => 1358, 'status' => 'C', 'lower' => [1406]], // ARMENIAN CAPITAL LETTER VEW
                        ['upper' => 1359, 'status' => 'C', 'lower' => [1407]], // ARMENIAN CAPITAL LETTER TIWN
                        ['upper' => 1360, 'status' => 'C', 'lower' => [1408]], // ARMENIAN CAPITAL LETTER REH
                        ['upper' => 1361, 'status' => 'C', 'lower' => [1409]], // ARMENIAN CAPITAL LETTER CO
                        ['upper' => 1362, 'status' => 'C', 'lower' => [1410]], // ARMENIAN CAPITAL LETTER YIWN
                        ['upper' => 1363, 'status' => 'C', 'lower' => [1411]], // ARMENIAN CAPITAL LETTER PIWR
                        ['upper' => 1364, 'status' => 'C', 'lower' => [1412]], // ARMENIAN CAPITAL LETTER KEH
                        ['upper' => 1365, 'status' => 'C', 'lower' => [1413]], // ARMENIAN CAPITAL LETTER OH
                        ['upper' => 1366, 'status' => 'C', 'lower' => [1414]], // ARMENIAN CAPITAL LETTER FEH
                    ];

                    break;

                case '1e00_1eff':
                    self::$_codeRanges['1e00_1eff'] = [
                        ['upper' => 7680, 'status' => 'C', 'lower' => [7681]], // LATIN CAPITAL LETTER A WITH RING BELOW
                        ['upper' => 7682, 'status' => 'C', 'lower' => [7683]], // LATIN CAPITAL LETTER B WITH DOT ABOVE
                        ['upper' => 7684, 'status' => 'C', 'lower' => [7685]], // LATIN CAPITAL LETTER B WITH DOT BELOW
                        ['upper' => 7686, 'status' => 'C', 'lower' => [7687]], // LATIN CAPITAL LETTER B WITH LINE BELOW
                        ['upper' => 7688, 'status' => 'C', 'lower' => [7689]], // LATIN CAPITAL LETTER C WITH CEDILLA AND ACUTE
                        ['upper' => 7690, 'status' => 'C', 'lower' => [7691]], // LATIN CAPITAL LETTER D WITH DOT ABOVE
                        ['upper' => 7692, 'status' => 'C', 'lower' => [7693]], // LATIN CAPITAL LETTER D WITH DOT BELOW
                        ['upper' => 7694, 'status' => 'C', 'lower' => [7695]], // LATIN CAPITAL LETTER D WITH LINE BELOW
                        ['upper' => 7696, 'status' => 'C', 'lower' => [7697]], // LATIN CAPITAL LETTER D WITH CEDILLA
                        ['upper' => 7698, 'status' => 'C', 'lower' => [7699]], // LATIN CAPITAL LETTER D WITH CIRCUMFLEX BELOW
                        ['upper' => 7700, 'status' => 'C', 'lower' => [7701]], // LATIN CAPITAL LETTER E WITH MACRON AND GRAVE
                        ['upper' => 7702, 'status' => 'C', 'lower' => [7703]], // LATIN CAPITAL LETTER E WITH MACRON AND ACUTE
                        ['upper' => 7704, 'status' => 'C', 'lower' => [7705]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX BELOW
                        ['upper' => 7706, 'status' => 'C', 'lower' => [7707]], // LATIN CAPITAL LETTER E WITH TILDE BELOW
                        ['upper' => 7708, 'status' => 'C', 'lower' => [7709]], // LATIN CAPITAL LETTER E WITH CEDILLA AND BREVE
                        ['upper' => 7710, 'status' => 'C', 'lower' => [7711]], // LATIN CAPITAL LETTER F WITH DOT ABOVE
                        ['upper' => 7712, 'status' => 'C', 'lower' => [7713]], // LATIN CAPITAL LETTER G WITH MACRON
                        ['upper' => 7714, 'status' => 'C', 'lower' => [7715]], // LATIN CAPITAL LETTER H WITH DOT ABOVE
                        ['upper' => 7716, 'status' => 'C', 'lower' => [7717]], // LATIN CAPITAL LETTER H WITH DOT BELOW
                        ['upper' => 7718, 'status' => 'C', 'lower' => [7719]], // LATIN CAPITAL LETTER H WITH DIAERESIS
                        ['upper' => 7720, 'status' => 'C', 'lower' => [7721]], // LATIN CAPITAL LETTER H WITH CEDILLA
                        ['upper' => 7722, 'status' => 'C', 'lower' => [7723]], // LATIN CAPITAL LETTER H WITH BREVE BELOW
                        ['upper' => 7724, 'status' => 'C', 'lower' => [7725]], // LATIN CAPITAL LETTER I WITH TILDE BELOW
                        ['upper' => 7726, 'status' => 'C', 'lower' => [7727]], // LATIN CAPITAL LETTER I WITH DIAERESIS AND ACUTE
                        ['upper' => 7728, 'status' => 'C', 'lower' => [7729]], // LATIN CAPITAL LETTER K WITH ACUTE
                        ['upper' => 7730, 'status' => 'C', 'lower' => [7731]], // LATIN CAPITAL LETTER K WITH DOT BELOW
                        ['upper' => 7732, 'status' => 'C', 'lower' => [7733]], // LATIN CAPITAL LETTER K WITH LINE BELOW
                        ['upper' => 7734, 'status' => 'C', 'lower' => [7735]], // LATIN CAPITAL LETTER L WITH DOT BELOW
                        ['upper' => 7736, 'status' => 'C', 'lower' => [7737]], // LATIN CAPITAL LETTER L WITH DOT BELOW AND MACRON
                        ['upper' => 7738, 'status' => 'C', 'lower' => [7739]], // LATIN CAPITAL LETTER L WITH LINE BELOW
                        ['upper' => 7740, 'status' => 'C', 'lower' => [7741]], // LATIN CAPITAL LETTER L WITH CIRCUMFLEX BELOW
                        ['upper' => 7742, 'status' => 'C', 'lower' => [7743]], // LATIN CAPITAL LETTER M WITH ACUTE
                        ['upper' => 7744, 'status' => 'C', 'lower' => [7745]], // LATIN CAPITAL LETTER M WITH DOT ABOVE
                        ['upper' => 7746, 'status' => 'C', 'lower' => [7747]], // LATIN CAPITAL LETTER M WITH DOT BELOW
                        ['upper' => 7748, 'status' => 'C', 'lower' => [7749]], // LATIN CAPITAL LETTER N WITH DOT ABOVE
                        ['upper' => 7750, 'status' => 'C', 'lower' => [7751]], // LATIN CAPITAL LETTER N WITH DOT BELOW
                        ['upper' => 7752, 'status' => 'C', 'lower' => [7753]], // LATIN CAPITAL LETTER N WITH LINE BELOW
                        ['upper' => 7754, 'status' => 'C', 'lower' => [7755]], // LATIN CAPITAL LETTER N WITH CIRCUMFLEX BELOW
                        ['upper' => 7756, 'status' => 'C', 'lower' => [7757]], // LATIN CAPITAL LETTER O WITH TILDE AND ACUTE
                        ['upper' => 7758, 'status' => 'C', 'lower' => [7759]], // LATIN CAPITAL LETTER O WITH TILDE AND DIAERESIS
                        ['upper' => 7760, 'status' => 'C', 'lower' => [7761]], // LATIN CAPITAL LETTER O WITH MACRON AND GRAVE
                        ['upper' => 7762, 'status' => 'C', 'lower' => [7763]], // LATIN CAPITAL LETTER O WITH MACRON AND ACUTE
                        ['upper' => 7764, 'status' => 'C', 'lower' => [7765]], // LATIN CAPITAL LETTER P WITH ACUTE
                        ['upper' => 7766, 'status' => 'C', 'lower' => [7767]], // LATIN CAPITAL LETTER P WITH DOT ABOVE
                        ['upper' => 7768, 'status' => 'C', 'lower' => [7769]], // LATIN CAPITAL LETTER R WITH DOT ABOVE
                        ['upper' => 7770, 'status' => 'C', 'lower' => [7771]], // LATIN CAPITAL LETTER R WITH DOT BELOW
                        ['upper' => 7772, 'status' => 'C', 'lower' => [7773]], // LATIN CAPITAL LETTER R WITH DOT BELOW AND MACRON
                        ['upper' => 7774, 'status' => 'C', 'lower' => [7775]], // LATIN CAPITAL LETTER R WITH LINE BELOW
                        ['upper' => 7776, 'status' => 'C', 'lower' => [7777]], // LATIN CAPITAL LETTER S WITH DOT ABOVE
                        ['upper' => 7778, 'status' => 'C', 'lower' => [7779]], // LATIN CAPITAL LETTER S WITH DOT BELOW
                        ['upper' => 7780, 'status' => 'C', 'lower' => [7781]], // LATIN CAPITAL LETTER S WITH ACUTE AND DOT ABOVE
                        ['upper' => 7782, 'status' => 'C', 'lower' => [7783]], // LATIN CAPITAL LETTER S WITH CARON AND DOT ABOVE
                        ['upper' => 7784, 'status' => 'C', 'lower' => [7785]], // LATIN CAPITAL LETTER S WITH DOT BELOW AND DOT ABOVE
                        ['upper' => 7786, 'status' => 'C', 'lower' => [7787]], // LATIN CAPITAL LETTER T WITH DOT ABOVE
                        ['upper' => 7788, 'status' => 'C', 'lower' => [7789]], // LATIN CAPITAL LETTER T WITH DOT BELOW
                        ['upper' => 7790, 'status' => 'C', 'lower' => [7791]], // LATIN CAPITAL LETTER T WITH LINE BELOW
                        ['upper' => 7792, 'status' => 'C', 'lower' => [7793]], // LATIN CAPITAL LETTER T WITH CIRCUMFLEX BELOW
                        ['upper' => 7794, 'status' => 'C', 'lower' => [7795]], // LATIN CAPITAL LETTER U WITH DIAERESIS BELOW
                        ['upper' => 7796, 'status' => 'C', 'lower' => [7797]], // LATIN CAPITAL LETTER U WITH TILDE BELOW
                        ['upper' => 7798, 'status' => 'C', 'lower' => [7799]], // LATIN CAPITAL LETTER U WITH CIRCUMFLEX BELOW
                        ['upper' => 7800, 'status' => 'C', 'lower' => [7801]], // LATIN CAPITAL LETTER U WITH TILDE AND ACUTE
                        ['upper' => 7802, 'status' => 'C', 'lower' => [7803]], // LATIN CAPITAL LETTER U WITH MACRON AND DIAERESIS
                        ['upper' => 7804, 'status' => 'C', 'lower' => [7805]], // LATIN CAPITAL LETTER V WITH TILDE
                        ['upper' => 7806, 'status' => 'C', 'lower' => [7807]], // LATIN CAPITAL LETTER V WITH DOT BELOW
                        ['upper' => 7808, 'status' => 'C', 'lower' => [7809]], // LATIN CAPITAL LETTER W WITH GRAVE
                        ['upper' => 7810, 'status' => 'C', 'lower' => [7811]], // LATIN CAPITAL LETTER W WITH ACUTE
                        ['upper' => 7812, 'status' => 'C', 'lower' => [7813]], // LATIN CAPITAL LETTER W WITH DIAERESIS
                        ['upper' => 7814, 'status' => 'C', 'lower' => [7815]], // LATIN CAPITAL LETTER W WITH DOT ABOVE
                        ['upper' => 7816, 'status' => 'C', 'lower' => [7817]], // LATIN CAPITAL LETTER W WITH DOT BELOW
                        ['upper' => 7818, 'status' => 'C', 'lower' => [7819]], // LATIN CAPITAL LETTER X WITH DOT ABOVE
                        ['upper' => 7820, 'status' => 'C', 'lower' => [7821]], // LATIN CAPITAL LETTER X WITH DIAERESIS
                        ['upper' => 7822, 'status' => 'C', 'lower' => [7823]], // LATIN CAPITAL LETTER Y WITH DOT ABOVE
                        ['upper' => 7824, 'status' => 'C', 'lower' => [7825]], // LATIN CAPITAL LETTER Z WITH CIRCUMFLEX
                        ['upper' => 7826, 'status' => 'C', 'lower' => [7827]], // LATIN CAPITAL LETTER Z WITH DOT BELOW
                        ['upper' => 7828, 'status' => 'C', 'lower' => [7829]], // LATIN CAPITAL LETTER Z WITH LINE BELOW
                        ['upper' => 7840, 'status' => 'C', 'lower' => [7841]], // LATIN CAPITAL LETTER A WITH DOT BELOW
                        ['upper' => 7842, 'status' => 'C', 'lower' => [7843]], // LATIN CAPITAL LETTER A WITH HOOK ABOVE
                        ['upper' => 7844, 'status' => 'C', 'lower' => [7845]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND ACUTE
                        ['upper' => 7846, 'status' => 'C', 'lower' => [7847]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND GRAVE
                        ['upper' => 7848, 'status' => 'C', 'lower' => [7849]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND HOOK ABOVE
                        ['upper' => 7850, 'status' => 'C', 'lower' => [7851]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND TILDE
                        ['upper' => 7852, 'status' => 'C', 'lower' => [7853]], // LATIN CAPITAL LETTER A WITH CIRCUMFLEX AND DOT BELOW
                        ['upper' => 7854, 'status' => 'C', 'lower' => [7855]], // LATIN CAPITAL LETTER A WITH BREVE AND ACUTE
                        ['upper' => 7856, 'status' => 'C', 'lower' => [7857]], // LATIN CAPITAL LETTER A WITH BREVE AND GRAVE
                        ['upper' => 7858, 'status' => 'C', 'lower' => [7859]], // LATIN CAPITAL LETTER A WITH BREVE AND HOOK ABOVE
                        ['upper' => 7860, 'status' => 'C', 'lower' => [7861]], // LATIN CAPITAL LETTER A WITH BREVE AND TILDE
                        ['upper' => 7862, 'status' => 'C', 'lower' => [7863]], // LATIN CAPITAL LETTER A WITH BREVE AND DOT BELOW
                        ['upper' => 7864, 'status' => 'C', 'lower' => [7865]], // LATIN CAPITAL LETTER E WITH DOT BELOW
                        ['upper' => 7866, 'status' => 'C', 'lower' => [7867]], // LATIN CAPITAL LETTER E WITH HOOK ABOVE
                        ['upper' => 7868, 'status' => 'C', 'lower' => [7869]], // LATIN CAPITAL LETTER E WITH TILDE
                        ['upper' => 7870, 'status' => 'C', 'lower' => [7871]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND ACUTE
                        ['upper' => 7872, 'status' => 'C', 'lower' => [7873]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND GRAVE
                        ['upper' => 7874, 'status' => 'C', 'lower' => [7875]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND HOOK ABOVE
                        ['upper' => 7876, 'status' => 'C', 'lower' => [7877]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND TILDE
                        ['upper' => 7878, 'status' => 'C', 'lower' => [7879]], // LATIN CAPITAL LETTER E WITH CIRCUMFLEX AND DOT BELOW
                        ['upper' => 7880, 'status' => 'C', 'lower' => [7881]], // LATIN CAPITAL LETTER I WITH HOOK ABOVE
                        ['upper' => 7882, 'status' => 'C', 'lower' => [7883]], // LATIN CAPITAL LETTER I WITH DOT BELOW
                        ['upper' => 7884, 'status' => 'C', 'lower' => [7885]], // LATIN CAPITAL LETTER O WITH DOT BELOW
                        ['upper' => 7886, 'status' => 'C', 'lower' => [7887]], // LATIN CAPITAL LETTER O WITH HOOK ABOVE
                        ['upper' => 7888, 'status' => 'C', 'lower' => [7889]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND ACUTE
                        ['upper' => 7890, 'status' => 'C', 'lower' => [7891]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND GRAVE
                        ['upper' => 7892, 'status' => 'C', 'lower' => [7893]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND HOOK ABOVE
                        ['upper' => 7894, 'status' => 'C', 'lower' => [7895]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND TILDE
                        ['upper' => 7896, 'status' => 'C', 'lower' => [7897]], // LATIN CAPITAL LETTER O WITH CIRCUMFLEX AND DOT BELOW
                        ['upper' => 7898, 'status' => 'C', 'lower' => [7899]], // LATIN CAPITAL LETTER O WITH HORN AND ACUTE
                        ['upper' => 7900, 'status' => 'C', 'lower' => [7901]], // LATIN CAPITAL LETTER O WITH HORN AND GRAVE
                        ['upper' => 7902, 'status' => 'C', 'lower' => [7903]], // LATIN CAPITAL LETTER O WITH HORN AND HOOK ABOVE
                        ['upper' => 7904, 'status' => 'C', 'lower' => [7905]], // LATIN CAPITAL LETTER O WITH HORN AND TILDE
                        ['upper' => 7906, 'status' => 'C', 'lower' => [7907]], // LATIN CAPITAL LETTER O WITH HORN AND DOT BELOW
                        ['upper' => 7908, 'status' => 'C', 'lower' => [7909]], // LATIN CAPITAL LETTER U WITH DOT BELOW
                        ['upper' => 7910, 'status' => 'C', 'lower' => [7911]], // LATIN CAPITAL LETTER U WITH HOOK ABOVE
                        ['upper' => 7912, 'status' => 'C', 'lower' => [7913]], // LATIN CAPITAL LETTER U WITH HORN AND ACUTE
                        ['upper' => 7914, 'status' => 'C', 'lower' => [7915]], // LATIN CAPITAL LETTER U WITH HORN AND GRAVE
                        ['upper' => 7916, 'status' => 'C', 'lower' => [7917]], // LATIN CAPITAL LETTER U WITH HORN AND HOOK ABOVE
                        ['upper' => 7918, 'status' => 'C', 'lower' => [7919]], // LATIN CAPITAL LETTER U WITH HORN AND TILDE
                        ['upper' => 7920, 'status' => 'C', 'lower' => [7921]], // LATIN CAPITAL LETTER U WITH HORN AND DOT BELOW
                        ['upper' => 7922, 'status' => 'C', 'lower' => [7923]], // LATIN CAPITAL LETTER Y WITH GRAVE
                        ['upper' => 7924, 'status' => 'C', 'lower' => [7925]], // LATIN CAPITAL LETTER Y WITH DOT BELOW
                        ['upper' => 7926, 'status' => 'C', 'lower' => [7927]], // LATIN CAPITAL LETTER Y WITH HOOK ABOVE
                        ['upper' => 7928, 'status' => 'C', 'lower' => [7929]], // LATIN CAPITAL LETTER Y WITH TILDE
                    ];

                    break;

                case '1f00_1fff':
                    self::$_codeRanges['1f00_1fff'] = [
                        ['upper' => 7944, 'status' => 'C', 'lower' => [7936, 953]], // GREEK CAPITAL LETTER ALPHA WITH PSILI
                        ['upper' => 7945, 'status' => 'C', 'lower' => [7937]], // GREEK CAPITAL LETTER ALPHA WITH DASIA
                        ['upper' => 7946, 'status' => 'C', 'lower' => [7938]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA
                        ['upper' => 7947, 'status' => 'C', 'lower' => [7939]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA
                        ['upper' => 7948, 'status' => 'C', 'lower' => [7940]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA
                        ['upper' => 7949, 'status' => 'C', 'lower' => [7941]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA
                        ['upper' => 7950, 'status' => 'C', 'lower' => [7942]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI
                        ['upper' => 7951, 'status' => 'C', 'lower' => [7943]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI
                        ['upper' => 7960, 'status' => 'C', 'lower' => [7952]], // GREEK CAPITAL LETTER EPSILON WITH PSILI
                        ['upper' => 7961, 'status' => 'C', 'lower' => [7953]], // GREEK CAPITAL LETTER EPSILON WITH DASIA
                        ['upper' => 7962, 'status' => 'C', 'lower' => [7954]], // GREEK CAPITAL LETTER EPSILON WITH PSILI AND VARIA
                        ['upper' => 7963, 'status' => 'C', 'lower' => [7955]], // GREEK CAPITAL LETTER EPSILON WITH DASIA AND VARIA
                        ['upper' => 7964, 'status' => 'C', 'lower' => [7956]], // GREEK CAPITAL LETTER EPSILON WITH PSILI AND OXIA
                        ['upper' => 7965, 'status' => 'C', 'lower' => [7957]], // GREEK CAPITAL LETTER EPSILON WITH DASIA AND OXIA
                        ['upper' => 7976, 'status' => 'C', 'lower' => [7968]], // GREEK CAPITAL LETTER ETA WITH PSILI
                        ['upper' => 7977, 'status' => 'C', 'lower' => [7969]], // GREEK CAPITAL LETTER ETA WITH DASIA
                        ['upper' => 7978, 'status' => 'C', 'lower' => [7970]], // GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA
                        ['upper' => 7979, 'status' => 'C', 'lower' => [7971]], // GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA
                        ['upper' => 7980, 'status' => 'C', 'lower' => [7972]], // GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA
                        ['upper' => 7981, 'status' => 'C', 'lower' => [7973]], // GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA
                        ['upper' => 7982, 'status' => 'C', 'lower' => [7974]], // GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI
                        ['upper' => 7983, 'status' => 'C', 'lower' => [7975]], // GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI
                        ['upper' => 7992, 'status' => 'C', 'lower' => [7984]], // GREEK CAPITAL LETTER IOTA WITH PSILI
                        ['upper' => 7993, 'status' => 'C', 'lower' => [7985]], // GREEK CAPITAL LETTER IOTA WITH DASIA
                        ['upper' => 7994, 'status' => 'C', 'lower' => [7986]], // GREEK CAPITAL LETTER IOTA WITH PSILI AND VARIA
                        ['upper' => 7995, 'status' => 'C', 'lower' => [7987]], // GREEK CAPITAL LETTER IOTA WITH DASIA AND VARIA
                        ['upper' => 7996, 'status' => 'C', 'lower' => [7988]], // GREEK CAPITAL LETTER IOTA WITH PSILI AND OXIA
                        ['upper' => 7997, 'status' => 'C', 'lower' => [7989]], // GREEK CAPITAL LETTER IOTA WITH DASIA AND OXIA
                        ['upper' => 7998, 'status' => 'C', 'lower' => [7990]], // GREEK CAPITAL LETTER IOTA WITH PSILI AND PERISPOMENI
                        ['upper' => 7999, 'status' => 'C', 'lower' => [7991]], // GREEK CAPITAL LETTER IOTA WITH DASIA AND PERISPOMENI
                        ['upper' => 8008, 'status' => 'C', 'lower' => [8000]], // GREEK CAPITAL LETTER OMICRON WITH PSILI
                        ['upper' => 8009, 'status' => 'C', 'lower' => [8001]], // GREEK CAPITAL LETTER OMICRON WITH DASIA
                        ['upper' => 8010, 'status' => 'C', 'lower' => [8002]], // GREEK CAPITAL LETTER OMICRON WITH PSILI AND VARIA
                        ['upper' => 8011, 'status' => 'C', 'lower' => [8003]], // GREEK CAPITAL LETTER OMICRON WITH DASIA AND VARIA
                        ['upper' => 8012, 'status' => 'C', 'lower' => [8004]], // GREEK CAPITAL LETTER OMICRON WITH PSILI AND OXIA
                        ['upper' => 8013, 'status' => 'C', 'lower' => [8005]], // GREEK CAPITAL LETTER OMICRON WITH DASIA AND OXIA
                        ['upper' => 8016, 'status' => 'F', 'lower' => [965, 787]], // GREEK SMALL LETTER UPSILON WITH PSILI
                        ['upper' => 8018, 'status' => 'F', 'lower' => [965, 787, 768]], // GREEK SMALL LETTER UPSILON WITH PSILI AND VARIA
                        ['upper' => 8020, 'status' => 'F', 'lower' => [965, 787, 769]], // GREEK SMALL LETTER UPSILON WITH PSILI AND OXIA
                        ['upper' => 8022, 'status' => 'F', 'lower' => [965, 787, 834]], // GREEK SMALL LETTER UPSILON WITH PSILI AND PERISPOMENI
                        ['upper' => 8025, 'status' => 'C', 'lower' => [8017]], // GREEK CAPITAL LETTER UPSILON WITH DASIA
                        ['upper' => 8027, 'status' => 'C', 'lower' => [8019]], // GREEK CAPITAL LETTER UPSILON WITH DASIA AND VARIA
                        ['upper' => 8029, 'status' => 'C', 'lower' => [8021]], // GREEK CAPITAL LETTER UPSILON WITH DASIA AND OXIA
                        ['upper' => 8031, 'status' => 'C', 'lower' => [8023]], // GREEK CAPITAL LETTER UPSILON WITH DASIA AND PERISPOMENI
                        ['upper' => 8040, 'status' => 'C', 'lower' => [8032]], // GREEK CAPITAL LETTER OMEGA WITH PSILI
                        ['upper' => 8041, 'status' => 'C', 'lower' => [8033]], // GREEK CAPITAL LETTER OMEGA WITH DASIA
                        ['upper' => 8042, 'status' => 'C', 'lower' => [8034]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA
                        ['upper' => 8043, 'status' => 'C', 'lower' => [8035]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA
                        ['upper' => 8044, 'status' => 'C', 'lower' => [8036]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA
                        ['upper' => 8045, 'status' => 'C', 'lower' => [8037]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA
                        ['upper' => 8046, 'status' => 'C', 'lower' => [8038]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI
                        ['upper' => 8047, 'status' => 'C', 'lower' => [8039]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI
                        ['upper' => 8064, 'status' => 'F', 'lower' => [7936, 953]], // GREEK SMALL LETTER ALPHA WITH PSILI AND YPOGEGRAMMENI
                        ['upper' => 8065, 'status' => 'F', 'lower' => [7937, 953]], // GREEK SMALL LETTER ALPHA WITH DASIA AND YPOGEGRAMMENI
                        ['upper' => 8066, 'status' => 'F', 'lower' => [7938, 953]], // GREEK SMALL LETTER ALPHA WITH PSILI AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8067, 'status' => 'F', 'lower' => [7939, 953]], // GREEK SMALL LETTER ALPHA WITH DASIA AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8068, 'status' => 'F', 'lower' => [7940, 953]], // GREEK SMALL LETTER ALPHA WITH PSILI AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8069, 'status' => 'F', 'lower' => [7941, 953]], // GREEK SMALL LETTER ALPHA WITH DASIA AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8070, 'status' => 'F', 'lower' => [7942, 953]], // GREEK SMALL LETTER ALPHA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8071, 'status' => 'F', 'lower' => [7943, 953]], // GREEK SMALL LETTER ALPHA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8072, 'status' => 'F', 'lower' => [7936, 953]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8072, 'status' => 'S', 'lower' => [8064]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8073, 'status' => 'F', 'lower' => [7937, 953]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8073, 'status' => 'S', 'lower' => [8065]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8074, 'status' => 'F', 'lower' => [7938, 953]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8074, 'status' => 'S', 'lower' => [8066]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8075, 'status' => 'F', 'lower' => [7939, 953]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8075, 'status' => 'S', 'lower' => [8067]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8076, 'status' => 'F', 'lower' => [7940, 953]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8076, 'status' => 'S', 'lower' => [8068]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8077, 'status' => 'F', 'lower' => [7941, 953]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8077, 'status' => 'S', 'lower' => [8069]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8078, 'status' => 'F', 'lower' => [7942, 953]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8078, 'status' => 'S', 'lower' => [8070]], // GREEK CAPITAL LETTER ALPHA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8079, 'status' => 'F', 'lower' => [7943, 953]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8079, 'status' => 'S', 'lower' => [8071]], // GREEK CAPITAL LETTER ALPHA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8080, 'status' => 'F', 'lower' => [7968, 953]], // GREEK SMALL LETTER ETA WITH PSILI AND YPOGEGRAMMENI
                        ['upper' => 8081, 'status' => 'F', 'lower' => [7969, 953]], // GREEK SMALL LETTER ETA WITH DASIA AND YPOGEGRAMMENI
                        ['upper' => 8082, 'status' => 'F', 'lower' => [7970, 953]], // GREEK SMALL LETTER ETA WITH PSILI AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8083, 'status' => 'F', 'lower' => [7971, 953]], // GREEK SMALL LETTER ETA WITH DASIA AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8084, 'status' => 'F', 'lower' => [7972, 953]], // GREEK SMALL LETTER ETA WITH PSILI AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8085, 'status' => 'F', 'lower' => [7973, 953]], // GREEK SMALL LETTER ETA WITH DASIA AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8086, 'status' => 'F', 'lower' => [7974, 953]], // GREEK SMALL LETTER ETA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8087, 'status' => 'F', 'lower' => [7975, 953]], // GREEK SMALL LETTER ETA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8088, 'status' => 'F', 'lower' => [7968, 953]], // GREEK CAPITAL LETTER ETA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8088, 'status' => 'S', 'lower' => [8080]], // GREEK CAPITAL LETTER ETA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8089, 'status' => 'F', 'lower' => [7969, 953]], // GREEK CAPITAL LETTER ETA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8089, 'status' => 'S', 'lower' => [8081]], // GREEK CAPITAL LETTER ETA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8090, 'status' => 'F', 'lower' => [7970, 953]], // GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8090, 'status' => 'S', 'lower' => [8082]], // GREEK CAPITAL LETTER ETA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8091, 'status' => 'F', 'lower' => [7971, 953]], // GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8091, 'status' => 'S', 'lower' => [8083]], // GREEK CAPITAL LETTER ETA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8092, 'status' => 'F', 'lower' => [7972, 953]], // GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8092, 'status' => 'S', 'lower' => [8084]], // GREEK CAPITAL LETTER ETA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8093, 'status' => 'F', 'lower' => [7973, 953]], // GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8093, 'status' => 'S', 'lower' => [8085]], // GREEK CAPITAL LETTER ETA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8094, 'status' => 'F', 'lower' => [7974, 953]], // GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8094, 'status' => 'S', 'lower' => [8086]], // GREEK CAPITAL LETTER ETA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8095, 'status' => 'F', 'lower' => [7975, 953]], // GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8095, 'status' => 'S', 'lower' => [8087]], // GREEK CAPITAL LETTER ETA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8096, 'status' => 'F', 'lower' => [8032, 953]], // GREEK SMALL LETTER OMEGA WITH PSILI AND YPOGEGRAMMENI
                        ['upper' => 8097, 'status' => 'F', 'lower' => [8033, 953]], // GREEK SMALL LETTER OMEGA WITH DASIA AND YPOGEGRAMMENI
                        ['upper' => 8098, 'status' => 'F', 'lower' => [8034, 953]], // GREEK SMALL LETTER OMEGA WITH PSILI AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8099, 'status' => 'F', 'lower' => [8035, 953]], // GREEK SMALL LETTER OMEGA WITH DASIA AND VARIA AND YPOGEGRAMMENI
                        ['upper' => 8100, 'status' => 'F', 'lower' => [8036, 953]], // GREEK SMALL LETTER OMEGA WITH PSILI AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8101, 'status' => 'F', 'lower' => [8037, 953]], // GREEK SMALL LETTER OMEGA WITH DASIA AND OXIA AND YPOGEGRAMMENI
                        ['upper' => 8102, 'status' => 'F', 'lower' => [8038, 953]], // GREEK SMALL LETTER OMEGA WITH PSILI AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8103, 'status' => 'F', 'lower' => [8039, 953]], // GREEK SMALL LETTER OMEGA WITH DASIA AND PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8104, 'status' => 'F', 'lower' => [8032, 953]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8104, 'status' => 'S', 'lower' => [8096]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND PROSGEGRAMMENI
                        ['upper' => 8105, 'status' => 'F', 'lower' => [8033, 953]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8105, 'status' => 'S', 'lower' => [8097]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND PROSGEGRAMMENI
                        ['upper' => 8106, 'status' => 'F', 'lower' => [8034, 953]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8106, 'status' => 'S', 'lower' => [8098]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8107, 'status' => 'F', 'lower' => [8035, 953]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8107, 'status' => 'S', 'lower' => [8099]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND VARIA AND PROSGEGRAMMENI
                        ['upper' => 8108, 'status' => 'F', 'lower' => [8036, 953]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8108, 'status' => 'S', 'lower' => [8100]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8109, 'status' => 'F', 'lower' => [8037, 953]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8109, 'status' => 'S', 'lower' => [8101]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND OXIA AND PROSGEGRAMMENI
                        ['upper' => 8110, 'status' => 'F', 'lower' => [8038, 953]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8110, 'status' => 'S', 'lower' => [8102]], // GREEK CAPITAL LETTER OMEGA WITH PSILI AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8111, 'status' => 'F', 'lower' => [8039, 953]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8111, 'status' => 'S', 'lower' => [8103]], // GREEK CAPITAL LETTER OMEGA WITH DASIA AND PERISPOMENI AND PROSGEGRAMMENI
                        ['upper' => 8114, 'status' => 'F', 'lower' => [8048, 953]], // GREEK SMALL LETTER ALPHA WITH VARIA AND YPOGEGRAMMENI
                        ['upper' => 8115, 'status' => 'F', 'lower' => [945, 953]], // GREEK SMALL LETTER ALPHA WITH YPOGEGRAMMENI
                        ['upper' => 8116, 'status' => 'F', 'lower' => [940, 953]], // GREEK SMALL LETTER ALPHA WITH OXIA AND YPOGEGRAMMENI
                        ['upper' => 8118, 'status' => 'F', 'lower' => [945, 834]], // GREEK SMALL LETTER ALPHA WITH PERISPOMENI
                        ['upper' => 8119, 'status' => 'F', 'lower' => [945, 834, 953]], // GREEK SMALL LETTER ALPHA WITH PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8120, 'status' => 'C', 'lower' => [8112]], // GREEK CAPITAL LETTER ALPHA WITH VRACHY
                        ['upper' => 8121, 'status' => 'C', 'lower' => [8113]], // GREEK CAPITAL LETTER ALPHA WITH MACRON
                        ['upper' => 8122, 'status' => 'C', 'lower' => [8048]], // GREEK CAPITAL LETTER ALPHA WITH VARIA
                        ['upper' => 8123, 'status' => 'C', 'lower' => [8049]], // GREEK CAPITAL LETTER ALPHA WITH OXIA
                        ['upper' => 8124, 'status' => 'F', 'lower' => [945, 953]], // GREEK CAPITAL LETTER ALPHA WITH PROSGEGRAMMENI
                        ['upper' => 8124, 'status' => 'S', 'lower' => [8115]], // GREEK CAPITAL LETTER ALPHA WITH PROSGEGRAMMENI
                        ['upper' => 8126, 'status' => 'C', 'lower' => [953]], // GREEK PROSGEGRAMMENI
                        ['upper' => 8130, 'status' => 'F', 'lower' => [8052, 953]], // GREEK SMALL LETTER ETA WITH VARIA AND YPOGEGRAMMENI
                        ['upper' => 8131, 'status' => 'F', 'lower' => [951, 953]], // GREEK SMALL LETTER ETA WITH YPOGEGRAMMENI
                        ['upper' => 8132, 'status' => 'F', 'lower' => [942, 953]], // GREEK SMALL LETTER ETA WITH OXIA AND YPOGEGRAMMENI
                        ['upper' => 8134, 'status' => 'F', 'lower' => [951, 834]], // GREEK SMALL LETTER ETA WITH PERISPOMENI
                        ['upper' => 8135, 'status' => 'F', 'lower' => [951, 834, 953]], // GREEK SMALL LETTER ETA WITH PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8136, 'status' => 'C', 'lower' => [8050]], // GREEK CAPITAL LETTER EPSILON WITH VARIA
                        ['upper' => 8137, 'status' => 'C', 'lower' => [8051]], // GREEK CAPITAL LETTER EPSILON WITH OXIA
                        ['upper' => 8138, 'status' => 'C', 'lower' => [8052]], // GREEK CAPITAL LETTER ETA WITH VARIA
                        ['upper' => 8139, 'status' => 'C', 'lower' => [8053]], // GREEK CAPITAL LETTER ETA WITH OXIA
                        ['upper' => 8140, 'status' => 'F', 'lower' => [951, 953]], // GREEK CAPITAL LETTER ETA WITH PROSGEGRAMMENI
                        ['upper' => 8140, 'status' => 'S', 'lower' => [8131]], // GREEK CAPITAL LETTER ETA WITH PROSGEGRAMMENI
                        ['upper' => 8146, 'status' => 'F', 'lower' => [953, 776, 768]], // GREEK SMALL LETTER IOTA WITH DIALYTIKA AND VARIA
                        ['upper' => 8147, 'status' => 'F', 'lower' => [953, 776, 769]], // GREEK SMALL LETTER IOTA WITH DIALYTIKA AND OXIA
                        ['upper' => 8150, 'status' => 'F', 'lower' => [953, 834]], // GREEK SMALL LETTER IOTA WITH PERISPOMENI
                        ['upper' => 8151, 'status' => 'F', 'lower' => [953, 776, 834]], // GREEK SMALL LETTER IOTA WITH DIALYTIKA AND PERISPOMENI
                        ['upper' => 8152, 'status' => 'C', 'lower' => [8144]], // GREEK CAPITAL LETTER IOTA WITH VRACHY
                        ['upper' => 8153, 'status' => 'C', 'lower' => [8145]], // GREEK CAPITAL LETTER IOTA WITH MACRON
                        ['upper' => 8154, 'status' => 'C', 'lower' => [8054]], // GREEK CAPITAL LETTER IOTA WITH VARIA
                        ['upper' => 8155, 'status' => 'C', 'lower' => [8055]], // GREEK CAPITAL LETTER IOTA WITH OXIA
                        ['upper' => 8162, 'status' => 'F', 'lower' => [965, 776, 768]], // GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND VARIA
                        ['upper' => 8163, 'status' => 'F', 'lower' => [965, 776, 769]], // GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND OXIA
                        ['upper' => 8164, 'status' => 'F', 'lower' => [961, 787]], // GREEK SMALL LETTER RHO WITH PSILI
                        ['upper' => 8166, 'status' => 'F', 'lower' => [965, 834]], // GREEK SMALL LETTER UPSILON WITH PERISPOMENI
                        ['upper' => 8167, 'status' => 'F', 'lower' => [965, 776, 834]], // GREEK SMALL LETTER UPSILON WITH DIALYTIKA AND PERISPOMENI
                        ['upper' => 8168, 'status' => 'C', 'lower' => [8160]], // GREEK CAPITAL LETTER UPSILON WITH VRACHY
                        ['upper' => 8169, 'status' => 'C', 'lower' => [8161]], // GREEK CAPITAL LETTER UPSILON WITH MACRON
                        ['upper' => 8170, 'status' => 'C', 'lower' => [8058]], // GREEK CAPITAL LETTER UPSILON WITH VARIA
                        ['upper' => 8171, 'status' => 'C', 'lower' => [8059]], // GREEK CAPITAL LETTER UPSILON WITH OXIA
                        ['upper' => 8172, 'status' => 'C', 'lower' => [8165]], // GREEK CAPITAL LETTER RHO WITH DASIA
                        ['upper' => 8178, 'status' => 'F', 'lower' => [8060, 953]], // GREEK SMALL LETTER OMEGA WITH VARIA AND YPOGEGRAMMENI
                        ['upper' => 8179, 'status' => 'F', 'lower' => [969, 953]], // GREEK SMALL LETTER OMEGA WITH YPOGEGRAMMENI
                        ['upper' => 8180, 'status' => 'F', 'lower' => [974, 953]], // GREEK SMALL LETTER OMEGA WITH OXIA AND YPOGEGRAMMENI
                        ['upper' => 8182, 'status' => 'F', 'lower' => [969, 834]], // GREEK SMALL LETTER OMEGA WITH PERISPOMENI
                        ['upper' => 8183, 'status' => 'F', 'lower' => [969, 834, 953]], // GREEK SMALL LETTER OMEGA WITH PERISPOMENI AND YPOGEGRAMMENI
                        ['upper' => 8184, 'status' => 'C', 'lower' => [8056]], // GREEK CAPITAL LETTER OMICRON WITH VARIA
                        ['upper' => 8185, 'status' => 'C', 'lower' => [8057]], // GREEK CAPITAL LETTER OMICRON WITH OXIA
                        ['upper' => 8186, 'status' => 'C', 'lower' => [8060]], // GREEK CAPITAL LETTER OMEGA WITH VARIA
                        ['upper' => 8187, 'status' => 'C', 'lower' => [8061]], // GREEK CAPITAL LETTER OMEGA WITH OXIA
                        ['upper' => 8188, 'status' => 'F', 'lower' => [969, 953]], // GREEK CAPITAL LETTER OMEGA WITH PROSGEGRAMMENI
                        ['upper' => 8188, 'status' => 'S', 'lower' => [8179]], // GREEK CAPITAL LETTER OMEGA WITH PROSGEGRAMMENI
                    ];

                    break;

                case '2100_214f':
                    self::$_codeRanges['2100_214f'] = [
                        ['upper' => 8486, 'status' => 'C', 'lower' => [969]], // OHM SIGN
                        ['upper' => 8490, 'status' => 'C', 'lower' => [107]], // KELVIN SIGN
                        ['upper' => 8491, 'status' => 'C', 'lower' => [229]], // ANGSTROM SIGN
                        ['upper' => 8498, 'status' => 'C', 'lower' => [8526]], // TURNED CAPITAL F
                    ];

                    break;

                case '2150_218f':
                    self::$_codeRanges['2150_218f'] = [
                        ['upper' => 8544, 'status' => 'C', 'lower' => [8560]], // ROMAN NUMERAL ONE
                        ['upper' => 8545, 'status' => 'C', 'lower' => [8561]], // ROMAN NUMERAL TWO
                        ['upper' => 8546, 'status' => 'C', 'lower' => [8562]], // ROMAN NUMERAL THREE
                        ['upper' => 8547, 'status' => 'C', 'lower' => [8563]], // ROMAN NUMERAL FOUR
                        ['upper' => 8548, 'status' => 'C', 'lower' => [8564]], // ROMAN NUMERAL FIVE
                        ['upper' => 8549, 'status' => 'C', 'lower' => [8565]], // ROMAN NUMERAL SIX
                        ['upper' => 8550, 'status' => 'C', 'lower' => [8566]], // ROMAN NUMERAL SEVEN
                        ['upper' => 8551, 'status' => 'C', 'lower' => [8567]], // ROMAN NUMERAL EIGHT
                        ['upper' => 8552, 'status' => 'C', 'lower' => [8568]], // ROMAN NUMERAL NINE
                        ['upper' => 8553, 'status' => 'C', 'lower' => [8569]], // ROMAN NUMERAL TEN
                        ['upper' => 8554, 'status' => 'C', 'lower' => [8570]], // ROMAN NUMERAL ELEVEN
                        ['upper' => 8555, 'status' => 'C', 'lower' => [8571]], // ROMAN NUMERAL TWELVE
                        ['upper' => 8556, 'status' => 'C', 'lower' => [8572]], // ROMAN NUMERAL FIFTY
                        ['upper' => 8557, 'status' => 'C', 'lower' => [8573]], // ROMAN NUMERAL ONE HUNDRED
                        ['upper' => 8558, 'status' => 'C', 'lower' => [8574]], // ROMAN NUMERAL FIVE HUNDRED
                        ['upper' => 8559, 'status' => 'C', 'lower' => [8575]], // ROMAN NUMERAL ONE THOUSAND
                        ['upper' => 8579, 'status' => 'C', 'lower' => [8580]], // ROMAN NUMERAL REVERSED ONE HUNDRED
                    ];

                    break;

                case '2460_24ff':
                    self::$_codeRanges['2460_24ff'] = [
                        ['upper' => 9398, 'status' => 'C', 'lower' => [9424]], // CIRCLED LATIN CAPITAL LETTER A
                        ['upper' => 9399, 'status' => 'C', 'lower' => [9425]], // CIRCLED LATIN CAPITAL LETTER B
                        ['upper' => 9400, 'status' => 'C', 'lower' => [9426]], // CIRCLED LATIN CAPITAL LETTER C
                        ['upper' => 9401, 'status' => 'C', 'lower' => [9427]], // CIRCLED LATIN CAPITAL LETTER D
                        ['upper' => 9402, 'status' => 'C', 'lower' => [9428]], // CIRCLED LATIN CAPITAL LETTER E
                        ['upper' => 9403, 'status' => 'C', 'lower' => [9429]], // CIRCLED LATIN CAPITAL LETTER F
                        ['upper' => 9404, 'status' => 'C', 'lower' => [9430]], // CIRCLED LATIN CAPITAL LETTER G
                        ['upper' => 9405, 'status' => 'C', 'lower' => [9431]], // CIRCLED LATIN CAPITAL LETTER H
                        ['upper' => 9406, 'status' => 'C', 'lower' => [9432]], // CIRCLED LATIN CAPITAL LETTER I
                        ['upper' => 9407, 'status' => 'C', 'lower' => [9433]], // CIRCLED LATIN CAPITAL LETTER J
                        ['upper' => 9408, 'status' => 'C', 'lower' => [9434]], // CIRCLED LATIN CAPITAL LETTER K
                        ['upper' => 9409, 'status' => 'C', 'lower' => [9435]], // CIRCLED LATIN CAPITAL LETTER L
                        ['upper' => 9410, 'status' => 'C', 'lower' => [9436]], // CIRCLED LATIN CAPITAL LETTER M
                        ['upper' => 9411, 'status' => 'C', 'lower' => [9437]], // CIRCLED LATIN CAPITAL LETTER N
                        ['upper' => 9412, 'status' => 'C', 'lower' => [9438]], // CIRCLED LATIN CAPITAL LETTER O
                        ['upper' => 9413, 'status' => 'C', 'lower' => [9439]], // CIRCLED LATIN CAPITAL LETTER P
                        ['upper' => 9414, 'status' => 'C', 'lower' => [9440]], // CIRCLED LATIN CAPITAL LETTER Q
                        ['upper' => 9415, 'status' => 'C', 'lower' => [9441]], // CIRCLED LATIN CAPITAL LETTER R
                        ['upper' => 9416, 'status' => 'C', 'lower' => [9442]], // CIRCLED LATIN CAPITAL LETTER S
                        ['upper' => 9417, 'status' => 'C', 'lower' => [9443]], // CIRCLED LATIN CAPITAL LETTER T
                        ['upper' => 9418, 'status' => 'C', 'lower' => [9444]], // CIRCLED LATIN CAPITAL LETTER U
                        ['upper' => 9419, 'status' => 'C', 'lower' => [9445]], // CIRCLED LATIN CAPITAL LETTER V
                        ['upper' => 9420, 'status' => 'C', 'lower' => [9446]], // CIRCLED LATIN CAPITAL LETTER W
                        ['upper' => 9421, 'status' => 'C', 'lower' => [9447]], // CIRCLED LATIN CAPITAL LETTER X
                        ['upper' => 9422, 'status' => 'C', 'lower' => [9448]], // CIRCLED LATIN CAPITAL LETTER Y
                        ['upper' => 9423, 'status' => 'C', 'lower' => [9449]], // CIRCLED LATIN CAPITAL LETTER Z
                    ];

                    break;

                case '2c00_2c5f':
                    self::$_codeRanges['2c00_2c5f'] = [
                        ['upper' => 11264, 'status' => 'C', 'lower' => [11312]], // GLAGOLITIC CAPITAL LETTER AZU
                        ['upper' => 11265, 'status' => 'C', 'lower' => [11313]], // GLAGOLITIC CAPITAL LETTER BUKY
                        ['upper' => 11266, 'status' => 'C', 'lower' => [11314]], // GLAGOLITIC CAPITAL LETTER VEDE
                        ['upper' => 11267, 'status' => 'C', 'lower' => [11315]], // GLAGOLITIC CAPITAL LETTER GLAGOLI
                        ['upper' => 11268, 'status' => 'C', 'lower' => [11316]], // GLAGOLITIC CAPITAL LETTER DOBRO
                        ['upper' => 11269, 'status' => 'C', 'lower' => [11317]], // GLAGOLITIC CAPITAL LETTER YESTU
                        ['upper' => 11270, 'status' => 'C', 'lower' => [11318]], // GLAGOLITIC CAPITAL LETTER ZHIVETE
                        ['upper' => 11271, 'status' => 'C', 'lower' => [11319]], // GLAGOLITIC CAPITAL LETTER DZELO
                        ['upper' => 11272, 'status' => 'C', 'lower' => [11320]], // GLAGOLITIC CAPITAL LETTER ZEMLJA
                        ['upper' => 11273, 'status' => 'C', 'lower' => [11321]], // GLAGOLITIC CAPITAL LETTER IZHE
                        ['upper' => 11274, 'status' => 'C', 'lower' => [11322]], // GLAGOLITIC CAPITAL LETTER INITIAL IZHE
                        ['upper' => 11275, 'status' => 'C', 'lower' => [11323]], // GLAGOLITIC CAPITAL LETTER I
                        ['upper' => 11276, 'status' => 'C', 'lower' => [11324]], // GLAGOLITIC CAPITAL LETTER DJERVI
                        ['upper' => 11277, 'status' => 'C', 'lower' => [11325]], // GLAGOLITIC CAPITAL LETTER KAKO
                        ['upper' => 11278, 'status' => 'C', 'lower' => [11326]], // GLAGOLITIC CAPITAL LETTER LJUDIJE
                        ['upper' => 11279, 'status' => 'C', 'lower' => [11327]], // GLAGOLITIC CAPITAL LETTER MYSLITE
                        ['upper' => 11280, 'status' => 'C', 'lower' => [11328]], // GLAGOLITIC CAPITAL LETTER NASHI
                        ['upper' => 11281, 'status' => 'C', 'lower' => [11329]], // GLAGOLITIC CAPITAL LETTER ONU
                        ['upper' => 11282, 'status' => 'C', 'lower' => [11330]], // GLAGOLITIC CAPITAL LETTER POKOJI
                        ['upper' => 11283, 'status' => 'C', 'lower' => [11331]], // GLAGOLITIC CAPITAL LETTER RITSI
                        ['upper' => 11284, 'status' => 'C', 'lower' => [11332]], // GLAGOLITIC CAPITAL LETTER SLOVO
                        ['upper' => 11285, 'status' => 'C', 'lower' => [11333]], // GLAGOLITIC CAPITAL LETTER TVRIDO
                        ['upper' => 11286, 'status' => 'C', 'lower' => [11334]], // GLAGOLITIC CAPITAL LETTER UKU
                        ['upper' => 11287, 'status' => 'C', 'lower' => [11335]], // GLAGOLITIC CAPITAL LETTER FRITU
                        ['upper' => 11288, 'status' => 'C', 'lower' => [11336]], // GLAGOLITIC CAPITAL LETTER HERU
                        ['upper' => 11289, 'status' => 'C', 'lower' => [11337]], // GLAGOLITIC CAPITAL LETTER OTU
                        ['upper' => 11290, 'status' => 'C', 'lower' => [11338]], // GLAGOLITIC CAPITAL LETTER PE
                        ['upper' => 11291, 'status' => 'C', 'lower' => [11339]], // GLAGOLITIC CAPITAL LETTER SHTA
                        ['upper' => 11292, 'status' => 'C', 'lower' => [11340]], // GLAGOLITIC CAPITAL LETTER TSI
                        ['upper' => 11293, 'status' => 'C', 'lower' => [11341]], // GLAGOLITIC CAPITAL LETTER CHRIVI
                        ['upper' => 11294, 'status' => 'C', 'lower' => [11342]], // GLAGOLITIC CAPITAL LETTER SHA
                        ['upper' => 11295, 'status' => 'C', 'lower' => [11343]], // GLAGOLITIC CAPITAL LETTER YERU
                        ['upper' => 11296, 'status' => 'C', 'lower' => [11344]], // GLAGOLITIC CAPITAL LETTER YERI
                        ['upper' => 11297, 'status' => 'C', 'lower' => [11345]], // GLAGOLITIC CAPITAL LETTER YATI
                        ['upper' => 11298, 'status' => 'C', 'lower' => [11346]], // GLAGOLITIC CAPITAL LETTER SPIDERY HA
                        ['upper' => 11299, 'status' => 'C', 'lower' => [11347]], // GLAGOLITIC CAPITAL LETTER YU
                        ['upper' => 11300, 'status' => 'C', 'lower' => [11348]], // GLAGOLITIC CAPITAL LETTER SMALL YUS
                        ['upper' => 11301, 'status' => 'C', 'lower' => [11349]], // GLAGOLITIC CAPITAL LETTER SMALL YUS WITH TAIL
                        ['upper' => 11302, 'status' => 'C', 'lower' => [11350]], // GLAGOLITIC CAPITAL LETTER YO
                        ['upper' => 11303, 'status' => 'C', 'lower' => [11351]], // GLAGOLITIC CAPITAL LETTER IOTATED SMALL YUS
                        ['upper' => 11304, 'status' => 'C', 'lower' => [11352]], // GLAGOLITIC CAPITAL LETTER BIG YUS
                        ['upper' => 11305, 'status' => 'C', 'lower' => [11353]], // GLAGOLITIC CAPITAL LETTER IOTATED BIG YUS
                        ['upper' => 11306, 'status' => 'C', 'lower' => [11354]], // GLAGOLITIC CAPITAL LETTER FITA
                        ['upper' => 11307, 'status' => 'C', 'lower' => [11355]], // GLAGOLITIC CAPITAL LETTER IZHITSA
                        ['upper' => 11308, 'status' => 'C', 'lower' => [11356]], // GLAGOLITIC CAPITAL LETTER SHTAPIC
                        ['upper' => 11309, 'status' => 'C', 'lower' => [11357]], // GLAGOLITIC CAPITAL LETTER TROKUTASTI A
                        ['upper' => 11310, 'status' => 'C', 'lower' => [11358]], // GLAGOLITIC CAPITAL LETTER LATINATE MYSLITE
                    ];

                    break;

                case '2c60_2c7f':
                    self::$_codeRanges['2c60_2c7f'] = [
                        ['upper' => 11360, 'status' => 'C', 'lower' => [11361]], // LATIN CAPITAL LETTER L WITH DOUBLE BAR
                        ['upper' => 11362, 'status' => 'C', 'lower' => [619]], // LATIN CAPITAL LETTER L WITH MIDDLE TILDE
                        ['upper' => 11363, 'status' => 'C', 'lower' => [7549]], // LATIN CAPITAL LETTER P WITH STROKE
                        ['upper' => 11364, 'status' => 'C', 'lower' => [637]], // LATIN CAPITAL LETTER R WITH TAIL
                        ['upper' => 11367, 'status' => 'C', 'lower' => [11368]], // LATIN CAPITAL LETTER H WITH DESCENDER
                        ['upper' => 11369, 'status' => 'C', 'lower' => [11370]], // LATIN CAPITAL LETTER K WITH DESCENDER
                        ['upper' => 11371, 'status' => 'C', 'lower' => [11372]], // LATIN CAPITAL LETTER Z WITH DESCENDER
                        ['upper' => 11381, 'status' => 'C', 'lower' => [11382]], // LATIN CAPITAL LETTER HALF H
                    ];

                    break;

                case '2c80_2cff':
                    self::$_codeRanges['2c80_2cff'] = [
                        ['upper' => 11392, 'status' => 'C', 'lower' => [11393]], // COPTIC CAPITAL LETTER ALFA
                        ['upper' => 11394, 'status' => 'C', 'lower' => [11395]], // COPTIC CAPITAL LETTER VIDA
                        ['upper' => 11396, 'status' => 'C', 'lower' => [11397]], // COPTIC CAPITAL LETTER GAMMA
                        ['upper' => 11398, 'status' => 'C', 'lower' => [11399]], // COPTIC CAPITAL LETTER DALDA
                        ['upper' => 11400, 'status' => 'C', 'lower' => [11401]], // COPTIC CAPITAL LETTER EIE
                        ['upper' => 11402, 'status' => 'C', 'lower' => [11403]], // COPTIC CAPITAL LETTER SOU
                        ['upper' => 11404, 'status' => 'C', 'lower' => [11405]], // COPTIC CAPITAL LETTER ZATA
                        ['upper' => 11406, 'status' => 'C', 'lower' => [11407]], // COPTIC CAPITAL LETTER HATE
                        ['upper' => 11408, 'status' => 'C', 'lower' => [11409]], // COPTIC CAPITAL LETTER THETHE
                        ['upper' => 11410, 'status' => 'C', 'lower' => [11411]], // COPTIC CAPITAL LETTER IAUDA
                        ['upper' => 11412, 'status' => 'C', 'lower' => [11413]], // COPTIC CAPITAL LETTER KAPA
                        ['upper' => 11414, 'status' => 'C', 'lower' => [11415]], // COPTIC CAPITAL LETTER LAULA
                        ['upper' => 11416, 'status' => 'C', 'lower' => [11417]], // COPTIC CAPITAL LETTER MI
                        ['upper' => 11418, 'status' => 'C', 'lower' => [11419]], // COPTIC CAPITAL LETTER NI
                        ['upper' => 11420, 'status' => 'C', 'lower' => [11421]], // COPTIC CAPITAL LETTER KSI
                        ['upper' => 11422, 'status' => 'C', 'lower' => [11423]], // COPTIC CAPITAL LETTER O
                        ['upper' => 11424, 'status' => 'C', 'lower' => [11425]], // COPTIC CAPITAL LETTER PI
                        ['upper' => 11426, 'status' => 'C', 'lower' => [11427]], // COPTIC CAPITAL LETTER RO
                        ['upper' => 11428, 'status' => 'C', 'lower' => [11429]], // COPTIC CAPITAL LETTER SIMA
                        ['upper' => 11430, 'status' => 'C', 'lower' => [11431]], // COPTIC CAPITAL LETTER TAU
                        ['upper' => 11432, 'status' => 'C', 'lower' => [11433]], // COPTIC CAPITAL LETTER UA
                        ['upper' => 11434, 'status' => 'C', 'lower' => [11435]], // COPTIC CAPITAL LETTER FI
                        ['upper' => 11436, 'status' => 'C', 'lower' => [11437]], // COPTIC CAPITAL LETTER KHI
                        ['upper' => 11438, 'status' => 'C', 'lower' => [11439]], // COPTIC CAPITAL LETTER PSI
                        ['upper' => 11440, 'status' => 'C', 'lower' => [11441]], // COPTIC CAPITAL LETTER OOU
                        ['upper' => 11442, 'status' => 'C', 'lower' => [11443]], // COPTIC CAPITAL LETTER DIALECT-P ALEF
                        ['upper' => 11444, 'status' => 'C', 'lower' => [11445]], // COPTIC CAPITAL LETTER OLD COPTIC AIN
                        ['upper' => 11446, 'status' => 'C', 'lower' => [11447]], // COPTIC CAPITAL LETTER CRYPTOGRAMMIC EIE
                        ['upper' => 11448, 'status' => 'C', 'lower' => [11449]], // COPTIC CAPITAL LETTER DIALECT-P KAPA
                        ['upper' => 11450, 'status' => 'C', 'lower' => [11451]], // COPTIC CAPITAL LETTER DIALECT-P NI
                        ['upper' => 11452, 'status' => 'C', 'lower' => [11453]], // COPTIC CAPITAL LETTER CRYPTOGRAMMIC NI
                        ['upper' => 11454, 'status' => 'C', 'lower' => [11455]], // COPTIC CAPITAL LETTER OLD COPTIC OOU
                        ['upper' => 11456, 'status' => 'C', 'lower' => [11457]], // COPTIC CAPITAL LETTER SAMPI
                        ['upper' => 11458, 'status' => 'C', 'lower' => [11459]], // COPTIC CAPITAL LETTER CROSSED SHEI
                        ['upper' => 11460, 'status' => 'C', 'lower' => [11461]], // COPTIC CAPITAL LETTER OLD COPTIC SHEI
                        ['upper' => 11462, 'status' => 'C', 'lower' => [11463]], // COPTIC CAPITAL LETTER OLD COPTIC ESH
                        ['upper' => 11464, 'status' => 'C', 'lower' => [11465]], // COPTIC CAPITAL LETTER AKHMIMIC KHEI
                        ['upper' => 11466, 'status' => 'C', 'lower' => [11467]], // COPTIC CAPITAL LETTER DIALECT-P HORI
                        ['upper' => 11468, 'status' => 'C', 'lower' => [11469]], // COPTIC CAPITAL LETTER OLD COPTIC HORI
                        ['upper' => 11470, 'status' => 'C', 'lower' => [11471]], // COPTIC CAPITAL LETTER OLD COPTIC HA
                        ['upper' => 11472, 'status' => 'C', 'lower' => [11473]], // COPTIC CAPITAL LETTER L-SHAPED HA
                        ['upper' => 11474, 'status' => 'C', 'lower' => [11475]], // COPTIC CAPITAL LETTER OLD COPTIC HEI
                        ['upper' => 11476, 'status' => 'C', 'lower' => [11477]], // COPTIC CAPITAL LETTER OLD COPTIC HAT
                        ['upper' => 11478, 'status' => 'C', 'lower' => [11479]], // COPTIC CAPITAL LETTER OLD COPTIC GANGIA
                        ['upper' => 11480, 'status' => 'C', 'lower' => [11481]], // COPTIC CAPITAL LETTER OLD COPTIC DJA
                        ['upper' => 11482, 'status' => 'C', 'lower' => [11483]], // COPTIC CAPITAL LETTER OLD COPTIC SHIMA
                        ['upper' => 11484, 'status' => 'C', 'lower' => [11485]], // COPTIC CAPITAL LETTER OLD NUBIAN SHIMA
                        ['upper' => 11486, 'status' => 'C', 'lower' => [11487]], // COPTIC CAPITAL LETTER OLD NUBIAN NGI
                        ['upper' => 11488, 'status' => 'C', 'lower' => [11489]], // COPTIC CAPITAL LETTER OLD NUBIAN NYI
                        ['upper' => 11490, 'status' => 'C', 'lower' => [11491]], // COPTIC CAPITAL LETTER OLD NUBIAN WAU
                    ];

                    break;

                case 'ff00_ffef':
                    self::$_codeRanges['ff00_ffef'] = [
                        ['upper' => 65313, 'status' => 'C', 'lower' => [65345]], // FULLWIDTH LATIN CAPITAL LETTER A
                        ['upper' => 65314, 'status' => 'C', 'lower' => [65346]], // FULLWIDTH LATIN CAPITAL LETTER B
                        ['upper' => 65315, 'status' => 'C', 'lower' => [65347]], // FULLWIDTH LATIN CAPITAL LETTER C
                        ['upper' => 65316, 'status' => 'C', 'lower' => [65348]], // FULLWIDTH LATIN CAPITAL LETTER D
                        ['upper' => 65317, 'status' => 'C', 'lower' => [65349]], // FULLWIDTH LATIN CAPITAL LETTER E
                        ['upper' => 65318, 'status' => 'C', 'lower' => [65350]], // FULLWIDTH LATIN CAPITAL LETTER F
                        ['upper' => 65319, 'status' => 'C', 'lower' => [65351]], // FULLWIDTH LATIN CAPITAL LETTER G
                        ['upper' => 65320, 'status' => 'C', 'lower' => [65352]], // FULLWIDTH LATIN CAPITAL LETTER H
                        ['upper' => 65321, 'status' => 'C', 'lower' => [65353]], // FULLWIDTH LATIN CAPITAL LETTER I
                        ['upper' => 65322, 'status' => 'C', 'lower' => [65354]], // FULLWIDTH LATIN CAPITAL LETTER J
                        ['upper' => 65323, 'status' => 'C', 'lower' => [65355]], // FULLWIDTH LATIN CAPITAL LETTER K
                        ['upper' => 65324, 'status' => 'C', 'lower' => [65356]], // FULLWIDTH LATIN CAPITAL LETTER L
                        ['upper' => 65325, 'status' => 'C', 'lower' => [65357]], // FULLWIDTH LATIN CAPITAL LETTER M
                        ['upper' => 65326, 'status' => 'C', 'lower' => [65358]], // FULLWIDTH LATIN CAPITAL LETTER N
                        ['upper' => 65327, 'status' => 'C', 'lower' => [65359]], // FULLWIDTH LATIN CAPITAL LETTER O
                        ['upper' => 65328, 'status' => 'C', 'lower' => [65360]], // FULLWIDTH LATIN CAPITAL LETTER P
                        ['upper' => 65329, 'status' => 'C', 'lower' => [65361]], // FULLWIDTH LATIN CAPITAL LETTER Q
                        ['upper' => 65330, 'status' => 'C', 'lower' => [65362]], // FULLWIDTH LATIN CAPITAL LETTER R
                        ['upper' => 65331, 'status' => 'C', 'lower' => [65363]], // FULLWIDTH LATIN CAPITAL LETTER S
                        ['upper' => 65332, 'status' => 'C', 'lower' => [65364]], // FULLWIDTH LATIN CAPITAL LETTER T
                        ['upper' => 65333, 'status' => 'C', 'lower' => [65365]], // FULLWIDTH LATIN CAPITAL LETTER U
                        ['upper' => 65334, 'status' => 'C', 'lower' => [65366]], // FULLWIDTH LATIN CAPITAL LETTER V
                        ['upper' => 65335, 'status' => 'C', 'lower' => [65367]], // FULLWIDTH LATIN CAPITAL LETTER W
                        ['upper' => 65336, 'status' => 'C', 'lower' => [65368]], // FULLWIDTH LATIN CAPITAL LETTER X
                        ['upper' => 65337, 'status' => 'C', 'lower' => [65369]], // FULLWIDTH LATIN CAPITAL LETTER Y
                        ['upper' => 65338, 'status' => 'C', 'lower' => [65370]], // FULLWIDTH LATIN CAPITAL LETTER Z
                    ];

                    break;
            }
        }

        return self::$_codeRanges[$range];
    }
}

class kxBans
{
    // Perform a check for a ban record for a specified IP address
    public static function BanCheck($ip, $board = '')
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
            echo $this->DisplayBannedMessage($bans);

            exit;
        }
    }

    // Add a ip/ip range ban
    public static function BanUser($ip, $board_ids, $duration, $reason, $allow_read, $allow_appeal, $notes, $staff_id, $delete_all_posts = false)
    {
        $em = kxOrm::getEntityManager();

        $ban = new Ban(
            ip: $ip,
            reason: $reason,
            allow_read: $allow_read,
            allow_appeal: $allow_appeal,
            expires_at: $expires_at = new DateTime('now + '.$duration.' seconds'),
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

    public static function UpdateHtaccess()
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

    // Return the page which will inform the user a quite unfortunate message
    private static function DisplayBannedMessage($bans, $board = '')
    {
        // Set a cookie with the users current IP address in case they use a proxy to attempt to make another post
        setcookie('tc_previousip', $_SERVER['REMOTE_ADDR'], time() + 604800, kxEnv::Get('kx:paths:boards:folder'));

        require_once KX_ROOT.'/lib/dwoo.php';

        kxTemplate::assign('bans', $bans);

        return $dwoo->get(KX_ROOT.kxEnv::Get('kx:templates:dir').'/banned.html.twig', $twigData);
    }
}
