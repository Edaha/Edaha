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

    public static function loadThread(int $board_id, int $post_id, object &$db)
    {
        $thread = new Thread($board_id, $post_id, $db);

        if (!$thread->validatePost()) return false;
        $thread->loadPostFields();
        if (!$thread->validateThread()) return false;
        return $thread;
    }

    public static function loadThreadFromAssoc(array $assoc, ?object &$db = null)
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
        
        if ($include_deleted) {
            $results = $results->condition("is_deleted", false);
        }

        $results = $results->orderBy("post_id", "ASC")
            ->execute();
        
        while ($row = $results->fetchAssoc()) {
            $this->replies[] = Post::loadPostFromAssoc($row);
        }
    }
}