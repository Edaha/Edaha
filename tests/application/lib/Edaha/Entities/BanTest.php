<?php
use PHPUnit\Framework\TestCase;
use DateTime;

final class BanTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming', New DateTime('+1 day'));
        $this->assertSame('192.168.69.1', $ban->ip);
        $this->assertSame('spamming', $ban->reason);
        $this->assertInstanceOf(DateTime::class, $ban->created_at);
        $this->assertInstanceOf(DateTime::class, $ban->expires_at);
        $this->assertFalse($ban->is_expired);
    }

    public function testWillExpire(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming', New DateTime('+2 seconds'));
        $this->assertFalse($ban->is_expired);
        sleep(2);
        $this->assertTrue($ban->is_expired);
    }

    public function testCanExpire(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming', New DateTime('+2 seconds'));
        $this->assertFalse($ban->is_expired);
        $ban->expire();
        $this->assertTrue($ban->is_expired);
    }

    public function testCanBeCreatedWithNoExpiry(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming', null);
        $this->assertSame(null, $ban->expires_at);
        $this->assertFalse($ban->is_expired);
    }

    public function testCanBanFromBoard(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $board = new Edaha\Entities\Board('name', 'directory');
        $ban->boards->add($board);
        $this->assertCount(1, $ban->boards);
        $this->assertSame($board, $ban->boards[0]);
    }
}
