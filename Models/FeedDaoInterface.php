<?php

namespace Models;

interface FeedDaoInterface
{

    public function create(FeedDto $feedDto);

    public function getFullFeedById(int $id):FeedDto;

    public function getFeedWithInstagramById(int $id):FeedDto;

    public function getFeedWithTiktokById(int $id):FeedDto;

    public function getPostsByFeedId(int $id, int $limit = null):array;
}