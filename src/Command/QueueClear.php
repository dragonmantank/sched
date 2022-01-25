<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueClear extends Command
{
    protected static $defaultName = 'queue:clear';

    public function __construct(
        protected Pheanstalk $pheanstalk
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Clears all jobs from a queue')
            ->addArgument('queueName', InputArgument::REQUIRED, 'Queue to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string */
        $queueName = $input->getArgument('queueName');

        try {
            $this->pheanstalk->watch($queueName);
            while ($job = $this->pheanstalk->reserveWithTimeout(0)) {
                $this->pheanstalk->delete($job);
            }
        } catch (ServerException $e) {
            $output->writeln('Error reading the time');
            return Command::FAILURE;
        }

        $output->writeln('Queue has been cleared');
        return Command::SUCCESS;
    }
}
