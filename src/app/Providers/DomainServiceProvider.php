<?php

namespace App\Providers;

use Application\UseCase\CreateUser\CreateUserUseCase;
use Domain\User\Repository\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Eloquent\Repository\EloquentUserRepository;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(CreateUserUseCase::class, function ($app) {
            return new CreateUserUseCase(
                $app->make(UserRepositoryInterface::class),
                $app->make('log')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
