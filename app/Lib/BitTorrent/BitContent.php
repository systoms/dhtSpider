<?php

namespace App\Lib\BitTorrent;

class BitContent
{
    public static function encode($data)
    {
        if (is_array($data) && (isset($data[0]) || empty($data))) {
            return static::encode_list($data);
        } elseif (is_array($data)) {
            return static::encode_dict($data);
        } elseif (is_integer($data) || is_float($data)) {
            $data = sprintf("%.0f", round($data, 0));
            return static::encode_integer($data);
        } else {
            return static::encode_string($data);
        }
    }

    public static function do_encode($data = null)
    {
        if (is_array($data) && (isset($data[0]) || empty($data))) {
            return static::encode_list($data);
        } elseif (is_array($data)) {
            return static::encode_dict($data);
        } elseif (is_integer($data) || is_float($data)) {
            $data = sprintf("%.0f", round($data, 0));
            return static::encode_integer($data);
        } else {
            return static::encode_string($data);
        }
    }

    public static function encode_list(array $data = null)
    {
        $list = '';

        foreach ($data as $value) {
            $list .= static::do_encode($value);
        }

        return "l{$list}e";
    }

    /**
     * 编码数字类型数据
     * @param integer $data 要编码的数据
     * @return string       编码后的数据
     */
    public static function encode_integer($data = null)
    {
        return sprintf("i%.0fe", $data);
    }

    /**
     * 编码字符串类型数据
     * @param string $data 要编码的数据
     * @return string       编码后的数据
     */
    public static function encode_string($data = null)
    {
        return strlen($data) . ':' . $data;
    }

    /**
     * 编码词典类型数据
     * @param array $data 要编码的数据
     * @return string           编码后的数据
     */
    public static function encode_dict(array $data = null)
    {
        ksort($data);
        $dict = '';

        foreach ($data as $key => $value)
            $dict .= static::encode_string($key) . static::do_encode($value);

        return "d{$dict}e";
    }
}