<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService
{
    private UserRepository $userRepository;

    public function __construct(PDO $pdo)
    {
        $this->userRepository = new UserRepository($pdo);
    }

    public function login(string $username, string $password): array
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return Response::error('Username atau password salah.');
        }

        if (!password_verify($password, $user['password'])) {
            return Response::error('Username atau password salah.');
        }

        unset($user['password']);

        return Response::success($user);
    }
}