<?php

namespace App\Foundation\Bus;

use League\Tactician\Middleware;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use League\Tactician\Exception\CanNotInvokeHandlerException;

class QueueCommandHandler implements Middleware
{

    protected $queue;

    /**
     *
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Executes a command and optionally returns a value
     *
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     *
     * @throws CanNotInvokeHandlerException
     */
    public function execute($command, callable $next)
    {
        if (!$this->commandShouldBeQueued($command)) {
            return $next($command);
        }

        $handler = function () use ($command) {
            $this->executeCommand($command);
        };

        $queue = $this->queue;

        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $handler);
        }

        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $handler);
        }

        if (isset($command->delay)) {
            return $queue->later($command->delay, $handler);
        }

        return $queue->push($handler);
    }

    /**
     *
     */
    protected function executeCommand($command)
    {
        if (method_exists($command, 'handle')) {
            return $command->handle();
        } elseif (is_callable($command)) {
            return $command();
        }

        throw new CanNotInvokeHandlerException(sprintf(
            'can\'t invoke command %s',
            get_class($command)
        ));
    }

    /**
     * Determine if the given command should be queued.
     *
     * @param  mixed  $command
     * @return bool
     */
    protected function commandShouldBeQueued($command)
    {
        return $command instanceof ShouldQueue;
    }
}
