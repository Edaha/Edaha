<?php
namespace Edaha\Interfaces;
use Edaha\Entities\Board;
use Edaha\Entities\Post;

interface RegeneratorInterface
{
    public function regenerateAllPages(int $posts_per_page = 10): void;

    public function regeneratePage(int $page): void;

    public function regenerateAllThreads(): void;

    public function regenerateThread(Post $thread): void;

    public function regenerateAll(): void;
}