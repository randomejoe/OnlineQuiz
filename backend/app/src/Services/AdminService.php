<?php

namespace App\Services;

use App\Repositories\AttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\UserRepository;
use App\Utils\Database;

class AdminService
{
	private UserRepository $userRepository;
	private QuizRepository $quizRepository;
	private AttemptRepository $attemptRepository;

	public function __construct(
		?UserRepository $userRepository = null,
		?QuizRepository $quizRepository = null,
		?AttemptRepository $attemptRepository = null
	) {
		$this->userRepository = $userRepository ?? new UserRepository();
		$this->quizRepository = $quizRepository ?? new QuizRepository();
		$this->attemptRepository = $attemptRepository ?? new AttemptRepository();
	}

	public function getStats(): array
	{
		$summary = $this->fetchSummaryStats();

		return [
			'total_students' => (int)($summary['total_students'] ?? 0),
			'total_quizzes' => (int)($summary['total_quizzes'] ?? 0),
			'total_attempts' => (int)($summary['total_attempts'] ?? 0),
			'average_score_percentage' => round((float)($summary['average_score_percentage'] ?? 0.0), 1),
			'popular_quizzes' => $this->quizRepository->getMostAttempted(5),
		];
	}

	private function fetchSummaryStats(): array
	{
		$db = Database::getConnection();
		$stmt = $db->query(
			"SELECT
				(SELECT COUNT(*) FROM users WHERE role = 'student') AS total_students,
				(SELECT COUNT(*) FROM quizzes) AS total_quizzes,
				(SELECT COUNT(*) FROM quiz_attempts) AS total_attempts,
				COALESCE((SELECT AVG(percentage) FROM quiz_attempts), 0) AS average_score_percentage"
		);

		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		return is_array($row) ? $row : [];
	}

	public function getAllUsers(int $page, int $perPage): array
	{
		return $this->userRepository->getPaginated($page, $perPage);
	}

	public function deleteUser(int $id): void
	{
		$existing = $this->userRepository->findById($id);
		if ($existing === null) {
			throw new \RuntimeException('User not found');
		}

		$this->userRepository->delete($id);
	}

	public function getQuizResults(int $quizId, int $page = 1, int $perPage = 10): array
	{
		$quiz = $this->quizRepository->getById($quizId);
		if ($quiz === null) {
			throw new \RuntimeException('Quiz not found');
		}

		$paginated = $this->attemptRepository->getQuizResultsPaginated($quizId, $page, $perPage);
		$results = $paginated['data'] ?? [];

		return [
			'quiz' => [
				'id' => (int)$quiz['id'],
				'title' => $quiz['title'] ?? '',
			],
			'data' => $results,
			// Backward compatibility alias.
			'results' => $results,
			'meta' => $paginated['meta'] ?? ['total' => count($results), 'page' => $page, 'per_page' => $perPage],
		];
	}

	public function getUserAttempts(int $userId, int $page = 1, int $perPage = 10): array
	{
		$user = $this->userRepository->findById($userId);
		if ($user === null) {
			throw new \RuntimeException('User not found');
		}

		$paginated = $this->attemptRepository->getUserAttemptsForAdminPaginated($userId, $page, $perPage);

		return [
			'user' => [
				'id' => (int)$user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $user->role,
			],
			'data' => $paginated['data'] ?? [],
			// Backward compatibility alias.
			'attempts' => $paginated['data'] ?? [],
			'meta' => $paginated['meta'] ?? ['total' => 0, 'page' => $page, 'per_page' => $perPage],
		];
	}
}
