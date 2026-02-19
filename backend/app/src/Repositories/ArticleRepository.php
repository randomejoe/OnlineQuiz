<?php

// NOTE: we are using a simplified file based storage for demo purposes
// For your assignment, you should use a database

namespace App\Repositories;

use App\Models\Article;
use App\Utils\JsonStore;

class ArticleRepository implements IArticleRepository
{
    private JsonStore $store;
    private const DATA_FILE = __DIR__ . '/../data/articles.json';

    public function __construct()
    {
        $this->store = new JsonStore(self::DATA_FILE, Article::class);
    }

    /**
     * @return Article[]
     */
    public function getAll(): array
    {
        return $this->store->getAll();
    }

    public function getById(int $id): ?Article
    {
        return $this->store->getById($id);
    }

    public function create(Article $article): Article
    {
        $this->store->create($article);
        return $article;
    }

    public function update(Article $article): bool
    {
        return $this->store->update($article);
    }

    public function delete(int $id): bool
    {
        return $this->store->delete($id);
    }
}