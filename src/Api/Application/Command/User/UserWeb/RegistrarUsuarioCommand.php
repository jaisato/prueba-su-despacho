<?php

declare(strict_types=1);

namespace Api\Application\Command\User\UserWeb;

class RegistrarUsuarioCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $nombre,
        public readonly string $password,
        public readonly string $passwordRepeat
    ) {
    }
}
