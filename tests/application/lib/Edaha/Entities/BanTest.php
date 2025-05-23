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
        $ban = new Edaha\Entities\Ban(
            ip: '192.168.69.1', 
            reason: 'spamming', 
            expires_at: New DateTime('+1 seconds'));
        $this->assertFalse($ban->is_expired);
        sleep(1);
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
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $this->assertSame(null, $ban->expires_at);
        $this->assertFalse($ban->is_expired);
    }

    public function testCanBanFromBoard(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $board = new Edaha\Entities\Board('name', 'directory');
        $ban->addBoard($board);
        $this->assertCount(1, $ban->boards);
        $this->assertSame($board, $ban->boards[0]);
    }

    public function testCanUnbanFromBoard(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $board = new Edaha\Entities\Board('name', 'directory');
        $ban->addBoard($board);
        $this->assertCount(1, $ban->boards);
        $this->assertSame($board, $ban->boards[0]);
        $ban->removeBoard($board);
        $this->assertCount(0, $ban->boards);
    }

    public function testCanAppealBan(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $ban->appealBan('I am sorry');
        $this->assertSame('I am sorry', $ban->appeal->message);
    }

    public function testCanApproveAppeal(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $ban->appealBan('I am sorry');
        $ban->approveAppeal();
        $this->assertTrue($ban->is_expired);
        $this->assertTrue($ban->appeal->is_approved);
    }

    public function testCanGloballyBan(): void
    {
        $ban = new Edaha\Entities\Ban('192.168.69.1', 'spamming');
        $ban->banGlobally();
        $this->assertTrue($ban->is_global);
        $this->assertCount(0, $ban->boards);

        $board = new Edaha\Entities\Board('name', 'directory');
        $this->assertTrue($ban->isBannedFromBoard($board));
    }
}
