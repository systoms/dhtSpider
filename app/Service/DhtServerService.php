<?php

declare(strict_types=1);

namespace App\Service;

use App\Lib\BitTorrent\Base;
use App\Lib\BitTorrent\BitContent;
use App\Lib\BitTorrent\Node;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Client;
use Swoole\Server;
use Swoole\Table;

class DhtServerService
{
    public static function join_dht($table, $bootstrap_nodes)
    {
        if (count($table) == 0) {
            foreach ($bootstrap_nodes as $node) {
                //echo '路由表为空 将自身伪造的ID 加入预定义的DHT网络 '.$node[0].PHP_EOL;
                self::find_node(array(gethostbyname($node[0]), $node[1])); //将自身伪造的ID 加入预定义的DHT网络
            }
        }
    }

    public static function auto_find_node(Table $table, $bootstrap_nodes)
    {
        foreach ($table as $tableNode) {
            $nid = $tableNode['nid'];
            $ip = $tableNode['ip'];
            $port = $tableNode['port'];
            $table->del(base64_encode($nid));
            self::find_node(array($ip, $port), $nid);
        }
    }

    public static function find_node($address, $id = null)
    {
        $nid = CommonService::getNid();
        if (is_null($id)) {
            $mid = Base::get_node_id();
        } else {
            $mid = Base::get_neighbor($id, $nid); // 否则伪造一个相邻id
        }
        //echo '查找朋友'.$address[0].'是否在线'.PHP_EOL;
        // 定义发送数据 认识新朋友的。
        $msg = array(
            't' => Base::entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => array(
                'id' => $nid,
                'target' => $mid
            )
        );
        // 发送请求数据到对端
        self::send_response($msg, $address);
    }

    public static function send_response($msg, $address)
    {
        if (!filter_var($address[0], FILTER_VALIDATE_IP)) {
            echo '不是一个有效的ip', "\n";
            return false;
        }
        $ip = $address[0];
//        $data = Base::encode($msg);
        $data = BitContent::encode($msg);


        $client = new Client(SWOOLE_SOCK_UDP);
        $client->connect($ip, $address[1]);
        $client->send($data);
        echo 'sendto(ip:', $ip, ',address:', $address[1], ',data:', $data, ")\n";
    }
}