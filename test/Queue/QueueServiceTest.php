<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Queue;

use Dragonmantank\Sched\Queue\MessageBroker\MessageBrokerInterface;
use function PHPSTORM_META\map;
use Mockery;

use PHPUnit\Framework\TestCase;

class QueueServiceTest extends TestCase
{
    protected array $config = [
        'queues' => [
            'queue-1' => [],
            'queue-2' => [],
        ]
    ];

    public function testCanSendMessage()
    {
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('sendMessage')
            ->once()
            ->with('test-queue', 'Test Data', Message::DEFAULT_PRIORITY, Message::DEFAULT_DELAY, Message::DEFAULT_TTR)
            ->andReturnUsing(function () {
                return new Message('Test Data', 1);
            });

        $service = new QueueService($this->config, $broker);
        $message = $service->sendMessage('test-queue', 'Test Data');

        $this->assertEquals(1, $message->id);
        $this->assertEquals('Test Data', $message->payload);
    }

    public function testCanGetQueueStatsFromSingleQueue()
    {
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('getStats')
            ->once()
            ->with('queue-1')
            ->andReturn([
                'current-jobs-ready' => "5",
                'current-jobs-reserved' => "2",
                'current-watching' => "1"
            ]);
        $service = new QueueService($this->config, $broker);

        $stats = $service->getStats('queue-1');

        $this->assertEquals("5", $stats['queue-1']['current-jobs-ready']);
        $this->assertEquals("2", $stats['queue-1']['current-jobs-reserved']);
        $this->assertEquals("1", $stats['queue-1']['current-watching']);
    }

    public function testCanGetQueueStatsFromAllQueues()
    {
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('getStats')
            ->once()
            ->with('queue-1')
            ->andReturn([
                'current-jobs-ready' => "5",
                'current-jobs-reserved' => "2",
                'current-watching' => "1"
            ]);
        $broker
            ->shouldReceive('getStats')
            ->once()
            ->with('queue-2')
            ->andReturn([
                'current-jobs-ready' => "10",
                'current-jobs-reserved' => "4",
                'current-watching' => "2"
            ]);
        $service = new QueueService($this->config, $broker);

        $stats = $service->getStats();

        $this->assertEquals("5", $stats['queue-1']['current-jobs-ready']);
        $this->assertEquals("2", $stats['queue-1']['current-jobs-reserved']);
        $this->assertEquals("1", $stats['queue-1']['current-watching']);

        $this->assertEquals("10", $stats['queue-2']['current-jobs-ready']);
        $this->assertEquals("4", $stats['queue-2']['current-jobs-reserved']);
        $this->assertEquals("2", $stats['queue-2']['current-watching']);
    }

    public function testCanBuryMessage()
    {
        $this->expectNotToPerformAssertions();

        $message = new Message('test', 1);
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('buryMessage')
            ->once()
            ->with('queue-1', $message);

        $service = new QueueService($this->config, $broker);

        $service->buryMessage('queue-1', $message);
    }

    public function testCanDeleteMessage()
    {
        $this->expectNotToPerformAssertions();

        $message = new Message('test', 1);
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('deleteMessage')
            ->once()
            ->with('queue-1', $message);

        $service = new QueueService($this->config, $broker);

        $service->deleteMessage('queue-1', $message);
    }

    public function testCanReleaseMessage()
    {
        $this->expectNotToPerformAssertions();

        $message = new Message('test', 1);
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('releaseMessage')
            ->once()
            ->with('queue-1', $message, Message::DEFAULT_PRIORITY, Message::DEFAULT_DELAY);

        $service = new QueueService($this->config, $broker);

        $service->releaseMessage('queue-1', $message);
    }

    public function testCanReleaseMessageWithDelay()
    {
        $this->expectNotToPerformAssertions();

        $message = new Message('test', 1);
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('releaseMessage')
            ->once()
            ->with('queue-1', $message, Message::DEFAULT_PRIORITY, 120);

        $service = new QueueService($this->config, $broker);

        $service->releaseMessage('queue-1', $message, delay: 120);
    }

    public function testCanReceiveMessage()
    {
        $broker = Mockery::mock(MessageBrokerInterface::class);
        $broker
            ->shouldReceive('receiveMessage')
            ->once()
            ->with('queue-1', null)
            ->andReturnUsing(function () {
                return new Message('test', 1);
            });

        $service = new QueueService($this->config, $broker);

        $message = $service->receiveMessage('queue-1');

        $this->assertEquals(1, $message->id);
        $this->assertEquals('test', $message->payload);
    }
}
