<?php
namespace Edaha\Entities;

class Board
{
    public object $db;

    public int $board_id;
    public string $board_name;
    protected array $options = [];

    protected function __construct(int $board_id, ?object &$db = null)
    {
        $this->board_id = $board_id;
        $this->db       = $db;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return null;
    }

    protected function validate() 
    {
        $board_exists = $this->db->select("boards")
            ->fields("boards")
            ->condition("board_id", $this->board_id)
            ->countQuery()
            ->execute()
            ->fetchField();
        return ($board_exists == 1);
    }

    public function loadBoardFields()
    {
        $results = $this->db->select("boards")
            ->fields("boards")
            ->condition("board_id", $this->board_id)
            ->execute()
            ->fetchAssoc();

        foreach ($results as $key => $value) {
            if (!is_null($value)) {
                if (property_exists(__CLASS__, $key)) {
                    $this->$key = $value;
                } else {
                    $this->options[$key] = $value;
                }
            }
        }
    }

    public static function loadBoardFromDb(int $board_id, object &$db)
    {
        $board = new Board($board_id, $db);
        if (!$board->validate()) return false;
        $board->loadBoardFields();
        return $board;
    }
}