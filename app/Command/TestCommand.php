<?php

declare(strict_types=1);

namespace App\Command;

use App\Lib\BitTorrent\Base;
use App\Lib\BitTorrent\BitContent;
use App\Service\DhtServerService;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Swoole\Server;

/**
 * @Command
 */
#[Command]
class TestCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('test:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $mid = Base::get_node_id();
        $msg = array(
            't' => Base::entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => array(
                'id' => '',
                'target' => $mid
            )
        );

        $data = BitContent::encode($msg);
        print_r($msg);
        var_dump($data);
//        $this->line('Hello Hyperf!', 'info');
//        var_dump(Base::entropy(2));

    }
}
