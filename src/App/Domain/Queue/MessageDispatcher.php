<?php

declare(strict_types=1);

namespace App\Domain\Queue;

interface MessageDispatcher
{
    public function dispatch(object $message): void;
}
