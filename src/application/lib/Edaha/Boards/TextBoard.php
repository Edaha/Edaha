<?php
namespace Edaha\Boards;

use Edaha\Entities\Board;

class TextBoard implements BoardInterface
{
    public Board $board;
    public string $type = 'text';

    public function __construct(string $name, string $directory)
    {
        $this->board = new Board($name, $directory);
        $this->board->setOption('type', $this->type);

        // Default settings
        $this->board->setOption('posts_per_page', 10);
        $this->board->setOption('anonymous', 'Anonymous');
        $this->board->setOption('maximum_message_length', 10000);
    }

    public function addPost(Post $post): void
    {
        $this->board->addPost($post);
    }

    public function getAllThreads(): Collection
    {
        return $this->board->getAllThreads();
    }

    public function getPaginatedThreads(int $page, ?int $perPage): Collection
    {
        if ($perPage === null) {
            $perPage = (int)($this->board->getOption('posts_per_page') ?? 10);
        }
        return $this->board->getPaginatedThreads($page, $perPage);
    }
}