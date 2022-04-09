<?php

declare(strict_types=1);

namespace Dragonmantank\SchedTest\Queue;

use Dragonmantank\Sched\Queue\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testCanCreateMessage()
    {
        $message = new Message(
            payload: 'Test Message',
            id: 1,
        );

        $this->assertSame('Test Message', $message->payload);
        $this->assertSame(1, $message->id);
    }
}
