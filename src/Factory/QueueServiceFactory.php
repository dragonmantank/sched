<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Factory;

use Dragonmantank\Sched\Queue\MessageBroker\Beanstalkd;
use Dragonmantank\Sched\Queue\QueueService;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;

class QueueServiceFactory
{
    public function __invoke(ContainerInterface $c)
    {
        return new QueueService($c->get('config'), new Beanstalkd($c->get(Pheanstalk::class)));
    }
}
