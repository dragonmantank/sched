<?php

declare(strict_types=1);

namespace Dragonmantank\Sched\Factory;

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

class ConfigFactory
{
    /** @return Mixed[] */
    public function __invoke(ContainerInterface $c): array
    {
        try {
            (Dotenv::createUnsafeImmutable([
                __DIR__ . '/../',
                __DIR__,
                getcwd() ?: '',
            ]))->load();
        } catch (\Exception) {
            // Do nothing, because there may not be a .env file
        }

        $defaultConfig = [
            'manager' => [
                'max_workers' => 10,
                'max_workers_per_tube' => 5,
            ],
            'messages' => [
                'jitter' => ['min' => 0, 'max' => 0],
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
        $index = 0;
        foreach ($_SERVER['argv'] as $index => $value) {
            if ($value === '--config' || $value === '-c') {
                $found = true;
                break;
            }
        }

        if ($found) {
            $path = realpath($_SERVER['argv'][$index + 1]);
            $config = include $path;
            $config['sched-config']['path'] = $path;
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
                    $config['sched-config']['path'] = $possibleLocation;
                    return array_merge($defaultConfig, $config);
                }
            }
        }

        throw new \RuntimeException('Unable to find a configuration file');
    }
}
