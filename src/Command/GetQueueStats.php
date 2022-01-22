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

class GetQueueStats extends Command
{
    protected static $defaultName = 'queue:stats';

    public function __construct(
        protected Pheanstalk $pheanstalk
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Returns information about the queue')
            ->addArgument('queueName', InputArgument::REQUIRED, 'Queue to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('queueName');

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
        $table = new Table($output);
        $table
            ->setHeaders(['Variable', 'Value'])
            ->setRows($rows);
        $table->render();
        return Command::SUCCESS;
    }
}
