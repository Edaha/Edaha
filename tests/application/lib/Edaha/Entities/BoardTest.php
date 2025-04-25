<?php
use PHPUnit\Framework\TestCase;

final class BoardTest extends TestCase
{
    public function testCanBeCreated()
    {
        $board = new Edaha\Entities\Board('name', 'directory');

        $this->assertSame('name', $board->name);
        $this->assertSame('directory', $board->directory);
        $this->assertInstanceOf(DateTime::class, $board->created_at);
    }

    public function testCanCreateAndChangeArbitraryProperty(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');

        $board->arbitrary_property = 'value';
        $this->assertSame('value', $board->arbitrary_property);

        $board->arbitrary_property = 'different';
        $this->assertSame('different', $board->arbitrary_property);
    }

    public function testCanPostToBoard(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2');

        $this->assertCount(2, $board->posts);
        $this->assertSame($post1, $board->posts[0]);
        $this->assertSame($post2, $board->posts[1]);
    }
}
