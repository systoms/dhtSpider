<?php

namespace App\Task;

use App\Service\DhtServerService;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use Swoole\Server;

/**
 * @Crontab(name="FindNodeTask", rule="*\/3 * * * * *", callback="execute", memo="这是一个示例的定时任务")
 */
class FindNodeTask
{
    public function execute()
    {
        $container = ApplicationContext::getContainer();
        $server = $container->get(Server::class);
        $table = $server->table;

        $bootstrap_nodes = array(
            array('router.bittorrent.com', 6881),
            array('dht.transmissionbt.com', 6881),
            array('router.utorrent.com', 6881)
        );

        try {
            if ($table->count() == 0) {
                DhtServerService::join_dht($table, $bootstrap_nodes);
                echo 'join_dht';
            } else {
                DhtServerService::auto_find_node($table, $bootstrap_nodes);
                echo 'auto_find_node';
            }
        }catch (\Throwable $throwable){
            echo $throwable->getMessage(),"\n";
        }
    }
}