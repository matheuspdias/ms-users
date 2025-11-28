<?php

namespace App\Console\Commands;

use Application\UseCase\CreateUser\CreateUserInput;
use Application\UseCase\CreateUser\CreateUserUseCase;
use Illuminate\Console\Command;
use Infrastructure\Messaging\RabbitMQ\RabbitMQConsumer;
use Psr\Log\LoggerInterface;

class ConsumeUserCreatedCommand extends Command
{
    protected $signature = 'rabbitmq:consume-user-created';

    protected $description = 'Consume user.created events from RabbitMQ';

    private CreateUserUseCase $createUserUseCase;
    private LoggerInterface $logger;

    public function __construct(
        CreateUserUseCase $createUserUseCase,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->createUserUseCase = $createUserUseCase;
        $this->logger = $logger;
    }

    public function handle(): int
    {
        $this->info('Starting RabbitMQ consumer for user.created events...');

        try {
            $consumer = new RabbitMQConsumer(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password'),
                config('rabbitmq.queue'),
                $this->logger
            );

            $consumer->consume(function (array $message) {
                $this->processMessage($message);
            });

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error in consumer: ' . $e->getMessage());
            $this->logger->error('Consumer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    private function processMessage(array $message): void
    {
        try {
            $this->info("Processing event: {$message['event_id']}");

            if (!isset($message['event_type']) || $message['event_type'] !== 'user.created') {
                $this->warn("Skipping non user.created event: {$message['event_type']}");
                return;
            }

            $payload = $message['payload'] ?? [];

            if (!isset($payload['name'], $payload['email'])) {
                throw new \InvalidArgumentException('Missing required fields: name or email');
            }

            $password = $payload['password'] ?? 'default123';

            $input = new CreateUserInput(
                $payload['name'],
                $payload['email'],
                $password
            );

            $output = $this->createUserUseCase->execute($input);

            $this->info("User created successfully: {$output->email}");
        } catch (\DomainException $e) {
            $this->warn("Domain error: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->error("Error processing message: {$e->getMessage()}");
            throw $e;
        }
    }
}
