<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Component\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends ConsoleApplication
{
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('sched-config', 'c', InputOption::VALUE_REQUIRED, 'Path to a config file'));

        return $definition;
    }
}
