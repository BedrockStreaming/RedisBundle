<?php

namespace M6Web\Bundle\RedisBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Redis event
 */
class RedisEvent extends Event
{
    private $executionTime = 0;
    private $command;
    private $arguments;

    private $clientName;

    /**
     * @param string $v
     *
     * @return $this
     */
    public function setClientName($v)
    {
        $this->clientName = $v;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set the redis command associated with this event
     * @param string $command The redis command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Get the redis command associated with this event
     * @return string the redis command
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * set the arguments
     * @param array $v argus
     */
    public function setArguments($v)
    {
        $this->arguments = $v;
    }

    /**
     * get the arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * set le temps d'exec
     * @param float $v temps
     */
    public function setExecutionTime($v)
    {
        $this->executionTime = $v;
    }

    /**
     * retourne le temps d'exec
     * @return float $v temps
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * alias de getExecutionTime pour le bundle statsd
     * retourne des millisecondes
     *
     * @return float
     */
    public function getTiming()
    {
        return $this->getExecutionTime() * 1000;
    }
}
