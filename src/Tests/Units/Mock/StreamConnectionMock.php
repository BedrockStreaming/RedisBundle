<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Tests\Units\Mock;

use M6Web\Component\RedisMock\RedisMock;
use M6Web\Component\RedisMock\UnsupportedException;
use Predis\Command\CommandInterface;
use Predis\Connection\ParametersInterface;

class StreamConnectionMock extends \Predis\Connection\StreamConnection
{
    /** @var RedisMock */
    protected $redisMock;

    /**
     * @param ParametersInterface $parameters initialization parameters for the connection
     */
    public function __construct(ParametersInterface $parameters)
    {
        $this->redisMock = new RedisMock();
        parent::__construct($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(CommandInterface $command)
    {
        $methodName = strtolower($command->getId());

        if (!method_exists(RedisMock::class, $methodName)) {
            throw new UnsupportedException(sprintf('Redis command `%s` is not supported by RedisMock.', $methodName));
        }

        return call_user_func_array([$this->redisMock, $methodName], $command->getArguments());
    }
}
