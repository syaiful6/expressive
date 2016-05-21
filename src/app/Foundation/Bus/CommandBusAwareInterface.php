<?php

namespace App\Foundation\Bus;

use League\Tactician\CommandBus;

interface CommandBusAwareInterface
{
    /**
     *
     */
    public function setCommandBus(CommandBus $commandBus);

    /**
     *
     */
    public function getCommandBus();
}
