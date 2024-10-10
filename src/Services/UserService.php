<?php

namespace App\Services;

use App\Models\User;
use App\Services\AuthService;

class UserService
{
    protected $userModel;
    protected $authService;

    public function __construct(User $userModel, AuthService $authService)
    {
        $this->userModel = $userModel;
        $this->authService = $authService;
    }

    public function current_user()
    {
        return $this->authService->current_user();
    }

    public function getUserById(int $id): ?array
    {
        return $this->userModel->find_by_id($id);
    }

    public function update_user(int $id, array $data): bool
    {
        return $this->userModel->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userModel->delete($id);
    }
}
