<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;

class AuthService implements IAuthService
{
	private const MIN_JWT_SECRET_LENGTH = 32;

	private IUserRepository $userRepository;

	public function __construct(?IUserRepository $userRepository = null)
	{
		$this->userRepository = $userRepository ?? new UserRepository();
	}

	public function register(array $data): array
	{
		$name = trim($data['name'] ?? '');
		$email = trim($data['email'] ?? '');
		$password = $data['password'] ?? '';
		$role = 'student';

		if ($name === '' || $email === '' || $password === '') {
			throw new \InvalidArgumentException('Missing required fields');
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new \InvalidArgumentException('Invalid email');
		}

		if (strlen($password) < 8) {
			throw new \InvalidArgumentException('Password must be at least 8 characters');
		}

		if ($this->userRepository->findByEmail($email)) {
			throw new \RuntimeException('Email already registered');
		}

		$user = new User();
		$user->name = $name;
		$user->email = $email;
		$user->passwordHash = password_hash($password, PASSWORD_BCRYPT);
		$user->role = $role;

		$user = $this->userRepository->create($user);

		return [
			'user' => $user->toArray(),
			'token' => $this->generateToken($user),
		];
	}

	public function login(array $data): array
	{
		$email = trim($data['email'] ?? '');
		$password = $data['password'] ?? '';

		if ($email === '' || $password === '') {
			throw new \InvalidArgumentException('Missing required fields');
		}

		$user = $this->userRepository->findByEmail($email);
		if (!$user) {
			throw new \RuntimeException('Invalid credentials');
		}

		if (!password_verify($password, $user->passwordHash)) {
			throw new \RuntimeException('Invalid credentials');
		}

		return [
			'user' => $user->toArray(),
			'token' => $this->generateToken($user),
		];
	}

	private function generateToken(User $user): string
	{
		$now = time();
		$expiryHours = max(1, (int)($_ENV['JWT_EXPIRY_HOURS'] ?? 2));
		$payload = [
			'sub' => $user->id,
			'name' => $user->name,
			'email' => $user->email,
			'role' => $user->role,
			'iat' => $now,
			'exp' => $now + ($expiryHours * 3600),
		];

		$secret = (string)($_ENV['JWT_SECRET'] ?? '');
		if (strlen($secret) < self::MIN_JWT_SECRET_LENGTH) {
			throw new \RuntimeException('JWT_SECRET is missing or too weak (minimum 32 characters).');
		}

		return JWT::encode($payload, $secret, 'HS256');
	}
}
