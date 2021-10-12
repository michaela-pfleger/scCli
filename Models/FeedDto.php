<?php

namespace Models;

class FeedDto
{

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var array
     */
    private $posts;

    /**
     * @var SourceDto|null
     */
    private $instagramSource;

    /**
     * @var SourceDto|null
     */
    private $tiktokSource;

    /**
     * FeedDto constructor.
     * @param int $id
     * @param string $name
     * @param array $posts
     * @param SourceDto $instagramSource
     * @param SourceDto $tiktokSource
     */
    public function __construct($id, $name, array $posts = [], SourceDto $instagramSource = null, SourceDto $tiktokSource = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->posts = $posts;
        $this->instagramSource = $instagramSource;
        $this->tiktokSource = $tiktokSource;
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }


    /**
     * @return array
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * @param array $posts
     */
    public function setPosts(array $posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return null|SourceDto
     */
    public function getInstagramSource()
    {
        return $this->instagramSource;
    }

    /**
     * @param null|SourceDto $instagramSource
     */
    public function setInstagramSource($instagramSource)
    {
        $this->instagramSource = $instagramSource;
    }

    /**
     * @return null|SourceDto
     */
    public function getTiktokSource()
    {
        return $this->tiktokSource;
    }

    /**
     * @param null|SourceDto $tiktokSource
     */
    public function setTiktokSource($tiktokSource)
    {
        $this->tiktokSource = $tiktokSource;
    }

}