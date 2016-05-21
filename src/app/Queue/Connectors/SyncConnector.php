<?php

namespace App\Queue\Connectors;

use App\Queue\SyncQueue;

class SyncConnector implements Connector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new SyncQueue;
    }
}
