<?php

namespace Services;

use Models\FeedDao;
use Models\FeedDto;
use Models\PostDto;
use Models\SourceDto;
use scCli\CliPrinter;
use mysqli;

class SetupService
{
    public function __construct()
    {
        $this->printer = new CliPrinter();
    }

    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @param mysqli $connection
     * @param string $dbname
     * @param bool $truncate
     */
    public function setupDatabase(mysqli $connection, string $dbname, $truncate = false)
    {
        $this->createDatabase($connection, $dbname, $truncate);

    }

    /**
     * @param mysqli $connection
     * @param string $db
     * @param bool $truncate
     */
    private function createDatabase(mysqli $connection, string $db, bool $truncate)
    {
        $this->executeSqlQuery($connection, "CREATE DATABASE IF NOT EXISTS {$db}");
        $connection->select_db($db);
        $this->createTables($connection, $db);
        if ($truncate) {
            $this->truncateTables($connection); // clear relevant tables in dev-database so there are no problems with ids and primary keys
        } else {
            $this->createData($connection); // create data for the live-database - skip this part if live db already exists
        }
    }

    /**
     * @param mysqli $connection
     */
    private function truncateTables(mysqli $connection)
    {
        $sqlStatements = [
            "SET FOREIGN_KEY_CHECKS = 0;",
            "TRUNCATE TABLE `tiktok_sources`",
            "TRUNCATE TABLE `instagram_sources`",
            "TRUNCATE TABLE `posts`",
            "TRUNCATE TABLE `feeds`",
        ];
        foreach ($sqlStatements as $sqlStatement) {
            $this->executeSqlQuery($connection, $sqlStatement);
        }
    }

    /**
     * @param mysqli $connection
     * @param string $db
     */
    private function createTables(mysqli $connection, string $db)
    {
        $sqlStatements = [
            "CREATE TABLE IF NOT EXISTS `{$db}`.`feeds` ( `ID` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL , PRIMARY KEY (`ID`));",
            "CREATE TABLE IF NOT EXISTS `{$db}`.`posts` ( `ID` INT NOT NULL AUTO_INCREMENT , `url` VARCHAR(255) NOT NULL , `feeds_ID` INT NOT NULL, PRIMARY KEY (`ID`), FOREIGN KEY (`feeds_ID`) REFERENCES `feeds` (`ID`) ON DELETE CASCADE) ENGINE=INNODB;",
            "CREATE TABLE IF NOT EXISTS `{$db}`.`instagram_sources` ( `name` VARCHAR(255) NOT NULL , `fan_count` INT NOT NULL , `feeds_ID` INT NOT NULL, UNIQUE (`name`), FOREIGN KEY (`feeds_ID`) REFERENCES `feeds` (`ID`) ON DELETE CASCADE) ENGINE=INNODB;",
            "CREATE TABLE iF NOT EXISTS `{$db}`.`tiktok_sources` ( `name` VARCHAR(255) NOT NULL , `fan_count` INT NOT NULL , `feeds_ID` INT NOT NULL, UNIQUE (`name`), FOREIGN KEY (`feeds_ID`) REFERENCES `feeds` (`ID`) ON DELETE CASCADE) ENGINE=INNODB;",
        ];

        foreach ($sqlStatements as $sqlStatement) {
            $this->executeSqlQuery($connection, $sqlStatement);
        }
    }

    /**
     * @param mysqli $connection
     * @param string $sql
     * @return bool|\mysqli_result
     */
    private function executeSqlQuery(mysqli $connection, string $sql)
    {
        return mysqli_query($connection, $sql);
    }

    /**
     * @param mysqli $connection
     */
    private function createData(mysqli $connection)
    {
        $feedDao = new FeedDao($connection);
        $feedDao->create(new FeedDto(1, 'Michaela Pfleger', [new PostDto(1, 'https://www.instagram.at/michaela')], new SourceDto("@michaela",3), new SourceDto("michaela_tiktok", 10)));
        $feedDao->create(new FeedDto(2, 'Aelroy Mergulhão', [new PostDto(2, 'https://www.instagram.at/aelroy'), new PostDto(3, "https://www.tiktok.com/aelroy"), new PostDto(4, 'https://www.instagram.at/aelroy'), new PostDto(5, 'https://www.instagram.at/aelroy'), new PostDto(6, 'https://www.instagram.at/aelroy')], new SourceDto("@aelroy",39393), new SourceDto("aelroy_tiktok", 10585)));
        $feedDao->create(new FeedDto(3, 'Ismail Hanli', [new PostDto(7, 'https://www.instagram.at/ismail')], new SourceDto("@ismail",258)));
    }
}
