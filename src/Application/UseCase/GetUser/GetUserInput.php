<?php

namespace Application\UseCase\GetUser;

class GetUserInput
{
    public function __construct(
        public readonly string $userId
    ) {}
}
