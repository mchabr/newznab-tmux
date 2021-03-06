<?php

namespace nntmux;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\GitProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Show log message to CLI/Web and log it to a file.
 * Turn these on in automated.config.php.
 *
 * @example usage:
 *
 *          (in method, LOG_INFO would be the severity of your error, see below)
 *          $this->Logger->start("MyClassName", "MyMethodName", "My debug message.", LOG_INFO);
 */
class Logger
{
    // You can use these constants when using the start method.
    const LOG_FATAL = 1; // Fatal error, the program exited.
    const LOG_ERROR = 2; // Recoverable error.
    const LOG_WARNING = 3; // Warnings.
    const LOG_NOTICE = 4; // Notices.
    const LOG_INFO = 5; // Info message, not important.
    const LOG_SQL = 6; // Full SQL query when it fails.

    /**
     * Name of class we are currently logging.
     * @var string
     */
    private $class;

    /**
     * Name of method we are currently logging.
     * @var string
     */
    private $method;

    /**
     * The log message.
     * @var string
     */
    private $logMessage = '';

    /**
     * Severity level.
     * @var string
     */
    private $severity = '';

    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var LineFormatter
     */
    private $formatter;

    /**
     * @var bool
     */
    private $outputCLI;

    /**
     * Is this the windows O/S?
     * @var bool
     */
    private $isWindows;

    /**
     * Unix time instance was created.
     * @var int
     */
    private $timeStart;

    /**
     * How many old logs can we have max in the logs folder.
     * (per log type, ex.: debug can have x logs, not_yEnc can have x logs, etc).
     * @var int
     */
    private $maxLogs;

    /**
     * Max log size in MegaBytes.
     * @var int
     */
    private $maxLogSize;

    /**
     * Current name of the log file.
     * @var string
     */
    private $currentLogName;

    /**
     * Current folder to store log files.
     * @var string
     */
    private $currentLogFolder;

    /**
     * Show memory usage in log/cli out?
     * @var bool
     */
    private $showMemoryUsage;

    /**
     * Show CPU load in log/cli out?
     * @var bool
     */
    private $showCPULoad;

    /**
     * Show running time of script on log/cli out?
     * @var bool
     */
    private $showRunningTime;

    /**
     * Show resource usages on log/cli out?.
     * @var bool
     */
    private $showResourceUsage;

    /**
     * Constructor.
     *
     * @param array $options (Optional) Class instances.
     *                       (Optional) Folder to store log files in.
     *                       (Optional) Filename of log, must be alphanumeric (a-z 0-9) and contain no file extensions.
     *
     * @throws LoggerException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        if (! NN_LOGGING && ! NN_DEBUG) {
            return;
        }

        $defaults = [
            'ColorCLI'    => null,
            'LogFolder'   => '',
            'LogFileName' => '',
        ];
        $options += $defaults;

        $this->getSettings();

        $this->currentLogFolder = (
            ! empty($options['LogFolder'])
                ? $options['LogFolder']
                : $this->currentLogFolder
        );

        $this->currentLogName = (
            ! empty($options['LogFileName'])
                ? $options['LogFileName']
                : $this->currentLogName
        ).'.log';

        $this->outputCLI = (strtolower(PHP_SAPI) === 'cli');
        $this->isWindows = stripos(PHP_OS, 'win') === 0;
        $this->timeStart = time();

        $this->logger = new Monolog('nntmux');
        $this->formatter = new LineFormatter(null, 'd/M/Y H:i', false, true);
        $this->introspection = new IntrospectionProcessor();
        $this->gitprocessor = new GitProcessor();
        $this->memoryUsage = new MemoryUsageProcessor();
        $this->streamHandler = new StreamHandler($this->currentLogFolder.$this->currentLogName, Monolog::DEBUG);
        $this->streamHandler->setFormatter($this->formatter);
        $this->logger->pushHandler($this->streamHandler);
        $this->logger->pushProcessor($this->introspection);
        $this->logger->pushProcessor($this->gitprocessor);
        if ($this->showMemoryUsage === true) {
            $this->logger->pushProcessor($this->memoryUsage);
        }
    }

    /**
     * Public method for logging and/or echoing log messages.
     *
     * @param string $class    The name of the class.
     * @param string $method   The method this is coming from.
     * @param string $message  The message to log/echo.
     * @param int    $severity How severe is this message?
     *               1 Fatal    - The program had to stop (exit).
     *               2 Error    - Something went very wrong but we recovered.
     *               3 Warning  - Not an error, but something we can probably fix.
     *               4 Notice   - User errors - the user did not enable any groups for example.
     *               5 Info     - General info, like we logged in to usenet for example.
     *               6 Query    - Failed SQL queries. (the full query).
     */
    public function log($class, $method, $message, $severity): void
    {
        // Check if echo debugging or logging is on.
        if (! NN_DEBUG && ! NN_LOGGING) {
            return;
        }

        $this->severity = $severity;
        // Check the severity of the message, if disabled return, if enabled create part of the log message.
        if (! $this->checkSeverity()) {
            return;
        }

        $this->class = $class;
        $this->method = $method;
        $this->logMessage = $message;

        $this->formLogMessage();
        $this->echoMessage();
        $this->logMessage();
    }

