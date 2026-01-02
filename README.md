# MS-Users - Microserviço de Usuários (Consumer)

Microserviço consumer que processa eventos de criação de usuários do RabbitMQ usando Laravel 11 com arquitetura Clean Code + DDD.

## Arquitetura

O projeto segue os princípios de Clean Architecture e Domain-Driven Design (DDD):

```
src/
├── Domain/                          # Camada de Domínio
│   └── User/
│       ├── Entity/                  # Entidades
│       │   └── User.php
│       ├── ValueObject/             # Objetos de Valor
│       │   ├── UserId.php
│       │   ├── UserName.php
│       │   ├── Email.php
│       │   └── Password.php
│       └── Repository/              # Interfaces de Repositório
│           └── UserRepositoryInterface.php
│
├── Application/                     # Camada de Aplicação (Casos de Uso)
│   └── UseCase/
│       └── CreateUser/
│           ├── CreateUserUseCase.php
│           ├── CreateUserInput.php
│           └── CreateUserOutput.php
│
├── Infrastructure/                  # Camada de Infraestrutura
│   ├── Persistence/
│   │   └── Eloquent/
│   │       ├── Models/
│   │       │   └── UserModel.php
│   │       └── Repository/
│   │           └── EloquentUserRepository.php
│   └── Messaging/
│       └── RabbitMQ/
│           └── RabbitMQConsumer.php
│
└── app/                            # Camada de Apresentação (Laravel)
    └── Console/
        └── Commands/
            └── ConsumeUserCreatedCommand.php
```

## Tecnologias

- PHP 8.4
- Laravel 11
- MySQL 8.0
- RabbitMQ (compartilhado com ms-producer)
- Docker & Docker Compose

## Pré-requisitos

- Docker e Docker Compose instalados
- RabbitMQ rodando em `localhost:5672` (porta 15672 para management)
  - Usuário: `rabbit`
  - Senha: `rabbit`

## Instalação

1. Clone o repositório e entre na pasta:
```bash
git clone https://github.com/matheuspdias/ms-users.git
cd ms-users
```

2. Suba os containers:
```bash
docker-compose up -d
```

3. Copie o arquivo de ambiente:
```bash
cp src/.env.example src/.env
```

4. Instale as dependências do Composer:
```bash
docker exec ms-users-app composer install
```

5. Gere a chave da aplicação:
```bash
docker exec ms-users-app php artisan key:generate
```

6. Execute as migrations:
```bash
docker exec ms-users-app php artisan migrate
```

**Nota**: A fila `user_events` será criada automaticamente quando o consumer rodar pela primeira vez.

## Configuração

As configurações do RabbitMQ estão no arquivo [.env](src/.env):

```env
RABBITMQ_HOST=host.docker.internal
RABBITMQ_PORT=5672
RABBITMQ_USER=rabbit
RABBITMQ_PASSWORD=rabbit
RABBITMQ_QUEUE=user_events
```

**Importante**: O `RABBITMQ_HOST=host.docker.internal` permite que o container acesse o RabbitMQ rodando no host.

## Uso

### Consumer Automático

O consumer do RabbitMQ **inicia automaticamente** quando o container sobe, graças ao Supervisor. Não é necessário executar comandos manuais!

O Supervisor garante que:
- O consumer inicia assim que o container é criado
- Reinicia automaticamente em caso de falha
- Continua rodando em background

Para verificar se está funcionando, veja os logs:

```bash
docker logs -f ms-users-app
```

Ou veja o log específico do consumer:

```bash
docker exec ms-users-app tail -f storage/logs/consumer.log
```

### Gerenciar o Consumer (Opcional)

Se precisar gerenciar manualmente o consumer via Supervisor:

```bash
# Ver status
docker exec ms-users-app supervisorctl status

# Parar o consumer
docker exec ms-users-app supervisorctl stop laravel-consumer:*

# Iniciar o consumer
docker exec ms-users-app supervisorctl start laravel-consumer:*

# Reiniciar o consumer
docker exec ms-users-app supervisorctl restart laravel-consumer:*
```

### Formato do evento esperado

O producer deve enviar eventos no seguinte formato:

```json
{
  "event_id": "user_unique_id",
  "event_type": "user.created",
  "timestamp": "2025-01-28T10:00:00+00:00",
  "payload": {
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123"
  },
  "metadata": {
    "source": "ms-producer",
    "version": "1.0",
    "environment": "local"
  }
}
```

## Camadas da Arquitetura

### Domain (Domínio)
- **Entities**: Objetos com identidade única ([User.php](src/Domain/User/Entity/User.php))
- **Value Objects**: Objetos imutáveis sem identidade (Email, Password, UserName, UserId)
- **Repository Interfaces**: Contratos para persistência de dados

### Application (Aplicação)
- **Use Cases**: Lógica de negócio ([CreateUserUseCase.php](src/Application/UseCase/CreateUser/CreateUserUseCase.php))
- **DTOs**: Input e Output para comunicação com casos de uso

### Infrastructure (Infraestrutura)
- **Persistence**: Implementação dos repositórios usando Eloquent
- **Messaging**: Implementação do consumer RabbitMQ

### Presentation (Apresentação)
- **Commands**: Comandos Artisan para executar os consumers

## Comandos Úteis

### Acessar o container
```bash
docker exec -it ms-users-app bash
```

### Logs do container
```bash
docker logs -f ms-users-app
```

### Executar migrations
```bash
docker exec -it ms-users-app php artisan migrate
```

### Limpar cache
```bash
docker exec -it ms-users-app php artisan cache:clear
docker exec -it ms-users-app php artisan config:clear
```

### Ver logs do consumer
```bash
# Logs do Supervisor
docker exec -it ms-users-app supervisorctl tail -f laravel-consumer

# Logs do Laravel
docker exec -it ms-users-app tail -f storage/logs/consumer.log
```

### Parar os containers
```bash
docker-compose down
```

## Fluxo de Processamento

1. Producer envia evento `user.created` para fila RabbitMQ
2. Consumer recebe mensagem da fila
3. Comando Laravel valida o tipo de evento
4. CreateUserUseCase é executado
5. Validações de domínio são aplicadas (email, nome, senha)
6. Verifica se usuário já existe
7. Cria entidade User
8. Salva no banco de dados via Repository
9. ACK é enviado para RabbitMQ
10. Em caso de erro, NACK é enviado e mensagem retorna para fila

## Princípios Aplicados

- **Clean Architecture**: Separação de responsabilidades em camadas
- **DDD**: Modelagem rica de domínio com Entities e Value Objects
- **SOLID**: Princípios de design orientado a objetos
- **Dependency Injection**: Inversão de controle via Service Container do Laravel
- **Repository Pattern**: Abstração da camada de dados

## Portas

- **8081**: Nginx (API REST - opcional)
- **3306**: MySQL
- **5672**: RabbitMQ (compartilhado)
- **15672**: RabbitMQ Management (compartilhado)
