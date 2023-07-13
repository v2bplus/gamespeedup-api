<?php

class Utility
{
    public static function randomNumber($length = 4)
    {
        if ($length < 9 && $length >= 2) {
            return str_pad(mt_rand(1, pow(10, $length)), '0', STR_PAD_LEFT);
        }
        $str = str_repeat('1234567890', $length / 2);

        return substr(str_shuffle($str), 0, $length);
    }

    public static function randomCode($n = 4)
    {
        return $n < 1 ? '' : substr(str_shuffle(str_repeat('abcdefghijkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ', 3)), 0, $n);
    }

    public static function htmlEncode($content)
    {
        return is_string($content) ? htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE) : $content;
    }

    public static function htmlDecode($content)
    {
        return is_string($content) ? htmlspecialchars_decode($content, ENT_QUOTES) : $content;
    }

    public static function hideMobile(string $mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    public static function getGuid()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);    // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function randomChar($len, $special = false)
    {
        $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
            'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
            'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2',
            '3', '4', '5', '6', '7', '8', '9',
        ];

        if ($special) {
            $chars = array_merge($chars, [
                '!', '@', '#', '$', '?', '|', '{', '/', ':', ';',
                '%', '^', '&', '*', '(', ')', '-', '_', '[', ']',
                '}', '<', '>', '~', '+', '=', ',', '.',
            ]);
        }

        $charsLen = count($chars) - 1;
        shuffle($chars);
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[mt_rand(0, $charsLen)];
        }

        return $str;
    }

    public static function trafficConvert(int $byte)
    {
        $kb = 1024;
        $mb = 1048576;
        $gb = 1073741824;
        if ($byte > $gb) {
            return round($byte / $gb, 2).' GB';
        }
        if ($byte > $mb) {
            return round($byte / $mb, 2).' MB';
        }
        if ($byte > $kb) {
            return round($byte / $kb, 2).' KB';
        }
        if ($byte < 0) {
            return 0;
        }

        return round($byte, 2).' B';
    }

    public static function multiPasswordVerify($algo, $salt, $password, $hash)
    {
        switch ($algo) {
            case 'md5': return md5($password) === $hash;
            case 'sha256': return hash('sha256', $password) === $hash;
            case 'md5salt': return md5($password.$salt) === $hash;
            default: return password_verify($password, $hash);
        }
    }

    public static function getHttpHeader(string $headerKey)
    {
        // test
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_'.$headerKey;

        return $_SERVER[$headerKey] ?? '';
    }

    public static function removeXss($val)
    {
        $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); ++$i) {
            $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }
        $ra1 = ['javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link',
            'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer',
            'layer', 'bgsound', 'title', 'base', ];

        $ra2 = ['onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut',
            'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur',
            'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable',
            'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
            'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin',
            'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
            'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend',
            'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart',
            'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', ];
        $ra = array_merge($ra1, $ra2);
        $found = true;
        while (true == $found) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); ++$i) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); ++$j) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                        $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                        $pattern .= ')?';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    $found = false;
                }
            }
        }

        return $val;
    }

    public static function htmlentitiesUTF8($string, $type = ENT_QUOTES)
    {
        if (is_array($string)) {
            return array_map(['Utility', 'htmlentitiesUTF8'], $string);
        }

        return htmlentities((string) $string, $type, 'utf-8');
    }

    public static function setCookie($key, $value, $expire = 0)
    {
        $config = \Yaf_Registry::get('_config');
        if (isset($config['application']['cookie'])) {
            $cookie = $config['application']['cookie'];
            $pre = $cookie['pre'] ?? 'pre';
            $path = $cookie['path'] ?? '/';
            $domain = $cookie['domain'] ?? '';
            $key = $pre.$key;
            $value = base64_encode(serialize($value));
            $expire = !empty($expire) ? time() + $expire : 0;
            setcookie($key, $value, $expire, $path, $domain);
        }
    }

    public static function getCookie($key, $value = '')
    {
        $config = \Yaf_Registry::get('_config');
        if (isset($config['application']['cookie'])) {
            $cookie = $config['application']['cookie'];
            $pre = $cookie['pre'] ?? 'pre';
            $key = $pre.$key;
            $value = $_COOKIE[$key] ?? $value;
            $value = unserialize(base64_decode($value));
        }

        return $value;
    }

    public static function arrayGet($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    public static function clearCookie()
    {
        unset($_COOKIE);
    }

    public static function std_class_object_to_array($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;

        foreach ($_array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ?? $value;
            $array[$key] = $value;
        }

        return $array;
    }

    public static function writeLog($content = '', $floder = '', $filename = '')
    {
        if (!$content) {
            return false;
        }
        $dir = LOGS_PATH.$floder;
        if (!is_dir($dir)) {
            if (!mkdir($dir)) {
                return false;
            }
        }
        if (!empty($filename)) {
            $filename = $dir.DS.$filename.'.log';
        } else {
            $filename = $dir.DS.date('YmdHis', time()).'.log';
        }
        $str = 'Time:'.date('Y-m-d H:i:s')."\r\n".'内容:'.$content."\r\n";
        if (!$fp = @fopen($filename, 'a')) {
            return false;
        }
        if (!fwrite($fp, $str)) {
            return false;
        }
        fclose($fp);

        return true;
    }

    public static function orderNum($uid = '')
    {
        if (!$uid) {
            return false;
        }
        [$userTIme, $secTime] = explode(' ', microtime());
        $floatTime = ((float) $userTIme + (float) $secTime);
        $preString = round($floatTime * 100);
        if ($uid) {
            $length = strlen($uid);
            if ($length < 5) {
                $code = str_repeat('0', 5 - $length).$uid;
            } else {
                $code = substr($uid, -5);
            }
            $preString = $preString.$code;
        }

        return $preString.mt_rand(1, 9);
    }

    public static function int(&$int, $default = 0, $min = 0, $max = PHP_INT_MAX)
    {
        return isset($int) ? filter_var(
            $int,
            FILTER_VALIDATE_INT,
            ['options' => ['default' => $default, 'min_range' => $min, 'max_range' => $max]]
        ) : $default;
    }

    public static function removeLink($string)
    {
        return preg_replace('/<a.*?>(.*?)<\/a>/isu', '$1', $string);
    }

    public static function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function bool(&$bool, $default = false)
    {
        return isset($bool) ? filter_var(
            $bool,
            FILTER_VALIDATE_BOOLEAN,
            ['options' => ['default' => $default]]
        ) : $default;
    }

    public static function float(&$float, $default = 0, $min = 0, $max = PHP_INT_MAX, $decimal = '.')
    {
        return isset($float) && $float >= $min && $float <= $max ? filter_var(
            $float,
            FILTER_VALIDATE_FLOAT,
            ['options' => ['default' => $default, 'decimal' => $decimal]]
        ) : $default;
    }

    public static function convertSzie($bytesNumber, $decimals = 2)
    {
        $unit = ['B', 'K', 'M', 'G', 'T', 'P'];

        return number_format($bytesNumber / pow(1024, $i = floor(log($bytesNumber, 1024))), $decimals).' '.$unit[$i];
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).''.$units[$pow];
    }

    public static function secondsToTime($inputSeconds)
    {
        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        $days = floor($inputSeconds / $secondsInADay);

        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        return [
            'd' => (int) $days,
            'h' => (int) $hours,
            'm' => (int) $minutes,
            's' => (int) $seconds,
        ];
    }

    public static function generateTree($data, $id = 'id', $pid = 'pid')
    {
        $tree = [];
        foreach ($data as $item) {
            if (isset($data[$item[$pid]])) {
                $data[$item[$pid]]['child'][] = &$data[$item[$id]];
            } else {
                $tree[] = &$data[$item[$id]];
            }
        }

        return $tree;
    }

    public static function arrayColumn2Key($source, $index): array
    {
        $data = [];
        if (!$source) {
            return $data;
        }
        foreach ($source as $item) {
            $data[$item[$index]] = $item;
        }

        return $data;
    }

    public static function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /*
     * 多维数组合并
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function array_merge_multiple(array $array1, array $array2)
    {
        $merge = $array1 + $array2;
        $data = [];
        foreach ($merge as $key => $val) {
            if (
                isset($array1[$key])
                && is_array($array1[$key])
                && isset($array2[$key])
                && is_array($array2[$key])
            ) {
                $data[$key] = self::is_assoc($array1[$key]) ? self::array_merge_multiple($array1[$key], $array2[$key]) : $array2[$key];
            } else {
                $data[$key] = $array2[$key] ?? $array1[$key];
            }
        }

        return $data;
    }
}
