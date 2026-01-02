<?php

namespace Application\UseCase\ListUsers;

class ListUsersOutput
{
    /**
     * @param UserDto[] $users
     */
    public function __construct(
        public readonly array $users
    ) {}
}
