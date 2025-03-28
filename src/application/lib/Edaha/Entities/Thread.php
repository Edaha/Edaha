<?php
namespace Edaha\Entities;

class Thread extends Post
{
    public array $replies = [];
    public bool $is_stickied;
    public bool $is_locked;
    public bool $is_valid {
        get {
            return $this->validateThread();
        }
    }
    public static function loadFromDb(array $identifiers, object &$db)
    {
        if (!array_key_exists('board_id', $identifiers) || !array_key_exists('post_id', $identifiers)) return null;

        $thread           = new Thread($identifiers['board_id'], $identifiers['post_id'], $db);
        if (!$thread->validatePost()) return false;
        
        $thread->loadPostFields();
        if (!$thread->validateThread()) return false;
        
        return $thread;
    }

    public static function loadFromAssoc(array $assoc)
    {
        $thread = new Thread($assoc['board_id'], $assoc['post_id'], $db);

        foreach ($assoc as $key => $value) {
            if (!is_null($value)) $thread->$key = $value;
        }
        
        return $thread;
    }

    protected function validateThread()
    {
        return ($this->parent_post_id == 0) ? true : false;
    }

    public function getAllReplies(bool $include_deleted = false)
    {
        $results = $this->db->select("posts")
            ->fields("posts")
            ->condition("parent_post_id", $this->post_id)
            ->condition("board_id", $this->board_id);
        
        if (!$include_deleted) {
            $results = $results->condition("is_deleted", false);
        }

        $results = $results->orderBy("post_id", "ASC")
            ->execute();
        
        while ($row = $results->fetchAssoc()) {
            $this->replies[] = Post::loadFromAssoc($row);
        }
    }

    public function getReplies(string $first_or_last = 'first', int $count_of_replies = 3)
    {
        $this->replies = [];
        $results = $this->db->select("posts")
            ->fields("posts")
            ->condition("parent_post_id", $this->post_id)
            ->condition("board_id", $this->board_id)
            ->condition("is_deleted", false);
        
        switch ($first_or_last) {
            case 'first':
                $results = $results->orderBy("created_at_timestamp", "ASC");
                break;
            case 'last':
                $results = $results->orderBy("created_at_timestamp", "DESC");
                break;
            default:
                return false;
                break;
        }
        $results = $results->range(0, $count_of_replies)
            ->execute();
        
        while ($row = $results->fetchAssoc()) {
            $this->replies[] = Post::loadFromAssoc($row);
        }

        if ($first_or_last == 'last')  $this->replies = array_reverse($this->replies);
    }
}