<?php
use PHPUnit\Framework\TestCase;

final class SectionTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $board = new Edaha\Entities\Board('name', 'directory');
        $section = new Edaha\Entities\Section(
            name: 'General',
            hidden: false,
        );

        $this->assertSame('General', $section->name);
        $this->assertFalse($section->is_hidden);
        $this->assertInstanceOf(DateTime::class, $section->created_at);
    }

    public function testCanAddBoard(): void
    {
        $section = new Edaha\Entities\Section(
            name: 'General',
            hidden: false,
        );

        $board = new Edaha\Entities\Board('name', 'directory');
        $section->addBoard($board);
        $this->assertCount(1, $section->boards);
        $this->assertSame($board, $section->boards[0]);
        $this->assertSame($section, $board->section);
    }

    public function testCanRemoveBoard(): void
    {
        $section = new Edaha\Entities\Section(
            name: 'General',
            hidden: false,
        );

        $board = new Edaha\Entities\Board('name', 'directory');
        $section->addBoard($board);
        $section->removeBoard($board);
        $this->assertCount(0, $section->boards);
        $this->assertNull($board->section);
    }
}