<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Cron;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Pheanstalk;

class SimplePayloadCron
{
    public function __construct(protected Pheanstalk $pheanstalk)
    {
    }

    /**
     * @param array<mixed> $payload 
     */
    public function __invoke(
        string $queueName,
        array $payload = [],
        int $ttr = PheanstalkInterface::DEFAULT_TTR,
        int $priority = PheanstalkInterface::DEFAULT_PRIORITY,
        int $delay = PheanstalkInterface::DEFAULT_DELAY
    ): int {
        $this->pheanstalk->useTube($queueName)->put(
            data: json_encode($payload, JSON_THROW_ON_ERROR),
            ttr: $ttr,
            priority: $priority,
            delay: $delay,
        );

        return 0;
    }
}
