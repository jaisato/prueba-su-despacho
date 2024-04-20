<?php

namespace App\Domain\CommandBus;

interface CommandBus
{
    public function handle(object $command);
}