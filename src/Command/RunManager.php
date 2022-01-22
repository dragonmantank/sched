<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunManager extends Command
{
    protected static $defaultName = 'manager:run';

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
        while (true) {
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
                        $proc = new Process($command, realpath(__DIR__ . '/../../bin'));
                        $proc->start();
                        $jobs[$queueName][] = ['proc' => $proc];
                    }
                }

                if ($verbose) $output->writeln("Checking job statuses");
                foreach ($jobs[$queueName] as $id => $procInfo) {
                    if (!$procInfo['proc']->isRunning()) {
                        $output->writeln($procInfo['proc']->getOutput());
                        $output->writeln($procInfo['proc']->getErrorOutput());
                        unset($jobs[$queueName][$id]);
                        if ($verbose) $output->writeln("Closed Job");
                    }
                }
            }

            if ($verbose) $output->writeln("Sleeping for a bit");
            sleep(5);
        }
    }
}
