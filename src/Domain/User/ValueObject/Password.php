<?php

namespace Domain\User\ValueObject;

class Password
{
    private string $hashedValue;

    private function __construct(string $hashedValue)
    {
        $this->hashedValue = $hashedValue;
    }

    public static function fromPlainText(string $plainPassword): self
    {
        if (strlen($plainPassword) < 6) {
            throw new \InvalidArgumentException('Password must be at least 6 characters');
        }

        $hashedValue = password_hash($plainPassword, PASSWORD_BCRYPT);
        return new self($hashedValue);
    }

    public static function fromHash(string $hashedValue): self
    {
        return new self($hashedValue);
    }

    public function value(): string
    {
        return $this->hashedValue;
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hashedValue);
    }
}
