<?php

namespace App\Service;

use App\Lib\BitTorrent\Base;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\Redis;

class CommonService
{
    public static function getNid()
    {
        $key = 'bit_nid';
        $key_nx = 'bit_nid_lock';
        $container = ApplicationContext::getContainer();

        $redis = $container->get(Redis::class);
        if ($redis->exists($key)) {
            return $redis->get($key);
        }

        if (!$redis->set($key_nx, 1, ['nx', 'ex' => 60])) {
            sleep(1);
            return $redis->get($key);
        }

        $redis->set($key_nx, 1, ['nx', 'ex' => 60]);
        $nid = Base::get_node_id();
        $redis->set($key, $nid);
        $redis->del($key_nx);
        return $nid;
    }
}