<?php

namespace App\Utils;

use PDO;

class Database
{
	private static ?PDO $connection = null;

	public static function getConnection(): PDO
	{
		if (self::$connection instanceof PDO) {
			return self::$connection;
		}

		$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
		$port = $_ENV['DB_PORT'] ?? '3306';
		$name = $_ENV['DB_NAME'] ?? 'quiz';
		$user = $_ENV['DB_USER'] ?? 'root';
		$pass = $_ENV['DB_PASS'] ?? 'secret123';

		$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];

		$pdo = new PDO($dsn, $user, $pass, $options);
		self::$connection = $pdo;

		return self::$connection;
	}
}