    /**
     * Get resource usage string.
     *
     * @return bool|string
     */
    public function getResUsage()
    {
        if (! $this->isWindows) {
            $usage = getrusage();

            return
                'USR: '.$this->formatTimeString($usage['ru_utime.tv_sec']).
                ' SYS: '.$this->formatTimeString($usage['ru_stime.tv_sec']).
                ' FAULTS: '.$usage['ru_majflt'].
                ' SWAPS: '.$usage['ru_nswap'];
        }

        return false;
    }

    /**
     * Get system load.
     *
     * @return string|bool
     */
    public function getSystemLoad()
    {
        if (! $this->isWindows) {
            $string = '';
            // Fix for single digits (2) or single float (2.1).
            foreach (sys_getloadavg() as $load) {
                $strLen = strlen($load);
                if ($strLen === 1) {
                    $string .= $load.'.00,';
                } elseif ($strLen === 3) {
                    $string .= str_pad($load, 4, '0', STR_PAD_RIGHT).',';
                } else {
                    $string .= $load.',';
                }
            }

            return substr($string, 0, -1);
        }

        return false;
    }

    /**
     * Changes the location of the log file.
     *
     * @param string $folder   Folder where the log should be stored.
     * @param string $fileName Name of the file (must be alphanumeric and contain no file extensions).
     *
     * @throws \nntmux\LoggerException
     */
    public function changeLogFileLocation($folder, $fileName): void
    {
        $this->currentLogFolder = $folder;
        $this->currentLogName = $fileName;
    }

    /**
     * Get the log folder, log name and full path to the default log.
     *
     * @return array
     * @static
     */
    public static function getDefaultLogPaths()
    {
        $defaultLogName = (defined('NN_LOGGING_LOG_NAME') ? NN_LOGGING_LOG_NAME : 'nntmux');
        $defaultLogName = (ctype_alnum($defaultLogName) ? $defaultLogName : 'nntmux');
        $defaultLogFolder = (defined('NN_LOGGING_LOG_FOLDER') && is_dir(NN_LOGGING_LOG_FOLDER) ? NN_LOGGING_LOG_FOLDER : NN_LOGS);
        $defaultLogFolder = (in_array(substr($defaultLogFolder, -1), ['/', '\\'], false) ? $defaultLogFolder : $defaultLogFolder.DS);

        return [
            'LogFolder' => $defaultLogFolder,
            'LogName'   => $defaultLogName,
            'LogPath'   => $defaultLogFolder.$defaultLogName.'.log',
        ];
    }

    /**
     * Get/set all settings.
     */
    private function getSettings()
    {
        $this->maxLogs = (defined('NN_LOGGING_MAX_LOGS') ? NN_LOGGING_MAX_LOGS : 20);
        $this->maxLogs = ($this->maxLogs < 1 ? 20 : $this->maxLogs);
        $this->maxLogSize = (defined('NN_LOGGING_MAX_SIZE') ? NN_LOGGING_MAX_SIZE : 30);
        $this->maxLogSize = ($this->maxLogSize < 1 ? 30 : $this->maxLogSize);
        $this->showMemoryUsage = (bool) (defined('NN_LOGGING_LOG_MEMORY_USAGE') ? NN_LOGGING_LOG_MEMORY_USAGE : true);
        $this->showCPULoad = (bool) (defined('NN_LOGGING_LOG_CPU_LOAD') ? NN_LOGGING_LOG_CPU_LOAD : true);
        $this->showRunningTime = (bool) (defined('NN_LOGGING_LOG_RUNNING_TIME') ? NN_LOGGING_LOG_RUNNING_TIME : true);
        $this->showResourceUsage = (bool) (defined('NN_LOGGING_LOG_RESOURCE_USAGE') ? NN_LOGGING_LOG_RESOURCE_USAGE : false);
        $paths = self::getDefaultLogPaths();
        $this->currentLogName = $paths['LogName'];
        $this->currentLogFolder = $paths['LogFolder'];
    }

