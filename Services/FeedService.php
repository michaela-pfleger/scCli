<?php

namespace Services;

use Models\FeedDaoInterface;

class FeedService
{

    /**
     * @var FeedDaoInterface
     */
    private $liveFeedDao;

    /**
     * @var FeedDaoInterface
     */
    private $devFeedDao;

    public function __construct(FeedDaoInterface $liveFeedDao, FeedDaoInterface $devFeedDao)
    {
        $this->liveFeedDao = $liveFeedDao;
        $this->devFeedDao = $devFeedDao;
    }

    public function copy(int $id, bool $onlyInstagram = false, bool $onlyTiktok = false, int $includePosts = null)
    {
        if ($id < 1) {
            throw new \InvalidArgumentException("No valid ID given");
        }
        if ($onlyTiktok && $onlyInstagram) {
            throw new \InvalidArgumentException("Only one option allowed");
        }
        if ($includePosts < 0) {
            throw new \InvalidArgumentException("No valid post limit given");
        }
        if ($onlyInstagram) {
            $feed = $this->liveFeedDao->getFeedWithInstagramById($id);
        } else if ($onlyTiktok) {
            $feed = $this->liveFeedDao->getFeedWithTiktokById($id);
        } else {
            $feed = $this->liveFeedDao->getFullFeedById($id);
        }

        if ((!$onlyInstagram && !$onlyTiktok) || $includePosts) {
            if ($posts = $this->liveFeedDao->getPostsByFeedId($id, $includePosts)) {
                $feed->setPosts($posts);
            }
        }

        $this->devFeedDao->create($feed);
    }
}