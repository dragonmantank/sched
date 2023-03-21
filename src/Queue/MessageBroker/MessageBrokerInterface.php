<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Queue\MessageBroker;

use Dragonmantank\Sched\Queue\Message;
use Stringable;

interface MessageBrokerInterface
{
    public function buryMessage(string $queueName, Message $message): void;

    public function deleteMessage(string $queueName, Message $message): void;

    /** @return Mixed[] */
    public function getMessageStats(string $queueName, Message $message): array;

    /** @return Mixed[] */
    public function getStats(string $queueName): array;

    public function peekReady(string $queueName): Message;

    public function receiveMessage(string $queueName, ?int $timeout = null): Message;

    public function releaseMessage(
        string $queueName,
        Message $message,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY
    ): void;

    public function sendMessage(
        string $queueName,
        Stringable|string $payload,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY,
        int $ttr = Message::DEFAULT_TTR,
    ): Message;
}
