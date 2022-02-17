<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunManager extends Command
{
    protected static $defaultName = 'manager:run';

    /**
     * @Inject({"config": "config"})
     *
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
        $this->setHelp('Starts up the manager process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        $jobs = [];

        /** @phpstan-ignore-next-line */
        while (true) {
            $total = 0;
            foreach ($jobs as $queueName => $procs) {
                $total += count($jobs[$queueName]);
            }

            if ($total >= $this->config['manager']['max_workers']) {
                if ($verbose) $output->writeln("Reached the total maximum of workers");
                goto checkProcesses;
            }

            foreach ($this->config['queues'] as $queueName => $data) {
                if (!isset($jobs[$queueName])) {
                    $jobs[$queueName] = [];
                }

                try {
                    $stats = $this->pheanstalk->statsTube($queueName);
                } catch (ServerException $e) {
                    if ($verbose) $output->writeln("Tube is empty or does not existing, skipping");
                    continue;
                }
                if ($verbose) $output->writeln("Checking to see if we need any workers");

                $neededWorkers = ceil((int) $stats['current-jobs-ready'] / 5);
                if (count($jobs[$queueName]) < $neededWorkers) {
                    if ($verbose) $output->writeln("Need " . $neededWorkers);

                    if (count($jobs[$queueName]) >= $this->config['manager']['max_workers_per_tube']) {
                        if ($verbose) $output->writeln($queueName . " has reached max workers ");
                        break;
                    }

                    if ($neededWorkers > $this->config['manager']['max_workers_per_tube']) {
                        if ($verbose) $output->writeln('Capping needed workers at ' . $this->config['manager']['max_workers_per_tube']);
                        $neededWorkers = $this->config['manager']['max_workers_per_tube'];
                    }

                    for ($i = 0; $i <= $neededWorkers - count($jobs[$queueName]); $i++) {
                        if ($verbose) $output->writeln("Opening job");
                        $command = [
                            'php',
                            'sched-manager',
                            'queue:process',
                            '--config',
                            $this->config['config']['path'],
                            '--',
                            $queueName
                        ];

                        /** @phpstan-ignore-next-line */
                        $proc = new Process($command, realpath(__DIR__ . '/../../bin'));
                        $proc->start();
                        $jobs[$queueName][] = ['proc' => $proc];
                    }
                }
            }

            checkProcesses:
            foreach ($this->config['queues'] as $queueName => $data) {
                if ($verbose) $output->writeln("Checking job statuses");
                foreach ($jobs[$queueName] as $id => $procInfo) {
                    if (!$procInfo['proc']->isRunning()) {
                        unset($jobs[$queueName][$id]);
                        if ($verbose) $output->writeln("Closed job " . $id . ' from ' . $queueName);
                    }
                }
            }

            if ($verbose) $output->writeln("Sleeping for a bit");
            sleep(5);
        }
    }
}
