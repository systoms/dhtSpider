<?php
declare(strict_types=1);
/*
 * Author: CaoLei
 * Date: 2021/9/23 17:45
 */

namespace App\Utils;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * 实现Laravel框架的日志用法
 *
 * Author: CaoLei
 * Date: 2021/9/23 17:46
 *
 * @method static debug(string $message, mixed[] $context)
 * @method static info(string $message, mixed[] $context)
 * @method static notice(string $message, mixed[] $context)
 * @method static warning(string $message, mixed[] $context)
 * @method static error(string $message, mixed[] $context)
 * @method static critical(string $message, mixed[] $context)
 * @method static alert(string $message, mixed[] $context)
 * @method static emergency(string $message, mixed[] $context)
 */
class Log
{

    /**
     * 魔术方法
     * Author: CaoLei
     * Date: 2021/9/23 17:46
     *
     */
    public static function __callStatic(string $func_name, array $args)
    {
        ApplicationContext::getContainer()->get(LoggerFactory::class)->get("APP",)->$func_name(...$args);
    }

}