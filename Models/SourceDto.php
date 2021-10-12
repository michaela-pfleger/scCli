<?php

namespace Models;

class SourceDto
{
    /**
     * @var string $name
     */
    private $name;

    /** @var  int $fanCount */
    private $fanCount;

    /**
     * SourceDto constructor.
     * @param string $name
     * @param int $fanCount
     */
    public function __construct(string $name, int $fanCount)
    {
        $this->name = $name;
        $this->fanCount = $fanCount;
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
     * @return int
     */
    public function getFanCount(): int
    {
        return $this->fanCount;
    }

    /**
     * @param int $fanCount
     */
    public function setFanCount(int $fanCount)
    {
        $this->fanCount = $fanCount;
    }
}