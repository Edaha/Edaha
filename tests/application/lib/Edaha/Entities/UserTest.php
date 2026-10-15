<?php
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $user = new Edaha\Entities\User(
            username: 'edaha',
            password: 'edaha'
        );

        $this->assertSame('edaha', $user->username);
    }

    public function testValidLogin(): void
    {
        $user = new Edaha\Entities\User(
            username: 'edaha',
            password: 'edaha'
        );

        $this->assertTrue($user->checkLogin('edaha'));
    }

    public function testInvalidLogin(): void
    {
        $user = new Edaha\Entities\User(
            username: 'edaha',
            password: 'edaha'
        );

        $this->assertFalse($user->checkLogin('kusaba'));
    }

    public function testLocksAfterInvalidLogins(): void
    {
        $user = new Edaha\Entities\User(
            username: 'edaha',
            password: 'edaha'
        );

        for ($i = 0; $i < 5; $i++) {
            $user->checkLogin('kusaba');
        }

        $this->assertTrue($user->is_locked);

    }

}