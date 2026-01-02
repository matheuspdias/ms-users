<?php

namespace Application\UseCase\GetUser;

use Domain\User\Repository\UserRepositoryInterface;
use Domain\User\ValueObject\UserId;

class GetUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(GetUserInput $input): ?GetUserOutput
    {
        $userId = UserId::fromString($input->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return null;
        }

        return new GetUserOutput(
            id: $user->getId()->value(),
            name: $user->getName()->value(),
            email: $user->getEmail()->value(),
            createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $user->getUpdatedAt()?->format('Y-m-d H:i:s')
        );
    }
}
