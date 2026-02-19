<?php

namespace App\Repositories;

use App\Models\Article;

interface IArticleRepository
{
    /**
     * @return Article[]
     */
    public function getAll(): array;
    public function getById(int $id): ?Article;
    public function create(Article $article): Article;
    public function update(Article $article): bool;
    public function delete(int $id): bool;
}
