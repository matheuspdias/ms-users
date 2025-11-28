<?php

namespace Domain\User\Entity;

use Domain\User\ValueObject\Email;
use Domain\User\ValueObject\Password;
use Domain\User\ValueObject\UserId;
use Domain\User\ValueObject\UserName;
use DateTimeImmutable;

class User
{
    private UserId $id;
    private UserName $name;
    private Email $email;
    private Password $password;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        UserName $name,
        Email $email,
        Password $password,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        UserName $name,
        Email $email,
        Password $password
    ): self {
        return new self(
            UserId::generate(),
            $name,
            $email,
            $password,
            new DateTimeImmutable(),
            null
        );
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): UserName
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateName(UserName $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name->value(),
            'email' => $this->email->value(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
