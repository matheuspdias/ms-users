<?php

namespace Infrastructure\Persistence\Eloquent\Repository;

use Domain\User\Entity\User;
use Domain\User\Repository\UserRepositoryInterface;
use Domain\User\ValueObject\Email;
use Domain\User\ValueObject\Password;
use Domain\User\ValueObject\UserId;
use Domain\User\ValueObject\UserName;
use Infrastructure\Persistence\Eloquent\Models\UserModel;
use DateTimeImmutable;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->getId()->value()],
            [
                'name' => $user->getName()->value(),
                'email' => $user->getEmail()->value(),
                'password' => $user->getPassword()->value(),
            ]
        );
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    public function findAll(): array
    {
        return UserModel::all()->map(fn($model) => $this->toDomain($model))->toArray();
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            UserId::fromString($model->id),
            UserName::fromString($model->name),
            Email::fromString($model->email),
            Password::fromHash($model->password),
            new DateTimeImmutable($model->created_at->toDateTimeString()),
            $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null
        );
    }
}
