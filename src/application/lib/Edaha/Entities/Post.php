<?php
namespace Edaha\Entities;

/* Nothing more permanent than a temporary fix */
/* Adding this until finalizing the db model revamp */
#[\AllowDynamicProperties]
class Post
{
    public int $id;
    public int $board_id;
    public string $name;
    public string $tripcode;
    public string $email;
    public string $password;
    public string $message;
    public string $ip;
    public string $ip_md5;

    public int $created_at;
    public int $deleted_at;
    public int $bumped_at;

    public bool $is_locked = false;
    public bool $is_stickied = false;
    public bool $is_deleted {
        get {
            return ($this->post_deleted > 0);
        }
    }

    protected function __construct(int $board_id, int $post_id, ?object &$db = null)
    {
        $this->board_id = $board_id;
        $this->id       = $post_id;
        $this->db       = $db;
    }

    protected function loadPostFields()
    {
        $post_query = $this->db->select("posts")
            ->fields("posts")
            ->condition("post_id", $this->id)
            ->condition("post_board", $this->board_id)
            ->execute()
            ->fetchAssoc();

        foreach ($post_query as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function validatePost() 
    {
        $post_exists = $this->db->select("posts")
            ->fields("posts")
            ->condition("post_id", $this->id)
            ->condition("post_board", $this->board_id)
            ->countQuery()
            ->execute()
            ->fetchField();
        return ($post_exists == 1);
    }

    public static function loadPostFromDb(int $board_id, int $post_id, object &$db)
    {
        $post           = new Post($board_id, $post_id, $db);
        if (!$post->validatePost()) return false;

        $post->loadPostFields();
        return $post;
    }

    public static function loadPostFromAssoc(array $assoc)
    {
        $post = new Post($assoc['post_board'], $assoc['post_id']);

        foreach ($assoc as $key => $value) {
            $post->$key = $value;
        }
        
        return $post;
    }
}
