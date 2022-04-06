<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Factory;

use Psr\Container\ContainerInterface;

class ConfigFactory
{
    public function __invoke(ContainerInterface $c): array
    {
        $defaultConfig = [
            'manager' => [
                'max_workers' => 10,
                'max_workers_per_tube' => 5,
            ],
            'pheanstalk' => [
                'host' => '127.0.0.1',
                'port' => 11300,
                'timeout' => 10,
            ],
        ];

        // No, getopt() nor Symfony's ArgV parser work correctly for parsing out
        // the config file at this stage, so we brute force it. This is a big issue
        // for the calls that the Manager makes, versus direct invocation.
        $found = false;
        foreach ($_SERVER['argv'] as $index => $value) {
            if ($value === '--config' || $value === '-c') {
                $found = true;
                break;
            }
        }

        if ($found) {
            $path = realpath($_SERVER['argv'][$index + 1]);
            $config = require_once $path;
            $config['config']['path'] = $path;
            return array_merge($defaultConfig, $config);
        } else {
            $paths = [
                getcwd() . '/.sched-manager.config.php',
                getcwd() . '/sched-manager.config.php',
                getcwd() . '/config/sched-manager.config.php',
            ];
            foreach ($paths as $possibleLocation) {
                if (is_file($possibleLocation)) {
                    $config = include $possibleLocation;
                    $config['config']['path'] = $possibleLocation;
                    return array_merge($defaultConfig, $config);
                }
            }
        }

        throw new \RuntimeException('Unable to find a configuration file');
    }
}
