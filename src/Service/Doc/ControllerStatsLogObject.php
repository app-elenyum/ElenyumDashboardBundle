<?php

namespace Elenyum\Dashboard\Service\Doc;

use DateTime;

class ControllerStatsLogObject
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    public const TYPE_REQUEST = 'request';
    public const TYPE_EXCEPTION = 'exception';

    private DateTime $timestamp;
    private string $endpoint;
    private string $controller;
    private int $duration;
    private string $status;
    private string $type;
    private ?string $errorMessage = null;
    private ?int $errorCode = null;

    /**
     * @param DateTime $timestamp
     * @param string $endpoint
     * @param string $controller
     * @param int $duration
     * @param string $status
     * @param string $type
     * @param string|null $errorMessage
     * @param int|null $errorCode
     */
    public function __construct(
        DateTime $timestamp,
        string $endpoint,
        string $controller,
        int $duration,
        string $status,
        string $type,
        string $errorMessage = null,
        int $errorCode = null
    ) {
        $this->timestamp = $timestamp;
        $this->endpoint = $endpoint;
        $this->controller = $controller;
        $this->duration = $duration;
        $this->status = $status;
        $this->type = $type;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }
}