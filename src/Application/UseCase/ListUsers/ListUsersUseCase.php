<?php

namespace Application\UseCase\ListUsers;

use Domain\User\Repository\UserRepositoryInterface;

class ListUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(): ListUsersOutput
    {
        $users = $this->userRepository->findAll();

        $userDtos = array_map(
            fn($user) => new UserDto(
                id: $user->getId()->value(),
                name: $user->getName()->value(),
                email: $user->getEmail()->value(),
                createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s'),
                updatedAt: $user->getUpdatedAt()?->format('Y-m-d H:i:s')
            ),
            $users
        );

        return new ListUsersOutput($userDtos);
    }
}
