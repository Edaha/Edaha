<?php
namespace Edaha\Entities;

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

    public bool $is_locked;
    public bool $is_stickied;
    public bool $is_deleted {
        get {
            return ($this->deleted_at > 0);
        }
    }

    public int $created_at;
    public int $deleted_at;
    public int $bumped_at;

    public static function LoadPost(int $board_id, int $post_id, object $db)
    {
        $post           = new Post();
        $post->board_id = $board_id;
        $post->id       = $post_id;
        $post->db       = $db;

        $post_query = $post->db->select("posts")
            ->fields("posts")
            ->condition("post_id", $post->id)
            ->condition("post_board", $post->board_id)
            ->execute()
            ->fetchAssoc();

        $post->name        = $post_query['post_name'];
        $post->tripcode    = $post_query['post_tripcode'];
        $post->email       = $post_query['post_email'];
        $post->password    = $post_query['post_password'];
        $post->message     = $post_query['post_message'];
        $post->ip          = $post_query['post_ip'];
        $post->ip_md5      = $post_query['post_ip_md5'];
        $post->is_locked   = (bool) $post_query['post_locked'];
        $post->is_stickied = (bool) $post_query['post_stickied'];
        $post->created_at  = $post_query['post_timestamp'];
        $post->deleted_at  = $post_query['post_deleted'];
        $post->bumped_at   = $post_query['post_bumped'];

        return $post;
    }
}
