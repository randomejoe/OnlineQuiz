<?php

namespace App\Repositories;

abstract class BaseRepository
{
	protected \PDO $db;

	public function __construct()
	{
		$this->db = \App\Utils\Database::getConnection();
	}
}
