<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Class Post
 * @package MetricsAPI\Domain
 */
class Post
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var string
     */
    private $fromId;

    /**
     * @var int
     */
    private $length;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Post constructor.
     * @param $id
     * @param $fromId
     * @param $fromName
     * @param $message
     * @param $createdAt
     */
    public function __construct($id, $fromId, $fromName, $message, \DateTime $createdAt)
    {
        $this->id = $id;
        $this->fromId = $fromId;
        $this->fromName = $fromName;
        $this->length = mb_strlen($message);
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getFromId(): string
    {
        return $this->fromId;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() : \DateTime
    {
        return $this->createdAt;
    }
}