<?php

namespace App\Controllers;

use App\Models\Article;
use App\Services\IArticleService;
use App\Services\ArticleService;
use App\Framework\Controller;

class ArticleController extends Controller
{
    private IArticleService $articleService;

    public function __construct()
    {
        $this->articleService = new ArticleService();
    }

    public function getAll()
    {
        try {
            $articles = $this->articleService->getAll();
            return $this->sendSuccessResponse($articles);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function get($vars = [])
    {
        try {
            $id = (int)($vars['id'] ?? 0);
            $article = $this->articleService->getById($id);
            
            if (!$article) {
                return $this->sendErrorResponse('Article not found', 404);
            }
            return $this->sendSuccessResponse($article);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function create()
    {
        try {
            $article = $this->mapPostDataToClass(Article::class);
            $article = $this->articleService->create($article);
            return $this->sendSuccessResponse($article, 201);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function update($vars = [])
    {
        try {
            $article = $this->mapPostDataToClass(Article::class);
            $id = (int)($vars['id'] ?? 0);
            $article->id = $id;
            $this->articleService->update($article);
            return $this->sendSuccessResponse($article);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function delete($vars = [])
    {
        try {
            $id = (int)($vars['id'] ?? 0);
            $this->articleService->delete($id);
            return $this->sendSuccessResponse();
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Internal server error', 500);
        }
    }


}