<?php
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $this->assertSame($board, $post->board);
        $this->assertSame('subject', $post->subject);
        $this->assertSame('message', $post->message);
        $this->assertInstanceOf(DateTime::class, $post->created_at);
    }

    public function testCanReplyToPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);

        $this->assertCount(1, $post1->replies);
        $this->assertSame($post2, $post1->replies[0]);
        $this->assertSame($post1, $post2->parent);
        $this->assertTrue($post1->is_thread);
        $this->assertFalse($post1->is_reply);
        $this->assertFalse($post2->is_thread);
        $this->assertTrue($post2->is_reply);
    }

    public function testCanNotReplyToReply(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);
        
        $this->expectException(Exception::class);
        $post3 = new Edaha\Entities\Post($board, 'message3', 'subject3', $post2);
    }

    public function testCanGetAllReplies(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);
        $post3 = new Edaha\Entities\Post($board, 'message3', 'subject3', $post1);

        $this->assertCount(2, $post1->getAllReplies());
        $this->assertSame($post2, $post1->getAllReplies()[0]);
        $this->assertSame($post3, $post1->getAllReplies()[1]);
    }

    public function testCanGetFirstNReplies(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);
        $post3 = new Edaha\Entities\Post($board, 'message3', 'subject3', $post1);
        $post4 = new Edaha\Entities\Post($board, 'message4', 'subject4', $post1);

        $this->assertCount(2, $post1->getFirstNReplies(2));
        $this->assertSame($post2, $post1->getFirstNReplies(2)[0]);
        $this->assertSame($post3, $post1->getFirstNReplies(2)[1]);
    }

    public function testCanGetLastNReplies(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);
        $post3 = new Edaha\Entities\Post($board, 'message3', 'subject3', $post1);
        $post4 = new Edaha\Entities\Post($board, 'message4', 'subject4', $post1);

        $this->assertCount(2, $post1->getLastNReplies(2));
        $this->assertSame($post3, $post1->getLastNReplies(2)[0]);
        $this->assertSame($post4, $post1->getLastNReplies(2)[1]);
    }

    public function testCanStickyPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $this->assertFalse($post->is_stickied);
        $post->sticky();
        $this->assertTrue($post->is_stickied);
    }

    public function testCanUnstickyPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $post->sticky();
        $this->assertTrue($post->is_stickied);
        $post->unsticky();
        $this->assertFalse($post->is_stickied);
    }

    public function testCanLockPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $this->assertFalse($post->is_locked);
        $post->lock();
        $this->assertTrue($post->is_locked);
    }

    public function testCanUnlockPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post = new Edaha\Entities\Post($board, 'message', 'subject');

        $post->lock();
        $this->assertTrue($post->is_locked);
        $post->unlock();
        $this->assertFalse($post->is_locked);
    }

    public function testCanNotReplyToLockedPost(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $post1 = new Edaha\Entities\Post($board, 'message', 'subject');
        $post1->lock();

        $this->expectException(Exception::class);
        $post2 = new Edaha\Entities\Post($board, 'message2', 'subject2', $post1);
    }
}
