<?php

declare(strict_types=1);

use Dragonmantank\Sched\Factory\ConfigFactory;
use Dragonmantank\Sched\Factory\LoggerInterfaceFactory;
use Dragonmantank\Sched\Factory\PheanstalkFactory;
use Pheanstalk\Pheanstalk;
use Psr\Log\LoggerInterface;

return [
    'config' => DI\factory(ConfigFactory::class),
    LoggerInterface::class => DI\factory(LoggerInterfaceFactory::class),
    Pheanstalk::class => DI\factory(PheanstalkFactory::class),
];
