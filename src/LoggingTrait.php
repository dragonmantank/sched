<?php

declare(strict_types=1);

namespace Dragonmantank\Sched;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

trait LoggingTrait
{
    protected function log(OutputInterface $output, $level, string $message)
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }

        $timestamp = new \DateTimeImmutable();
        $message = '[' . $timestamp->format(\DateTimeInterface::ATOM) . '] ' . $message;
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
    }
}
