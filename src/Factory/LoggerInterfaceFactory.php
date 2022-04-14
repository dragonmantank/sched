<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Factory;

use Psr\Container\ContainerInterface;

class LoggerInterfaceFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $factory = $c->get('sched-config')['logger'] ?? null;

        if (is_null($factory)) {
            return null;
        }

        if (is_string($factory)) {
            $factory = $c->get($factory);
        }

        if (!is_callable($factory)) {
            throw new \InvalidArgumentException('Logger Factory is not callable');
        }

        return $factory($c);
    }
}
