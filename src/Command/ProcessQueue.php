<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueue extends Command
{
    protected static $defaultName = 'queue:process';

    /**
     * @param array{
     *      'pheanstalk': array<string, mixed>,
     *      'cron': array<
     *          int,
     *          array{'name': string, 'expression': string, 'worker': string|callable}
     *      >,
     *      'queues': array<string, array{'worker': string|callable}>,
     *      'config': array{'path': string}
     * } $config
     */
    public function __construct(
        protected array $config,
        protected Pheanstalk $pheanstalk,
        protected ContainerInterface $container
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Reads a queue and attempts to process it')
            ->addArgument('queueName', InputArgument::REQUIRED, 'Queue to process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string */
        $queueName = $input->getArgument('queueName');
        $verbose = $input->getOption('verbose');

        $this->pheanstalk->watch($queueName);
        for ($i = 0; $i <= 5; $i++) {
            $stats = $this->pheanstalk->statsTube($queueName);
            if ($stats['current-jobs-ready'] < 1) {
                exit(0);
            }

            $job = $this->pheanstalk->reserve();
            try {
                $payload = json_decode($job->getData(), true);
                $worker = $this->config['queues'][$queueName]['worker'];

                if (is_callable($worker)) {
                    $worker($payload);
                } elseif (is_string($worker)) {
                    /** @var callable */
                    $worker = $this->container->get($worker);
                    $worker($payload);
                }

                $this->pheanstalk->delete($job);
            } catch (\Exception $e) {
                $this->pheanstalk->release($job);
            }
        }

        return Command::SUCCESS;
    }
}
