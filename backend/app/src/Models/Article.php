<?php

namespace App\Models;

class Article
{
    public ?int $id;
    public string $title;
    public string $author;
    public string $category;
    public string $published;
    public string $content;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->author = $data['author'] ?? '';
        $this->category = $data['category'] ?? '';
        $this->published = $data['published'] ?? '';
        $this->content = $data['content'] ?? '';
    }
}
