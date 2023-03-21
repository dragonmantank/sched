<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Queue\MessageBroker;

use Dragonmantank\Sched\Exception\NoMessageInQueueException;
use Dragonmantank\Sched\Queue\Message;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Stringable;

class Beanstalkd implements MessageBrokerInterface
{
    public function __construct(protected Pheanstalk $pheanstalk)
    {
    }

    public function buryMessage(string $queueName, Message $message): void
    {
        $this->pheanstalk->useTube($queueName)->bury(new Job($message->id, $message->payload));
    }

    public function deleteMessage(string $queueName, Message $message): void
    {
        $this->pheanstalk->useTube($queueName)->delete(new Job($message->id, $message->payload));
    }

    public function getMessageStats(string $queueName, Message $message): array
    {
        return (array) $this->pheanstalk->useTube($queueName)->statsJob(new Job($message->id, $message->payload));
    }

    public function getStats(string $queueName): array
    {

        $stats = $this->pheanstalk->statsTube($queueName);

        return [
            'current-jobs-ready' => $stats['current-jobs-ready'],
            'current-jobs-reserved' => $stats['current-jobs-reserved'],
            'current-watching' => $stats['current-watching'],
        ];
    }

    public function peekReady(string $queueName): Message
    {
        $job = $this->pheanstalk->useTube($queueName)->peekReady();
        if ($job) {
            return new Message(id: $job->getId(), payload: $job->getData());
        }

        throw new NoMessageInQueueException();
    }

    public function receiveMessage(string $queueName, ?int $timeout = null): Message
    {
        $this->pheanstalk->watch($queueName);
        if (is_null($timeout)) {
            $job = $this->pheanstalk->reserve();
        } else {
            $job = $this->pheanstalk->reserveWithTimeout($timeout);
        }

        if ($job) {
            return new Message(id: $job->getId(), payload: $job->getData());
        }

        throw new NoMessageInQueueException();
    }

    public function releaseMessage(
        string $queueName,
        Message $message,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY
    ): void {
        $this->pheanstalk->useTube($queueName)->release(
            job: new Job($message->id, $message->payload),
            priority: $priority,
            delay: $delay
        );
    }

    public function sendMessage(
        string $queueName,
        Stringable|string $payload,
        int $priority = Message::DEFAULT_PRIORITY,
        int $delay = Message::DEFAULT_DELAY,
        int $ttr = Message::DEFAULT_TTR,
    ): Message {
        $job = $this->pheanstalk
            ->useTube($queueName)
            ->put(
                data: (string) $payload,
                priority: $priority,
                delay: $delay,
                ttr: $ttr,
            );
        return new Message((string) $payload, $job->getId());
    }
}
