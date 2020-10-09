<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait CreateStdLoggerTrait
{
    /**
     * Returns a logger configured to use STDOUT and STDERR depending on $input and $output settings
     *
     * Use this method to construct a logger that sends log messages to stdout or stderr.  This
     * is especially useful for commands that are executed using crontab in combination with the
     * linux tool cronic.
     *
     * Usage:
     * ```php
     * class ExampleCommand extends AbstractCommand
     * {
     *     use CreateStdLoggerTrait;
     *
     *     protected function execute(InputInterface $input, OutputInterface $output)
     *     {
     *         $logger = $this->createStdLogger($output, 'console');
     *         $logger->debug(...);      // logs to stdout when -v or --verbose
     *         $logger->info(...);       // logs to stdout
     *         $logger->notice(...);     // logs to stdout
     *         $logger->warning(...);    // logs to stdout
     *         $logger->error(...);      // logs to stderr
     *         $logger->critical(...);   // logs to stderr even when -q (quiet)
     *         $logger->alert(...);      // logs to stderr even when -q (quiet)
     *         $logger->emergency(...);  // logs to stderr even when -q (quiet)
     *     }
     * }
     * ```
     */
    protected function createStdLogger(OutputInterface $output, string $name): LoggerInterface
    {
        $levelMap = [
            OutputInterface::VERBOSITY_QUIET => Logger::CRITICAL,
            OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
            OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
        ];
        $level = $levelMap[$output->getVerbosity()] ?: Logger::ERROR;

        $logger = new Logger($name);
        $logger->pushHandler(new FilterHandler(new StreamHandler(STDOUT, $level), Logger::DEBUG, Logger::ERROR - 1));
        $logger->pushHandler(new FilterHandler(new StreamHandler(STDERR, $level), Logger::ERROR, Logger::EMERGENCY));
        return $logger;
    }
}
