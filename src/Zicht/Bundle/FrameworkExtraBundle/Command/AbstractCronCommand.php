<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use \Exception;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Processor\MemoryUsageProcessor;
use \Monolog\Processor\MemoryPeakUsageProcessor;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

/**
 * Simple utility class for console applications. Uses Monolog for logging and error/exception reporting.
 */
abstract class AbstractCronCommand extends ContainerAwareCommand
{
    /**
     * If the application was neatly cleaned up, this is set to true, and the endOfScript() method will not issue
     * an error
     *
     * @var bool
     */
    private $isFinishedCleanly = false;

    /**
     * Logger instance
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Initialize the logger and attach it to error/exception handling and the clean shutdown function.
     *
     * @param \Monolog\Logger $logger
     * @param int $verbosity
     * @param bool $paranoid Whether or not non-clean shutdown should be logged.
     * @return void
     */
    public function setLogger(Logger $logger, $verbosity = 0, $paranoid = true)
    {
        $this->logger = $logger;
        if ($paranoid) {
            register_shutdown_function(array($this, 'endOfScript'));
        }
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));

        if ($verbosity == OutputInterface::VERBOSITY_VERBOSE) {
            $logger->pushHandler(
                new StreamHandler(fopen('php://stdout', 'w'), Logger::DEBUG, false)
            );
            $logger->pushProcessor(new MemoryUsageProcessor);
            $logger->pushProcessor(new MemoryPeakUsageProcessor);
        }
        if ($verbosity == OutputInterface::VERBOSITY_NORMAL) {
            $logger->pushHandler(
                new StreamHandler(fopen('php://stdout', 'w'), Logger::INFO, false)
            );
        }
        $logger->pushHandler(new StreamHandler(fopen('php://stderr', 'w'), Logger::WARNING));
    }


    /**
     * Exception handler; will log the exception and exit the script.
     *
     * @param \Exception $exception
     * @return void
     */
    public function exceptionHandler(Exception $exception)
    {
        $this->logger->addError($exception->getMessage(), array($exception->getFile(), $exception->getLine()));
        exit(-1);
    }


    /**
     * Error handler; will log the error and exit the script.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $file
     * @param int $line
     * @return void
     */
    public function errorHandler($errno, $errstr, $file, $line)
    {
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
            case E_RECOVERABLE_ERROR:
                $this->logger->addError($errstr, array($file, $line));
                exit();
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $this->logger->addWarning($errstr, array($file, $line));
                break;
            default:
                $this->logger->addInfo($errstr, array($file, $line));
                break;
        }
    }


    /**
     * Triggered on shutddown of the script.
     *
     * @return void
     */
    public function endOfScript()
    {
        if (!$this->isFinishedCleanly) {
            $this->logger->addCritical('Unexpected end of script!');
        }
    }


    /**
     * Tells the app that the end was reached without trouble.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->isFinishedCleanly = true;
        restore_error_handler();
        restore_exception_handler();
    }


    /**
     * Run the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->isFinishedCleanly = false;
        $ret                     = parent::run($input, $output);
        $this->cleanup();

        return $ret;
    }
}
