<?php

declare (strict_types=1);
// 应用公共文件
if (!function_exists('array_filter_empty_value')) {
    /**
     * array_filter_empty_value 去除数组中的空值
     */
    function array_filter_empty_value($arr): array
    {
        return array_filter($arr, function ($v) {
            if ($v === '' || $v === null) {
                return false;
            } else {
                return true;
            }
        });
    }
}

if (!function_exists('transformerDate')) {
    /**
     * @return false|mixed|string|null
     */
    function transformerDate($date, string $fmt = 'Y-m-d'): mixed
    {
        if (empty($date)) {
            return null;
        }
        if (is_numeric($date)) {
            return date($fmt, $date);
        } else {
            return $date;
        }
    }
}

if (!function_exists('generate_salt')) {
    /**
     * salt
     *
     * @param  int  $length 长度，默认为4，不能超过13位
     * @param  bool  $has_letter 是否包含字母
     */
    function generate_salt(int $length = 4, bool $has_letter = false): string
    {
        $salt = '';

        if ($has_letter) {
            $length = -$length;
            $salt = substr(uniqid(), $length);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $salt .= mt_rand(0, 9);
            }
        }

        return $salt;
    }
}

/**
 * 读取/dev/urandom获取随机数
 *
 * @param $len
 * @return mixed|string
 */
if (!function_exists('randomFromDev')) {
    function randomFromDev($len): string
    {
        $fp = @fopen('/dev/urandom', 'rb');
        $result = '';
        if ($fp !== false) {
            $result .= @fread($fp, $len);
            @fclose($fp);
        } else {
            @trigger_error('Can not open /dev/urandom.');

            return substr(time().md5(time().rand()), 0, $len);
        }
        // convert from binary to string
        $result = base64_encode($result);
        // remove none url chars
        $result = strtr($result, '+/', '-_');

        return substr($result, 0, $len);
    }
}

/**
 * @param $array
 * @param $key
 * @param  null  $default
 * @return mixed
 */
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if (!function_exists('transform')) {
    /**
     * 数据 类型转换.
     *
     * @param  mixed  $value 值
     * @param array|string $type  要转换的类型
     * @return mixed
     */
    function transform(mixed $value, array|string $type): mixed
    {
        if (is_null($value) || 0 === $value || '' === $value) {
            return $value;
        }

        if (is_array($type)) {
            [$type, $param] = $type;
        } elseif (strpos($type, ':')) {
            [$type, $param] = explode(':', $type, 2);
        }

        switch ($type) {
            case 'string':
                $value = (string) $value;

                break;
            case 'integer':
                $value = (int) $value;

                break;
            case 'float':
                if (empty($param)) {
                    $value = (float) $value;
                } else {
                    $value = (float) number_format($value, (int) $param, '.', '');
                }

                break;
            case 'boolean':
                $value = (bool) $value;

                break;
            case 'timestamp':
                if (!is_numeric($value)) {
                    $value = strtotime($value);
                }

                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);

                if (empty($param)) {
                    $value = date('Y-m-d H:i:s', $value);
                } else {
                    $value = date($param, $value);
                }

                break;
            case 'object':
                if (is_object($value)) {
                    $value = json_encode($value, JSON_FORCE_OBJECT);
                }

                break;
            case 'array':
                $value = (array) $value;

                break;
            case 'json':
                $option = !empty($param) ? (int) $param : JSON_UNESCAPED_UNICODE;
                $value = json_encode($value, $option);

                break;
            case 'serialize':
                $value = serialize($value);

                break;
            default:
                break;
        }

        return $value;
    }
}
