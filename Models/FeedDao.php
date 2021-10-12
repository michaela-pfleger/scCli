<?php

namespace Models;

use mysqli;

class FeedDao implements FeedDaoInterface
{
    /**
     * @var mysqli
     */
    private $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param FeedDto $feedDto
     */
    public function create(FeedDto $feedDto)
    {
        $this->createFeed($feedDto);
        $this->createPosts($feedDto);
        $this->createInstagramSource($feedDto);
        $this->createTikSource($feedDto);
    }

    /**
     * @param int $id
     * @return FeedDto
     * @throws \Exception
     */
    public function getFullFeedById(int $id):FeedDto
    {
        $feedResult = $this->executeSqlQueryPrepared($this->connection, "SELECT `feeds`.ID as feedsID, `feeds`.name as feedsName, instagram.name as instagramName, instagram.fan_count as instagramFanCount, tiktok.name as tiktokName, tiktok.fan_count as tiktokFanCount FROM `feeds` LEFT JOIN `instagram_sources` as instagram ON instagram.feeds_ID = `feeds`.ID LEFT JOIN `tiktok_sources` as tiktok ON tiktok.feeds_ID = `feeds`.ID WHERE `feeds`.ID = ?", [$id]);

        if (mysqli_num_rows($feedResult) > 0) {
            $row = mysqli_fetch_assoc($feedResult);
            $feedDto = new FeedDto($row['feedsID'], $row['feedsName']);

            if (isset($row['instagramName'])) {
                $instagramSource = new SourceDto($row['instagramName'], $row['instagramFanCount']);
                $feedDto->setInstagramSource($instagramSource);
            }

            if (isset($row['tiktokName'])) {
                $tiktokSource = new SourceDto($row['tiktokName'], $row['tiktokFanCount']);
                $feedDto->setTiktokSource($tiktokSource);
            }

            return $feedDto;
        }

        throw new \Exception('Feed not Found');

    }

    /**
     * @param int $id
     * @return FeedDto
     * @throws \Exception
     */
    public function getFeedWithInstagramById(int $id):FeedDto
    {
        $feedResult = $this->executeSqlQueryPrepared($this->connection, "SELECT `feeds`.ID as feedsID, `feeds`.name as feedsName, instagram.name as instagramName, instagram.fan_count as instagramFanCount FROM `feeds` LEFT JOIN `instagram_sources` as instagram ON instagram.feeds_ID = `feeds`.ID WHERE `feeds`.ID = ?", [$id]);

        if (mysqli_num_rows($feedResult) > 0) {
            $row = mysqli_fetch_assoc($feedResult);
            $feedDto = new FeedDto($row['feedsID'], $row['feedsName']);

            if (isset($row['instagramName'])) {
                $instagramSource = new SourceDto($row['instagramName'], $row['instagramFanCount']);
                $feedDto->setInstagramSource($instagramSource);
            }

            return $feedDto;
        }

        throw new \Exception('Feed not Found');

    }

    /**
     * @param int $id
     * @return FeedDto
     * @throws \Exception
     */
    public function getFeedWithTiktokById(int $id):FeedDto
    {
        $feedResult = $this->executeSqlQueryPrepared($this->connection, "SELECT `feeds`.ID as feedsID, `feeds`.name as feedsName, tiktok.name as tiktokName, tiktok.fan_count as tiktokFanCount FROM `feeds` LEFT JOIN `tiktok_sources` as tiktok ON tiktok.feeds_ID = `feeds`.ID WHERE `feeds`.ID = ?", [$id]);

        if (mysqli_num_rows($feedResult) > 0) {
            $row = mysqli_fetch_assoc($feedResult);
            $feedDto = new FeedDto($row['feedsID'], $row['feedsName']);

            if (isset($row['tiktokName'])) {
                $tiktokSource = new SourceDto($row['tiktokName'], $row['tiktokFanCount']);
                $feedDto->setTiktokSource($tiktokSource);
            }

            return $feedDto;
        }

        throw new \Exception('Feed not Found');

    }


    /**
     * @param FeedDto $feedDto
     */
    private function createFeed(FeedDto $feedDto)
    {
        $lastId = $this->executeSqlInsertIdPrepared($this->connection, "INSERT INTO `feeds` (`ID`, `name`) VALUES (?,?) ON DUPLICATE KEY UPDATE `name` = ?", [$feedDto->getId(), $feedDto->getName(), $feedDto->getName()]);
    }


    /**
     * @param FeedDto $feedDto
     */
    private function createPosts(FeedDto $feedDto)
    {
        $posts = $feedDto->getPosts();
        /**
         * @var PostDto $postDto
         */
        foreach ($posts as $postDto) {
            $this->executeSqlInsertIdPrepared($this->connection, "INSERT INTO `posts` (`ID`, `url`, `feeds_ID`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `url` = ?", [$postDto->getId(), $postDto->getUrl(), $feedDto->getId(), $postDto->getUrl()]);
        }
    }

    /**
     * @param FeedDto $feedDto
     */
    private function createInstagramSource(FeedDto $feedDto)
    {
        /** @var SourceDto $instagramSource */
        $instagramSource = $feedDto->getInstagramSource();
        if ($instagramSource) {
            $this->executeSqlInsertIdPrepared($this->connection, "INSERT INTO `instagram_sources` (`name`, `fan_count`, `feeds_ID`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `fan_count` = ?", [$instagramSource->getName(), $instagramSource->getFanCount(), $feedDto->getId(), $instagramSource->getFanCount()]);
        }
    }

    /**
     * @param FeedDto $feedDto
     */
    private function createTikSource(FeedDto $feedDto)
    {
        /** @var SourceDto $tiktokSource */
        $tiktokSource = $feedDto->getTiktokSource();
        if ($tiktokSource) {
            $this->executeSqlInsertIdPrepared($this->connection, "INSERT INTO `tiktok_sources` (`name`, `fan_count`, `feeds_ID`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `fan_count` = ?", [$tiktokSource->getName(), $tiktokSource->getFanCount(), $feedDto->getId(), $tiktokSource->getFanCount()]);
        }
    }

    /**
     * @param int $id
     * @param int|null $limit
     * @return array
     */
    public function getPostsByFeedId(int $id, int $limit = null):array
    {
        $posts = [];
        $sql = "SELECT `posts`.ID as postsID, `posts`.url as postsUrl FROM `posts` WHERE `posts`.feeds_ID = ?";
        $params = [$id];
        if ($limit) {
            $sql .= " LIMIT ?,?";
            $params[] = 0;
            $params[] = $limit;
        }
        $postsResult = $this->executeSqlQueryPrepared($this->connection, $sql, $params);
        if (mysqli_num_rows($postsResult) > 0) {
            while ($row = mysqli_fetch_assoc($postsResult)) {
                $postDto = new PostDto($row['postsID'], $row['postsUrl']);
                $posts[] = $postDto;
            }
        }
        return $posts;
    }

    /**
     * @param mysqli $connection
     * @param string $sql
     * @param array $params
     * @return bool|\mysqli_result
     */
    private function executeSqlQueryPrepared(mysqli $connection, string $sql, array $params)
    {
        $stmt = $connection->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    /**
     * @param mysqli $connection
     * @param string $sql
     * @param array $params
     * @return int|string
     */
    private function executeSqlInsertIdPrepared(mysqli $connection, string $sql, array $params)
    {
        $stmt = $connection->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return mysqli_insert_id($connection);
    }


}