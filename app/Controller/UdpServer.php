<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lib\BitTorrent\Base;
use App\Service\DhtClientService;
use App\Service\DhtServerService;
use App\Service\MetadataService;
use Hyperf\Contract\OnPacketInterface;
use Hyperf\Utils\ApplicationContext;
use Swoole\Server;
use Swoole\Client;
use Swoole\Table;

class UdpServer implements OnPacketInterface
{
    public function onBeforeStart()
    {
        $table = new Table(65536);
        $table->column('fd', Table::TYPE_INT);
        $table->column('reactor_id', Table::TYPE_INT);
        $table->column('data', Table::TYPE_STRING, 64);
        $table->create();
        $container = ApplicationContext::getContainer();
        $server = $container->get(Server::class);
        $server->table = $table;
    }

    public function onWorkerStart($server, $workerId): void
    {
        var_dump($workerId);
    }

    public function onPacket($server, $data, $clientInfo): void
    {
        if (strlen($data) == 0) {
            return;
        }
        $msg = Base::decode($data);
        try {
            if (!isset($msg['y'])) {
                return;
            }
            echo 'onPacket->', $msg['y'], "\n";
            if ($msg['y'] == 'r') {
                // 如果是回复, 且包含nodes信息 添加到路由表
                if (array_key_exists('nodes', $msg['r'])) {
                    DhtClientService::response_action($msg, array($clientInfo['address'], $clientInfo['port']));
                }
            } elseif ($msg['y'] == 'q') {
                // 如果是请求, 则执行请求判断
                DhtClientService::request_action($msg, array($clientInfo['address'], $clientInfo['port']));
            }
        } catch (\Exception $e) {
            //var_dump($e->getMessage());
        }

//        var_dump($clientInfo);
//        $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server：' . $data);
    }

    public function onTask($server, $task_id, $reactor_id, $data)
    {
        $ip = $data['ip'];
        $port = $data['port'];


        echo 'onTask', "\n";
        $infohash = swoole_serialize::unpack($data['infohash']);
//        $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);

        $container = ApplicationContext::getContainer();
        $client = $container->get(Client::class);

        if (!@$client->connect($ip, $port, 1)) {
            //echo ("connect failed. Error: {$client->errCode}".PHP_EOL);
        } else {
            //echo 'connent success! '.$ip.':'.$port.PHP_EOL;
            $rs = MetadataService::download_metadata($client, $infohash);
            if ($rs != false) {
                //echo $ip.':'.$port.' udp send！'.PHP_EOL;
                DhtServerService::send_response($rs, array('35.35.35.35', '2345'));
                echo date('Y-m-d H:i:s') . ' ' . $rs['name'] . PHP_EOL;
            } else {
                //echo 'false'.date('Y-m-d H:i:s').PHP_EOL;
            }
            $client->close(true);
        }

    }
}
