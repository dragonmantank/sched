<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueuePeek extends Command
{
    protected static $defaultName = 'queue:peek';

    public function __construct(
        protected Pheanstalk $pheanstalk
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Returns the next payload in a queue')
            ->addArgument('queueName', InputArgument::REQUIRED, 'Queue to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string */
        $queueName = $input->getArgument('queueName');

        try {
            $this->pheanstalk->useTube($queueName);
            $job = $this->pheanstalk->peekReady();
        } catch (ServerException $e) {
            $output->writeln('Unable to get stats, tube does not exist');
            return Command::FAILURE;
        }
        if ($job) {
            /** @var string */
            $message = json_encode(json_decode($job->getData(), true), JSON_PRETTY_PRINT);
            $output->writeln($message);
        }
        return Command::SUCCESS;
    }
}
