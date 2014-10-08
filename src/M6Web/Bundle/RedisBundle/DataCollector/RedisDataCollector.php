<?php
namespace M6Web\Bundle\RedisBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handle datacollector for redis
 */
class RedisDataCollector extends DataCollector
{
    private $commands;

    /**
     * Construct the data collector
     */
    public function __construct()
    {
        $this->data['redis'] = array();
    }

    /**
     * Collect the data
     * @param Request    $request   The request object
     * @param Response   $response  The response object
     * @param \Exception $exception An exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * Listen for redis command event
     * @param object $event The event object
     */
    public function onRedisCommand($event)
    {
        $this->data['redis'][] = array(
            'command'   => $event->getCommand(),
            'arguments' => $event->getArguments(),
            'executiontime' => $event->getExecutionTime()
        );
    }

    /**
     * Return command list and number of times they were called
     * @return array The command list and number of times called
     */
    public function getCommands()
    {
        return $this->data['redis'];
    }

    /**
     * Return the name of the collector
     * @return string data collector name
     */
    public function getName()
    {
        return 'redis';
    }

    /**
     * temps total d'exec des commandes
     * @return float
     */
    public function getTotalExecutionTime()
    {
        $ret = 0;
        foreach ($this->data['redis'] as $command) {
            $ret += $command['executiontime'];
        }

        return $ret;
    }

    /**
     * temps moyen d'exec
     * @return float
     */
    public function getAvgExecutionTime()
    {
        return ($this->getTotalExecutionTime()) ? ($this->getTotalExecutionTime() / count($this->data['redis']) ) : 0;
    }
}
