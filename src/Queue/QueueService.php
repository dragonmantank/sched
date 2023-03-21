<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Queue;

use Dragonmantank\Sched\Queue\MessageBroker\MessageBrokerInterface;
use Stringable;

class QueueService
{
    /** @param Mixed[] $config */
    public function __construct(
        protected array $config,
        protected MessageBrokerInterface $broker
    ) {
    }

    public function buryMessage(string $queueName, Message $message): void
    {
        $this->broker->buryMessage($queueName, $message);
    }

    public function deleteMessage(string $queueName, Message $message): void
    {
        $this->broker->deleteMessage($queueName, $message);
    }

    /** @return Mixed[] */
    public function getMessageStats(string $queueName, Message $message): array
    {
        return $this->broker->getMessageStats($queueName, $message);
    }

    /** @return Mixed[] */
    public function getStats(?string $queueName = null): array
    {
        if (is_string($queueName)) {
            $queues = [$queueName];
        } else {
            $queues = array_keys($this->config['queues']);
        }

        $data = [];
        foreach ($queues as $queueName) {
            $data[$queueName] = $this->broker->getStats((string)$queueName);
        }

        return $data;
    }

    public function peekReady(string $queueName): Message
    {
        return $this->broker->peekReady($queueName);
    }

    public function receiveMessage(string $queueName, ?int $timeout = null): Message
    {
        return $this->broker->receiveMessage($queueName, $timeout);
    }

    public function releaseMessage(
        string $queueName,
        Message $message,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY
    ): void {
        $this->broker->releaseMessage($queueName, $message, $priority, $delay);
    }

    public function sendMessage(
        string $queueName,
        Stringable|string $payload,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY,
        int $ttr = Message::DEFAULT_TTR,
    ): Message {
        $delay += rand((int) $this->config['messages']['jitter']['min'], (int) $this->config['messages']['jitter']['max']);

        return $this->broker->sendMessage(
            $queueName,
            $payload,
            $priority,
            $delay,
            $ttr
        );
    }
}
