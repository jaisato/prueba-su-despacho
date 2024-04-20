<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Security;

use App\Domain\ValueObject\DateTime;
use App\Domain\ValueObject\Security\Token\ClearTextToken;
use App\Domain\ValueObject\Security\Token\TokenHash;

final class RecoveryToken
{
    private const TOKEN_LIFE_SPAN = '+1 day';

    private DateTime $validUntil;

    private function __construct(
        private ClearTextToken $selector,
        private TokenHash $verifier
    ) {
        $this->validUntil = DateTime::createIn(self::TOKEN_LIFE_SPAN);
    }

    /**
     * @return RecoveryToken
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

    public function validUntil(): DateTime
    {
        return $this->validUntil;
    }
}
