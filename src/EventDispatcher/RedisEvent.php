<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Redis event
 */
class RedisEvent extends Event
{
    private $executionTime = 0;
    private $command;
    private $arguments;

    private $clientName;

    public function setClientName(string $v): self
    {
        $this->clientName = $v;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setArguments(array $v)
    {
        $this->arguments = $v;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function setExecutionTime(float $v)
    {
        $this->executionTime = $v;
    }

    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     * alias of getExecutionTime for statsd Bundle
     *
     * @return float millisecondes
     */
    public function getTiming(): float
    {
        return $this->getExecutionTime() * 1000;
    }
}
