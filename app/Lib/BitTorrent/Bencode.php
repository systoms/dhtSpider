<?php

namespace App\Lib\BitTorrent;

use App\Lib\BitTorrent\Decode;
use App\Lib\BitTorrent\Encode;

class Bencode
{
    /**
     * bencode解码
     * @param string $str 要解码的数据
     * @return object      解码后的数据
     */
    static public function decode($str)
    {
        return Decode::decode($str);
    }

    /**
     * bencode编码
     * @param object $value 要编码的数据
     * @return string        编码后的数据
     */
    static public function encode($value)
    {
        return Encode::encode($value);
    }
}