<?php

declare(strict_types=1);

namespace Api\Application\Command\User\UserWeb;

use App\Domain\Dto\User\DetalleUser;
use App\Domain\Exception\Model\User\UserNotFound;
use App\Domain\Exception\Model\User\UserWeb\UserWebAlreadyExists;
use App\Domain\Exception\ValueObject\DateIsNotValid;
use App\Domain\Exception\ValueObject\EmailAddressIsNotValid;
use App\Domain\Exception\ValueObject\NameIsNotValid;
use App\Domain\Exception\ValueObject\Security\PasswordsDoNotMatch;
use App\Domain\Model\User\UserWeb;
use App\Domain\Repository\Doctrine\User\UserReadRepository;
use App\Domain\Repository\Doctrine\User\UserWeb\UserWebWriteRepository;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\EmailAddress;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Security\PasswordHash;

use function assert;
use function strcmp;

class RegistrarUsuarioCommandHandler
{
    public function __construct(
        private readonly UserReadRepository $userReadRepository,
        private readonly UserWebWriteRepository $userWebWriteRepository,
    ) {
    }

    /**
     * @throws DateIsNotValid
     * @throws EmailAddressIsNotValid
     * @throws NameIsNotValid
     * @throws PasswordsDoNotMatch
     * @throws UserWebAlreadyExists
     */
    public function __invoke(RegistrarUsuarioCommand $command): DetalleUser
    {
        if (strcmp($command->password, $command->passwordRepeat) !== 0) {
            throw PasswordsDoNotMatch::withRepeatDifferentValues();
        }

        $this->handleDuplicatedEmailErrors($command);

        $emailAddress = EmailAddress::fromString($command->email);

        $userWeb = UserWeb::crearFromApi(
            $this->userWebWriteRepository->nextIdentity(),
            Name::fromString($command->nombre),
            $emailAddress,
            PasswordHash::fromString($command->password)
        );

        assert($userWeb instanceof UserWeb);

        $this->userWebWriteRepository->save($userWeb);

        return DetalleUser::fromModel($userWeb);
    }

    /**
     * @throws EmailAddressIsNotValid
     * @throws UserWebAlreadyExists
     */
    private function handleDuplicatedEmailErrors(RegistrarUsuarioCommand $command): void
    {
        $email = EmailAddress::fromString($command->email);

        try {
            $this->userReadRepository->ofEmailOrFail(
                EmailAddress::fromString($email->asString())
            );

            throw UserWebAlreadyExists::withEmailAddress($email);
        } catch (UserNotFound $exception) {
        }
    }
}
