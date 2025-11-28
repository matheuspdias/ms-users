<?php

namespace Infrastructure\Messaging\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RabbitMQConsumer
{
    private AMQPStreamConnection $connection;
    private LoggerInterface $logger;
    private string $queueName;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        string $queueName,
        LoggerInterface $logger
    ) {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->queueName = $queueName;
        $this->logger = $logger;
    }

    public function consume(callable $callback): void
    {
        $channel = $this->connection->channel();

        $channel->queue_declare(
            $this->queueName,
            false,
            true,
            false,
            false
        );

        $this->logger->info("Waiting for messages on queue: {$this->queueName}");

        $channel->basic_qos(null, 1, null);

        $messageCallback = function (AMQPMessage $message) use ($callback) {
            try {
                $this->logger->info('Received message', [
                    'body' => $message->body
                ]);

                $data = json_decode($message->body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON in message');
                }

                $callback($data);

                $message->ack();

                $this->logger->info('Message processed successfully');
            } catch (\Exception $e) {
                $this->logger->error('Error processing message', [
                    'error' => $e->getMessage(),
                    'message' => $message->body
                ]);

                $message->nack(true);
            }
        };

        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            false,
            false,
            false,
            $messageCallback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }

    public function __destruct()
    {
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }
}
