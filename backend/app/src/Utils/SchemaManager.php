<?php

namespace App\Utils;

use PDO;

final class SchemaManager
{
	private static bool $initialized = false;
	private const SEED_MIGRATION = '002_seed_quiz_data.sql';

	public static function ensure(PDO $db): void
	{
		if (self::$initialized) {
			return;
		}

		self::ensureCoreTables($db);
		self::ensureQuestionsColumns($db);
		self::ensureOptionsColumns($db);
		self::ensureSeedLogTable($db);
		self::applySeedMigration($db);

		self::$initialized = true;
	}

	private static function ensureCoreTables(PDO $db): void
	{
		$requiredTables = ['users', 'quizzes', 'questions', 'options', 'quiz_attempts', 'attempt_answers'];
		foreach ($requiredTables as $table) {
			if (!self::tableExists($db, $table)) {
				self::applyInitialMigration($db);
				return;
			}
		}
	}

	private static function ensureQuestionsColumns(PDO $db): void
	{
		if (!self::columnExists($db, 'questions', 'deleted_at')) {
			$db->exec('ALTER TABLE questions ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER created_at');
		}
	}

	private static function ensureOptionsColumns(PDO $db): void
	{
		if (!self::columnExists($db, 'options', 'match_type')) {
			$db->exec("ALTER TABLE options ADD COLUMN match_type ENUM('exact','regex','levenshtein') NOT NULL DEFAULT 'exact' AFTER is_correct");
		}

		if (!self::columnExists($db, 'options', 'match_threshold')) {
			$db->exec('ALTER TABLE options ADD COLUMN match_threshold DECIMAL(5,2) NULL DEFAULT NULL AFTER match_type');
		}

		if (!self::columnExists($db, 'options', 'deleted_at')) {
			$db->exec('ALTER TABLE options ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER created_at');
		}
	}

	private static function columnExists(PDO $db, string $table, string $column): bool
	{
		$stmt = $db->prepare(
			'SELECT COUNT(*)
			 FROM information_schema.COLUMNS
			 WHERE TABLE_SCHEMA = DATABASE()
			   AND TABLE_NAME = :table_name
			   AND COLUMN_NAME = :column_name'
		);
		$stmt->execute([
			':table_name' => $table,
			':column_name' => $column,
		]);

		return (int)$stmt->fetchColumn() > 0;
	}

	private static function tableExists(PDO $db, string $table): bool
	{
		$stmt = $db->prepare(
			'SELECT COUNT(*)
			 FROM information_schema.TABLES
			 WHERE TABLE_SCHEMA = DATABASE()
			   AND TABLE_NAME = :table_name'
		);
		$stmt->execute([':table_name' => $table]);
		return (int)$stmt->fetchColumn() > 0;
	}

	private static function applyInitialMigration(PDO $db): void
	{
		$migrationPath = dirname(__DIR__, 2) . '/database/migrations/001_create_tables.sql';
		self::applySqlFile($db, $migrationPath, 'Initial database migration');
	}

	private static function ensureSeedLogTable(PDO $db): void
	{
		$db->exec(
			'CREATE TABLE IF NOT EXISTS app_seed_migrations (
				name VARCHAR(255) NOT NULL PRIMARY KEY,
				applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
		);
	}

	private static function applySeedMigration(PDO $db): void
	{
		if (self::seedMigrationApplied($db)) {
			return;
		}

		$migrationPath = dirname(__DIR__, 2) . '/database/migrations/' . self::SEED_MIGRATION;
		self::applySqlFile($db, $migrationPath, 'Seed migration');

		$stmt = $db->prepare('INSERT INTO app_seed_migrations (name) VALUES (:name)');
		$stmt->execute([':name' => self::SEED_MIGRATION]);
	}

	private static function seedMigrationApplied(PDO $db): bool
	{
		$stmt = $db->prepare('SELECT COUNT(*) FROM app_seed_migrations WHERE name = :name');
		$stmt->execute([':name' => self::SEED_MIGRATION]);

		return (int)$stmt->fetchColumn() > 0;
	}

	private static function applySqlFile(PDO $db, string $path, string $label): void
	{
		if (!is_file($path)) {
			throw new \RuntimeException($label . ' not found.');
		}

		$sql = file_get_contents($path);
		if (!is_string($sql) || trim($sql) === '') {
			throw new \RuntimeException($label . ' is empty.');
		}

		$statements = array_filter(array_map('trim', explode(';', $sql)));
		foreach ($statements as $statement) {
			$db->exec($statement);
		}
	}
}
