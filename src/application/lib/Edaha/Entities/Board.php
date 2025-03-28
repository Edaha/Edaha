<?php
namespace Edaha\Entities;

class Board implements EntityInterface
{
    public object $db;

    public int $board_id;
    public string $board_name;
    protected array $options = [];
    public array $threads = [];

    public int $board_uniqueposts {
        get {
            $result = $this->db->select("posts");
            $result->addExpression("COUNT(DISTINCT ip_md5)");
            return $result->condition("board_id", $this->board_id)
                ->condition("is_deleted", 0)
                ->execute()
                ->fetchField();
        }
    }

    public array $board_filetypes_allowed {
        get {
            $result = $this->db->select("filetypes", "f")
                ->fields("f", ["type_ext"]);
            $result->innerJoin("board_filetypes", "bf", "bf.type_id = f.type_id");
            $result->innerJoin("boards", "b", "b.board_id = bf.board_id");
            return $result->condition("bf.board_id", $this->board_id)
                ->orderBy("type_ext")
                ->execute()
                ->fetchCol();
        }
    }

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

    public function __set(string $name, mixed $value)
    {
        $this->options[$name] = $value;
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
            $this->$key = $value;
        }
    }

    public static function loadFromDb(array $identifiers, object &$db) 
    {
        if (!array_key_exists('board_id', $identifiers)) return null;

        $board = new Board($identifiers['board_id'], $db);
        if (!$board->validate()) return false;
        $board->loadBoardFields();
        return $board;
    }

    public static function loadFromDbByName(string $board_name, object &$db)
    {
        $board_id = $db->select("boards")
            ->fields("boards", ['board_id'])
            ->condition('board_name', $board_name)
            ->execute()
            ->fetchField();
        
        if ($board_id) {
            $board = Board::loadFromDb(['board_id' => $board_id], $db);
            return $board;
        }

        return null;
    }

    public static function loadFromAssoc(array $assoc) 
    {
        $post = new Board(['board_id' => $assoc['board_id']]);

        foreach ($assoc as $key => $value) {
            if (!is_null($value)) $post->$key = $value;
        }
        
        return $post;
    }

    public function getAllThreads(bool $include_deleted = false)
    {
        $this->threads = [];
        $results = $this->db->select("posts")
            ->fields("posts")
            ->condition("parent_post_id", 0)
            ->condition("board_id", $this->board_id);
        
        if ($include_deleted) {
            $results = $results->condition("is_deleted", false);
        }

        $results = $results->orderBy("post_id", "ASC")
            ->execute();

        while ($row = $results->fetchAssoc()) {
            $this->threads[] = Thread::loadFromAssoc($row);
        }
    }
}