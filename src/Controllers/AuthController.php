<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function register()
    {
        $data = $this->getInput();
        
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
            return $this->jsonResponse(['error' => 'Missing required fields'], 400);
        }

        $user = new User();
        if ($user->findByEmail($data['email'])) {
            return $this->jsonResponse(['error' => 'User already exists'], 409);
        }

        if ($user->create($data['name'], $data['email'], $data['password'])) {
            return $this->jsonResponse(['message' => 'User registered successfully'], 201);
        }

        return $this->jsonResponse(['error' => 'Registration failed'], 500);
    }

    public function login()
    {
        $data = $this->getInput();

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->jsonResponse(['error' => 'Missing credentials'], 400);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['password'])) {
            $payload = [
                'iss' => 'http://localhost',
                'aud' => 'http://localhost',
                'iat' => time(),
                'exp' => time() + 3600, // 1 hour
                'sub' => $user['id'],
                'email' => $user['email']
            ];

            $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

            return $this->jsonResponse([
                'message' => 'Login successful',
                'token' => $jwt
            ]);
        }

        return $this->jsonResponse(['error' => 'Invalid credentials'], 401);
    }
}
