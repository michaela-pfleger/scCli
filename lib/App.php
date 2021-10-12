<?php

namespace ScCli;

use Models\FeedDao;
use ScCli\CliPrinter;
use Services\SetupService;
use Services\FeedService;
use mysqli;
require_once('./dbconf.php');

class App
{
    /**
     * @var CliPrinter
     */
    protected $printer;

    /**
     * @var SetupService
     */
    private $setupService;

    /**
     * @var array
     */
    protected $registry = [];

    protected $allowedOptions = [
        'only',
        'include-posts'
    ];


    public function __construct()
    {
        $this->printer = new CliPrinter();
        $this->setupService = new SetupService();
    }

    /**
     * @param $name
     * @param $callable
     */
    public function registerCommand($name, $callable)
    {
        $this->registry[$name] = $callable;
    }

    /**
     * @param $command
     * @return mixed|null
     */
    public function getCommand($command)
    {
        return isset($this->registry[$command]) ? $this->registry[$command] : null;
    }

    /**
     * @return CliPrinter
     */
    public function getPrinter():CliPrinter
    {
        return $this->printer;
    }

    /**
     * @param array $argv
     */
    public function runCommand(array $argv)
    {
        $command_name = "help";

        if (isset($argv[1])) {
            $command_name = $argv[1];
        }

        $command = $this->getCommand($command_name);
        if ($command === null) {
            $this->getPrinter()->display("[ERROR]: Command \"$command_name\" not found.");
            exit;
        }

        call_user_func($command, $argv);
    }

    public function setupDatabases()
    {
        $live = $this->createConnection(DB_HOST_LIVE, DB_USER_LIVE, DB_PASS_LIVE);
        $dev = $this->createConnection(DB_HOST_DEV, DB_USER_DEV, DB_PASS_DEV);
        $this->setupService->setupDatabase($live, DB_NAME_LIVE); // this step creates the live database and fills it with test data - skip if database already exists
        $this->setupService->setupDatabase($dev, DB_NAME_DEV, true);
        $this->closeConnection($live);
        $this->closeConnection($dev);
    }

    /**
     * @param array $argv
     */
    public function copy(array $argv)
    {
        try {
            $id = $this->validateInputOptions($argv);

            $live = $this->createConnection(DB_HOST_LIVE, DB_USER_LIVE, DB_PASS_LIVE, DB_NAME_LIVE);
            $dev = $this->createConnection(DB_HOST_DEV, DB_USER_DEV, DB_PASS_DEV, DB_NAME_DEV);

            $liveFeedDao = new FeedDao($live);
            $devFeedDao = new FeedDao($dev);

            $includePosts = isset($argv['include-posts']) ? $argv['include-posts'] : null;
            $onlyInstagram = isset($argv['only']) && $argv['only'] == 'instagram' ? true : false;
            $onlyTiktok = isset($argv['only']) && $argv['only'] == 'tiktok' ? true : false;

            $feedService = new FeedService($liveFeedDao, $devFeedDao);

            $feedService->copy($id, $onlyInstagram, $onlyTiktok, $includePosts);

            $this->closeConnection($live);
            $this->closeConnection($dev);
            $this->getPrinter()->display("[SUCCESS]");
        } catch (\Throwable $throwable) {
            $this->getPrinter()->display("[ERROR]: {$throwable->getMessage()}");
//            $this->getPrinter()->display("[STACK]: {$throwable->getTraceAsString()}");
        }
    }

    public function help()
    {
        $this->getPrinter()->display("[HELP]: Following Commands are registered:");
        foreach ($this->registry as $key => $command) {
            $this->getPrinter()->display($key);
        }
    }

    public function getAllowedOptions()
    {
        return $this->allowedOptions;
    }

    /**
     * @param array $argv
     * @return int|null|string
     * @throws \Exception
     */
    public function validateInputOptions(array &$argv)
    {
        $id = null;
        $options = [];
        $allowedOptions = $this->getAllowedOptions();
        foreach ($argv as $argument) {
            if (is_numeric($argument)) {
                $id = $argument;
            } else if (str_starts_with($argument, "--")) {
                $split = explode("=", trim($argument, "--"));
                if (in_array($split[0], $allowedOptions)) {
                    $options[$split[0]] = $split[1];
                } else {
                    throw new \InvalidArgumentException("Option {$split[0]} not allowed");
                }
            }
        }
        if ($id < 1) {
            throw new \InvalidArgumentException("No valid ID given");
        }
        $argv = $options;
        return $id;
    }

    /**
     * @param string $servername
     * @param string $username
     * @param string $password
     * @param string|null $dbname
     * @return mysqli
     * @throws \Exception
     */
    private function createConnection(string $servername, string $username, string $password, string $dbname = null):mysqli
    {
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
            throw new \Exception("Could not connect to database");
        }
        if ($dbname) {
            $conn->select_db($dbname);
        }
        return $conn;
    }


    /**
     * @param mysqli $connection
     */
    private function closeConnection(mysqli $connection)
    {
        mysqli_close($connection);
    }

}