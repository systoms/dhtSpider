<?php

namespace App\Task;

use App\Service\DhtNodeNetWorkService;
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

        try {
            if ($table->count() == 0) {
                DhtNodeNetWorkService::joinDht();
//                echo "join_dht\n";
            } else {
                DhtNodeNetWorkService::autoFindNode();
//                echo "auto_find_node\n";
            }
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage(), "\n";
        }
    }
}