<?php
namespace Edaha\Entities;

use Doctrine\Common\Collections\Collection;

interface BoardInterface
{
    public function __construct(string $name, string $directory);
    public function addPost(Post $post): void;
    public function getAllThreads(): Collection; 
    public function getPaginatedThreads(int $page, int $perPage): Collection;
}