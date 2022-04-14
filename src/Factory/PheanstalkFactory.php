<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Factory;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;

class PheanstalkFactory
{
    public function __invoke(ContainerInterface $c)
    {
        return Pheanstalk::create(
            $c->get('sched-config')['pheanstalk']['host'] ?? '127.0.0.1',
            $c->get('sched-config')['pheanstalk']['port'] ?? 11300,
            $c->get('sched-config')['pheanstalk']['timeout'] ?? 10,
        );
    }
}
