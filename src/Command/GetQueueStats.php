<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetQueueStats extends Command
{
    protected static $defaultName = 'queue:stats';

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
        protected Pheanstalk $pheanstalk
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Returns information about the queue')
            ->addArgument('queueName', InputArgument::OPTIONAL, 'Queue to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string */
        $queueName = $input->getArgument('queueName');
        $rows = [];
        $headers = [];

        if ($queueName) {
            $headers = ['Variable', 'Value'];
            try {
                $stats = $this->pheanstalk->statsTube($queueName);
            } catch (ServerException $e) {
                $output->writeln('Unable to get stats, tube does not exist');
                return Command::FAILURE;
            }

            $rows = [];
            foreach ($stats as $key => $value) {
                $rows[] = [$key, $value];
            }
        } else {
            $headers = ['Queue', 'Stats'];
            foreach ($this->config['queues'] as $queueName => $_) {
                try {
                    $stats = $this->pheanstalk->statsTube($queueName);
                } catch (ServerException $e) {
                    $rows[] = [$queueName, 'Tube is empty'];
                    continue;
                }
                $rows[] = [$queueName, "current-jobs-ready: " . $stats['current-jobs-ready']];
                $rows[] = ['', "current-jobs-reserved: " . $stats['current-jobs-reserved']];
                $rows[] = ['', "current-watching: " . $stats['current-watching']];
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
}