    /**
     * Log message to file.
     */
    private function logMessage()
    {
        // Check if debug logging is on.
        if (! NN_LOGGING) {
            return;
        }

        $this->logger->debug($this->logMessage);
    }

    /**
     * Echo log message to CLI or web.
     */
    private function echoMessage()
    {
        if (! NN_DEBUG) {
            return;
        }

        // Check if this is CLI or web.
        if ($this->outputCLI) {
            ColorCLI::doEcho(ColorCLI::debug($this->logMessage));
        } else {
            echo '<pre>'.$this->logMessage.'</pre><br />';
        }
    }

    /**
     * Creates the message object for the log message.
     */
    private function formLogMessage()
    {
        $pid = getmypid();

        $this->logMessage =
            // The severity.
            $this->severity.

            // Average system load.
            (($this->showCPULoad && ! $this->isWindows) ? ' ['.$this->getSystemLoad().']' : '').

            // Script running time.
            ($this->showRunningTime ? ' ['.$this->formatTimeString(time() - $this->timeStart).']' : '').

            // Resource usage (user time, system time, major page faults, memory swaps).
            (($this->showResourceUsage && ! $this->isWindows) ? ' ['.$this->getResUsage().']' : '').

            // Running process id.
            ($pid ? ' [PID:'.$pid.']' : '').

            // The class/function.
            ' ['.$this->class.'.'.$this->method.']'.

            ' ['.

            // Now reformat the log message, first stripping leading spaces.
            trim(

                // Removing 2 or more spaces.
                preg_replace(
                    '/\s{2,}/',
                    ' ',

                    // Removing new lines and carriage returns.
                    str_replace(["\n", '\n', "\r", '\r'], ' ', $this->logMessage)
                )
            ).

            ']';

        return $this->logMessage;
    }

    /**
     * Convert seconds to hours minutes seconds string.
     *
     * @param int $seconds
     *
     * @return string
     */
    private function formatTimeString($seconds)
    {
        $time = '';
        if ($seconds > 3600) {
            $time .= str_pad(round(($seconds % 86400) / 3600), 2, '0', STR_PAD_LEFT).'H:';
        } else {
            $time .= '00H:';
        }
        if ($seconds > 60) {
            $time .= str_pad(round(($seconds % 3600) / 60), 2, '0', STR_PAD_LEFT).'M:';
        } else {
            $time .= '00M:';
        }
        $time .= str_pad($seconds % 60, 2, '0', STR_PAD_LEFT).'S';

        return $time;
    }

    /**
     * Check if the user wants to echo or log this message, form part of the log message at the same time.
     *
     * @return bool
     */
    private function checkSeverity()
    {
        switch ($this->severity) {
            case self::LOG_FATAL:
                if (NN_LOGFATAL) {
                    $this->severity = '[FATAL] ';

                    return true;
                }

                return false;
            case self::LOG_ERROR:
                if (NN_LOGERROR) {
                    $this->severity = '[ERROR] ';

                    return true;
                }

                return false;
            case self::LOG_WARNING:
                if (NN_LOGWARNING) {
                    $this->severity = '[WARN]  ';

                    return true;
                }

                return false;
            case self::LOG_NOTICE:
                if (NN_LOGNOTICE) {
                    $this->severity = '[NOTICE]';

                    return true;
                }

                return false;
            case self::LOG_INFO:
                if (NN_LOGINFO) {
                    $this->severity = '[INFO]  ';

                    return true;
                }

                return false;
            case self::LOG_SQL:
                if (NN_LOGQUERIES) {
                    $this->severity = '[SQL]   ';

                    return true;
                }

                return false;
            default:
                return false;
        }
    }
}
