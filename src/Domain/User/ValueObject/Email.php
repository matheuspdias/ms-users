<?php

namespace Domain\User\ValueObject;

class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function validate(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('Email cannot exceed 255 characters');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
