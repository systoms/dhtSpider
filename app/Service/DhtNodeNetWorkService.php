<?php

namespace App\Service;

use App\Lib\BitTorrent\Base;
use App\Lib\BitTorrent\BitContent;
use Hyperf\Utils\ApplicationContext;
use Swoole\Client;
use Swoole\Server;
use Swoole\Table;

class DhtNodeNetWorkService
{
    /**
     * 加入dht网络
     * @return void
     */
    public static function joinDht()
    {
        $bootstrap_nodes = array(
            array('router.bittorrent.com', 6881),
            array('dht.transmissionbt.com', 6881),
            array('router.utorrent.com', 6881)
        );

        foreach ($bootstrap_nodes as $bootstrap_node) {
            static::findNode(gethostbyname($bootstrap_node[0]), $bootstrap_node[1]);
        }
    }

    /**
     * 自动查找节点
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function autoFindNode()
    {
        $container = ApplicationContext::getContainer();
        $server = $container->get(Server::class);

        /**
         * @var $table Table
         */
        $table = $server->table;
        foreach ($table as $key=>$tableNode) {
            $nid = $tableNode['nid'];
            $ip = $tableNode['ip'];
            $port = $tableNode['port'];
            $table->del(base64_encode($nid));
            self::findNode($ip, $port, $nid);
        }
    }

    public static function findNode($ip, $port, $id = null)
    {
        $nid = CommonService::getNid();
        if (is_null($id)) {
            $mid = Base::get_node_id();
        } else {
            $mid = Base::get_neighbor($id, $nid); // 否则伪造一个相邻id
        }
        //echo '查找朋友'.$address[0].'是否在线'.PHP_EOL;
        // 定义发送数据 认识新朋友的。
        $message = array(
            't' => Base::entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => array(
                'id' => $nid,
                'target' => $mid
            )
        );
        // 发送请求数据到对端
        self::sendResponse($ip, $port, $message);
    }

    public static function sendResponse($ip, $port, $message)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            echo 'ip:',$ip,',port:',$port,"\n";
            echo '不是一个有效的ip', "\n";
            return false;
        }
        $data = BitContent::encode($message);
        $container = ApplicationContext::getContainer();
        $server = $container->get(Server::class);
        $server->sendto($ip, $port, $data);
    }
}