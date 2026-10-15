<?php

namespace kx;

class kxRequest
{
    /**
     * Used as an upper limit in parseInput to avoid traversing overly-deep arrays.
     *
     * @var int
     */
    private const int MAX_ARRAY_DEPTH = 10;
    private static ?kxRequest $instance;
    private array $get = [] {
        set => self::parseInput($value);
    }

    private array $post = [] {
        set => self::parseInput($value);
    }

    private array $cookie = [] {
        set => self::parseInput($value);
    }
    private array $server = [] {
        set => $value;
    }

    private function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->server = $_SERVER;
    }

    /**
     * Return a $_GET parameter.
     */
    public function get(string $property, string $fallback = ''): string
    {
        return $this->get[$property] ?? $fallback;
    }

    /**
     * Return a $_POST parameter.
     */
    public function post(string $property, string $fallback = ''): string
    {
        return $this->post[$property] ?? $fallback;
    }

    /**
     * Return a value from $_SERVER.
     */
    public function server(string $property, string $fallback = ''): string
    {
        return $this->server[$property] ?? $fallback;
    }

    /**
     * Get the instance of kxRequest.
     */
    public static function getInstance(): kxRequest
    {
        if (!isset(self::$instance)) {
            self::$instance = new kxRequest();
        }

        return self::$instance;
    }

    /**
     * Recursively cleans keys and values and
     * inserts them into the input array.
     *
     * @param array $data  Input data
     * @param array $input Array to merge into
     * @param int   $i     Iteration
     *
     * @return array Cleaned data
     */
    private static function parseInput(array $data, array $input = [], int $i = 0): array
    {
        if ($i > self::MAX_ARRAY_DEPTH) {
            return $input;
        }

        foreach ($data as $k => $v) {
            if (\is_array($v)) {
                $input[$k] = self::parseInput($data[$k], [], $i++);
            } else {
                $k = self::cleanInputKey($k);
                $v = self::cleanInputVal($v);

                $input[$k] = $v;
            }
        }

        return $input;
    }

    /**
     * Clean up $key by decoding URL-encoded values, removing special characters.
     */
    private static function cleanInputKey(string $key): string
    {
        if ('' == $key) {
            return '';
        }

        $key = htmlspecialchars(urldecode($key));
        $key = str_replace('..', '', $key);
        $key = preg_replace('/\_\_(.+?)\_\_/', '', $key);

        return preg_replace('/^([\w\.\-\_]+)$/', '$1', $key);
    }

    /**
     * Clean up the string $txt by removing potentially harmful input.
     */
    private static function cleanInputVal(string $txt): string
    {
        if (empty($txt)) {
            return '';
        }

        // Decimal places. Script kiddies might think they can try to access files outside the board
        $txt = str_replace('&#46;&#46;/', '../', $txt);

        // This litte bugger changes the formatting to be right-to-left, like on a hebrew locale.
        $txt = str_replace('&#8238;', '', $txt);

        // Null byte characters can mess with formatting as well, so we remove them
        $txt = str_replace("\x00", '', $txt);
        $txt = str_replace(\chr('0'), '', $txt);
        $txt = str_replace("\0", '', $txt);

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

        $txt = preg_replace('/&amp;#([0-9]+);/s', '&#\1;', $txt);

        return preg_replace('/&#(\d+?)([^\d;])/i', '&#\1;\2', $txt);
    }
}
