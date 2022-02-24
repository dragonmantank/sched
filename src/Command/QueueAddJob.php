<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueAddJob extends Command
{
    protected static $defaultName = 'queue:add-job';

    public function __construct(
        protected Pheanstalk $pheanstalk
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Returns the next payload in a queue')
            ->addOption('ttr', 't', InputOption::VALUE_REQUIRED, 'TTR for the job', PheanstalkInterface::DEFAULT_TTR)
            ->addArgument('queueName', InputArgument::REQUIRED, 'Queue to add job to')
            ->addArgument('payload', InputArgument::REQUIRED, 'String payload to add to queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tube = $this->pheanstalk->useTube($input->getArgument('queueName'));
        $job = $tube->put(data: $input->getArgument('payload'), ttr: (int) $input->getOption('ttr'));
        $output->writeln("Job " . $job->getId() . " added to queue " . $input->getArgument('queueName'));

        return Command::SUCCESS;
    }
}
