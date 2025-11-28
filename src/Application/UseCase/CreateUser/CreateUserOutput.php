<?php

namespace Application\UseCase\CreateUser;

use DateTimeImmutable;

class CreateUserOutput
{
    public string $id;
    public string $name;
    public string $email;
    public DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $email,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
