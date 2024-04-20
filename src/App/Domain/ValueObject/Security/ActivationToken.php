<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Security;

use App\Domain\ValueObject\Security\Token\ClearTextToken;
use App\Domain\ValueObject\Security\Token\TokenHash;

final class ActivationToken
{
    private function __construct(
        private ClearTextToken $selector,
        private TokenHash $verifier
    ) {
    }

    /**
     * @return ActivationToken
     */
    public static function fromSplittedToken(Token\SplittedToken $splittedToken): self
    {
        return new self(
            $splittedToken->selector(),
            $splittedToken->verifier()
        );
    }

    public function selector(): ClearTextToken
    {
        return $this->selector;
    }

    public function verifier(): TokenHash
    {
        return $this->verifier;
    }
}
