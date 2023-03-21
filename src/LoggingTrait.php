<?php

declare(strict_types=1);

namespace Dragonmantank\Sched;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait LoggingTrait
{
    /** @param LogLevel::* $level */
    protected function log(OutputInterface $output, string $level, string $message): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }

        $timestamp = new \DateTimeImmutable();
        $message = '[' . $timestamp->format(\DateTimeInterface::ATOM) . '] [' . $level . '] ' . $message;

        // Log messages should always go to stderr, not stdout
        // https://pubs.opengroup.org/onlinepubs/9699919799/functions/stdin.html
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        if (($level === LogLevel::DEBUG) && $output->isDebug()) {
            $output->writeln($message);
            return;
        }

        if (($level === LogLevel::INFO) && $output->isVeryVerbose()) {
            $output->writeln($message);
            return;
        }

        if (($level === LogLevel::NOTICE) && $output->isVerbose()) {
            $output->writeln($message);
            return;
        }

        $errorLevels = [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
        ];
        
        if (in_array($level, $errorLevels)) {
            $output->writeln($message);
        }
    }
}
