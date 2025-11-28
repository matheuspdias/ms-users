<?php

namespace Application\UseCase\CreateUser;

use Domain\User\Entity\User;
use Domain\User\Repository\UserRepositoryInterface;
use Domain\User\ValueObject\Email;
use Domain\User\ValueObject\Password;
use Domain\User\ValueObject\UserName;
use Psr\Log\LoggerInterface;

class CreateUserUseCase
{
    private UserRepositoryInterface $userRepository;
    private LoggerInterface $logger;

    public function __construct(
        UserRepositoryInterface $userRepository,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function execute(CreateUserInput $input): CreateUserOutput
    {
        try {
            $email = Email::fromString($input->email);

            if ($this->userRepository->existsByEmail($email)) {
                throw new \DomainException("User with email {$input->email} already exists");
            }

            $user = User::create(
                UserName::fromString($input->name),
                $email,
                Password::fromPlainText($input->password)
            );

            $this->userRepository->save($user);

            $this->logger->info('User created successfully', [
                'user_id' => $user->getId()->value(),
                'email' => $user->getEmail()->value()
            ]);

            return new CreateUserOutput(
                $user->getId()->value(),
                $user->getName()->value(),
                $user->getEmail()->value(),
                $user->getCreatedAt()
            );
        } catch (\DomainException $e) {
            $this->logger->warning('User creation failed - domain error', [
                'email' => $input->email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('User creation failed', [
                'email' => $input->email,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Failed to create user: ' . $e->getMessage());
        }
    }
}
