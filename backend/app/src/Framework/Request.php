<?php

namespace App\Framework;

class Request
{
	private array $query;
	private array $body;
	private array $server;
	private array $cookies;
	private ?array $user = null;

	public function __construct(array $query, array $body, array $server, array $cookies)
	{
		$this->query = $query;
		$this->body = $body;
		$this->server = $server;
		$this->cookies = $cookies;
	}

	public static function capture(): self
	{
		$rawBody = file_get_contents('php://input');
		$decoded = json_decode($rawBody ?: '', true);
		$body = is_array($decoded) ? $decoded : [];

		return new self($_GET, $body, $_SERVER, $_COOKIE);
	}

	public function body(): array
	{
		return $this->body;
	}

	public function query(string $key, mixed $default = null): mixed
	{
		return $this->query[$key] ?? $default;
	}

	public function pagination(
		string $pageKey = 'page',
		string $perPageKey = 'per_page',
		int $defaultPage = 1,
		int $defaultPerPage = 10,
		int $maxPerPage = 100
	): array {
		$page = max(1, (int)$this->query($pageKey, $defaultPage));
		$perPage = max(1, min($maxPerPage, (int)$this->query($perPageKey, $defaultPerPage)));

		return [
			'page' => $page,
			'per_page' => $perPage,
		];
	}

	public function cookie(string $key, mixed $default = null): mixed
	{
		return $this->cookies[$key] ?? $default;
	}

	public function header(string $name, mixed $default = null): mixed
	{
		$serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
		return $this->server[$serverKey] ?? $default;
	}

	public function isHttps(): bool
	{
		if (($this->server['HTTPS'] ?? '') === 'on') {
			return true;
		}

		if (($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
			return true;
		}

		$port = (int)($this->server['SERVER_PORT'] ?? 0);
		return $port === 443;
	}

	public function user(): ?array
	{
		return $this->user;
	}

	public function setUser(?array $user): void
	{
		$this->user = $user;
	}
}
