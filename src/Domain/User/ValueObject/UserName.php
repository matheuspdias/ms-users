<?php

namespace Domain\User\ValueObject;

class UserName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('User name cannot be empty');
        }

        if (strlen($value) < 2) {
            throw new \InvalidArgumentException('User name must be at least 2 characters');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('User name cannot exceed 255 characters');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return $this->value === $other->value;
    }
}
