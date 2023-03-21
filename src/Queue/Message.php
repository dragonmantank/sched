<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Queue;

class Message
{
    const DEFAULT_PRIORITY = 1024;
    const DEFAULT_DELAY = 0;
    const DEFAULT_TTR = 60;

    public function __construct(
        public string $payload,
        public int $id,
    ) {
    }
}
