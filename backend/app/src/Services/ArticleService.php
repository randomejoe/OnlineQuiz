<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\IArticleRepository;
use App\Repositories\ArticleRepository;

class ArticleService implements IArticleService
{
    private IArticleRepository $repository;

    public function __construct()
    {
        $this->repository = new ArticleRepository();
    }

    /**
     * @return Article[]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): ?Article
    {
        return $this->repository->getById($id);
    }

    public function create(Article $article): Article
    {
        return $this->repository->create($article);
    }

    public function update(Article $article): bool
    {
        return $this->repository->update($article);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
