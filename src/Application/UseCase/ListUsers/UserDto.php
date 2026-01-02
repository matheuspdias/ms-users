<?php

namespace Application\UseCase\ListUsers;

class UserDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $createdAt,
        public readonly ?string $updatedAt
    ) {}
}
