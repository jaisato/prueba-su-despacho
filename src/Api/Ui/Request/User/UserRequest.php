<?php

declare(strict_types=1);

namespace Api\Ui\Request\User;

use Api\Ui\Request\ApiRequest;
use Symfony\Component\Validator\Constraints as Assert;

abstract class UserRequest extends ApiRequest
{
    /** @Assert\NotBlank(message="El nombre es requerido", groups={"enviar"}) */
    public ?string $nombre;

    /** @Assert\NotBlank(message="El email es requerido", groups={"enviar"}) */
    public ?string $email;

    /** @Assert\NotBlank(message="El password es requerido", groups={"enviar"}) */
    public ?string $password;

    /** @Assert\NotBlank(message="El password es requerido", groups={"enviar"}) */
    public ?string $passwordRepeat;

    final public function __construct(
        ?string $nombre = null,
        ?string $email = null,
        ?string $password = null,
        ?string $passwordRepeat = null
    ) {
        $this->nombre                  = $nombre;
        $this->email                   = $email;
        $this->password                = $password;
        $this->passwordRepeat          = $passwordRepeat;
    }
}
