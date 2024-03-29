<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\DataCollector;

use M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Handle datacollector for redis
 */
class RedisDataCollector extends DataCollector
{
    /**
     * Construct the data collector
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Collect the data
     *
     * @param Request         $request   The request object
     * @param Response        $response  The response object
     * @param \Throwable|null $exception An exception
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
    }

    public function reset()
    {
        $this->data = [
            'redis' => new \SplQueue(),
        ];
    }

    /**
     * Listen for redis command event
     *
     * @param RedisEvent $event The event object
     */
    public function onRedisCommand(RedisEvent $event)
    {
        $this->data['redis'][] = [
            'event' => $event->getClientName(),
            'command' => $event->getCommand(),
            'arguments' => $event->getArguments(),
            'executiontime' => $event->getExecutionTime(),
        ];
    }

    /**
     * Return command list and number of times they were called
     *
     * @return array The command list and number of times called
     */
    public function getCommands(): \SplQueue
    {
        return $this->data['redis'];
    }

    public function getName(): string
    {
        return 'redis';
    }

    public function getTotalExecutionTime(): float
    {
        $result = array_reduce(iterator_to_array($this->getCommands()), function ($time, $value) {
            $time += $value['executiontime'];

            return $time;
        });

        return $result ?? 0;
    }

    public function getAvgExecutionTime(): float
    {
        $totalExecutionTime = $this->getTotalExecutionTime();

        return ($totalExecutionTime) ? ($totalExecutionTime / count($this->getCommands())) : 0;
    }
}
