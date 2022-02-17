<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Command;

use Cron\CronExpression;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCron extends Command
{
    protected static $defaultName = 'cron:process';

    /**
     * @Inject({"config": "config"})
     *
     * @param array{
     *      'pheanstalk': array<string, mixed>,
     *      'cron': array<
     *          int,
     *          array{'name': string, 'expression': string, 'worker': string|callable, 'options': null|array<string, mixed>}
     *      >,
     *      'queues': array<string, array{'worker': string|callable}>,
     *      'config': array{'path': string}
     * } $config 
     */
    public function __construct(
        protected array $config,
        protected ContainerInterface $container
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Reads in the cron from config and runs anything that is due');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        foreach ($this->config['cron'] as $cronJob) {
            if ((new CronExpression($cronJob['expression']))->isDue()) {
                if ($verbose) $output->writeln($cronJob['name'] . ' is due, running');
                $worker = $cronJob['worker'];
                if (is_string($worker)) {
                    /** @var callable */
                    $worker = $this->container->get($worker);
                }
                $cronJob['options'] = $cronJob['options'] ?? [];
                $worker(...$cronJob['options']);
            }
        }

        return Command::SUCCESS;
    }
}
