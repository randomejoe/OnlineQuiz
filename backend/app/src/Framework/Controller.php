<?php

namespace App\Framework;

class Controller
{
	protected function sendSuccessResponse($data = [], $code = 200): void
	{
		$this->respondJson($data, $code);
	}

	protected function sendErrorResponse($message, $code = 500): void
	{
		$this->respondJson(['error' => $message], $code);
	}

	protected function sendExceptionResponse(\Throwable $e, array $map = [], string $defaultMessage = 'Internal server error', int $defaultCode = 500): void
	{
		$message = $e->getMessage();
		$code = $defaultCode;

		if (isset($map[$message])) {
			$mapped = $map[$message];
			$message = $mapped['message'] ?? $message;
			$code = $mapped['code'] ?? $code;
		} elseif (isset($map[get_class($e)])) {
			$mapped = $map[get_class($e)];
			$message = $mapped['message'] ?? $defaultMessage;
			$code = $mapped['code'] ?? $code;
		} else {
			$message = $defaultMessage;
		}

		$this->sendErrorResponse($message, $code);
	}

	protected function request(): Request
	{
		return RequestContext::get();
	}

	private function respondJson(array $payload, int $code): void
	{
		header('Content-Type: application/json; charset=utf-8');
		http_response_code($code);
		echo json_encode($payload, JSON_PRETTY_PRINT);
	}
}
