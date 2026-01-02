<?php

namespace App\Http\Controllers;

use Application\UseCase\GetUser\GetUserInput;
use Application\UseCase\GetUser\GetUserUseCase;
use Application\UseCase\ListUsers\ListUsersUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(
        private ListUsersUseCase $listUsersUseCase,
        private GetUserUseCase $getUserUseCase
    ) {}

    public function index(): JsonResponse
    {
        try {
            $output = $this->listUsersUseCase->execute();

            $users = array_map(
                fn($userDto) => [
                    'id' => $userDto->id,
                    'name' => $userDto->name,
                    'email' => $userDto->email,
                    'created_at' => $userDto->createdAt,
                    'updated_at' => $userDto->updatedAt,
                ],
                $output->users
            );

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => count($users)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error listing users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $input = new GetUserInput($id);
            $output = $this->getUserUseCase->execute($input);

            if (!$output) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $output->id,
                    'name' => $output->name,
                    'email' => $output->email,
                    'created_at' => $output->createdAt,
                    'updated_at' => $output->updatedAt,
                ]
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
