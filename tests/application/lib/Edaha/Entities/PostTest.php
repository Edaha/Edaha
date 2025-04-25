<?php
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    public function testCanBeCreated()
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $this->assertSame($board, $post->board);
        $this->assertSame('subject', $post->subject);
        $this->assertSame('message', $post->message);
        $this->assertInstanceOf(DateTime::class, $post->created_at);
    }
}