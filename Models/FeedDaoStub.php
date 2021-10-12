<?php

namespace Models;


class FeedDaoStub implements FeedDaoInterface
{
    private $liveData;

    private $devData;

    private $live = false;

    private function getData():string {
        if ($this->live) {
            return "liveData";
        }
        return "devData";
    }

    public function __construct(bool $live = false)
    {
        $this->live = $live;
        if ($live) {
            $this->liveData = [];
            $data = $this->getData();
            $this->$data[1] = new FeedDto(1, "Michaela Pfleger", [], new SourceDto("@michaela", 1229), new SourceDto("@michaela_tiktok", 19));
            $this->$data[2] = new FeedDto(2, 'Aelroy Mergulhão', [new PostDto(2, 'https://www.instagram.at/aelroy'), new PostDto(3, "https://www.tiktok.com/aelroy")], new SourceDto("@aelroy", 39393), new SourceDto("aelroy_tiktok", 10585));
            $this->$data[3] = new FeedDto(3, 'Ismail Hanli', [], new SourceDto("@ismail", 258));
        } else {
            $this->devData = [];
        }
    }

    public function create(FeedDto $feedDto)
    {
        $data = $this->getData();
        $this->$data[$feedDto->getId()] = $feedDto;
    }

    public function getFullFeedById(int $id):FeedDto
    {
        $data = $this->getData();
        if (isset($this->$data[$id])) {
            return $this->$data[$id];
        }
        throw new \Exception('Feed not Found');
    }

    public function getFeedWithInstagramById(int $id):FeedDto
    {
        $data = $this->getData();
        if (isset($this->$data[$id])) {
            /**
             * @var FeedDto $feed;
             */
            $feed = $this->$data[$id];
            $feed->setTiktokSource(null);
            return $feed;
        }

        throw new \Exception('Feed not Found');
    }

    public function getFeedWithTiktokById(int $id):FeedDto
    {
        $data = $this->getData();
        if (isset($this->$data[$id])) {
            /**
             * @var FeedDto $feed;
             */
            $feed = $this->$data[$id];
            $feed->setInstagramSource(null);
            return $feed;
        }

        throw new \Exception('Feed not Found');
    }

    public function getPostsByFeedId(int $id, int $limit = null):array
    {
        $posts = [];
        $data = $this->getData();
        if (isset($this->$data[$id])) {
            /**
             * @var FeedDto $feed;
             */
            $feed = $this->$data[$id];
            $feedPosts = $feed->getPosts();
            if ($limit) {
                $feedPosts = array_slice($feedPosts, 0, $limit);
            }
            $posts = $feedPosts;

        }
        return $posts;
    }


}