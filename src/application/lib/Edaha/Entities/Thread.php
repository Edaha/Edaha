<?php
namespace Edaha\Entities;

class Thread extends Post
{
    public array $replies = [];

    public static function loadThread(int $board_id, int $post_id, object &$db)
    {
        $thread = new Thread($board_id, $post_id, $db);

        if (!$thread->validatePost()) return false;
        $thread->loadPostFields();
        if (!$thread->validateThread()) return false;
        return $thread;
    }

    public function validateThread()
    {
        return ($this->post_parent == 0) ? true : false;
    }

    public function getAllReplies(bool $include_deleted = false)
    {
        $results = $this->db->select("posts")
            ->fields("posts")
            ->condition("post_parent", $this->id)
            ->condition("post_board", $this->board_id);
        
        if ($include_deleted) {
            $results = $results->condition("post_deleted", false);
        }

        $results = $results->orderBy("post_id", "ASC")
            ->execute();
        

        while ($row = $results->fetchAssoc()) {
            $reply = new Post($row['post_id'], $row['post_board'], $this->db);
            foreach ($row as $key => $value) {
                $reply->$key = $value;
            }
            $this->replies[] = $reply;
        }
    }
}