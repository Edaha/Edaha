<?php
use PHPUnit\Framework\TestCase;

final class BoardTest extends TestCase
{
    public function testCanBeCreatedFromAssoc(): void
    {
        $assoc = [
            'board_id' => 1,
            'board_name' => 'test_board',
            'unspecified_option' => false
        ];

        $board = Edaha\Entities\Board::loadFromAssoc($assoc);

        foreach ($assoc as $key => $value) {
            $this->assertSame($value, $board->$key);
        }
    }

    public function testCanCreateArbitraryProperties(): void
    {
        $assoc = [
            'board_id' => 1,
            'board_name' => 'test_board',
        ];

        $arbitrary_properties = [
            'prop1' => 1,
            'propTrue' => true,
            'propObject' => new stdClass(),
        ];


        $board = Edaha\Entities\Board::loadFromAssoc($assoc);

        foreach ($arbitrary_properties as $key => $value) {
            $board->$key = $value;
            $this->assertSame($value, $board->$key);
        }

    }
}
