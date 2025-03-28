<?php
namespace Edaha\Entities;

class Post implements EntityInterface
{
    public object $db;

    public int $post_id;
    public int $board_id;
    public int $parent_post_id;
    public string $name;
    public string $tripcode;
    public string $email;
    public string $password;
    public string $message;
    public string $subject;
    public int $authority;
    public string $tag;
    public string $ip;
    public string $ip_md5;

    public \DateTimeImmutable $created_at_timestamp {
        set(string|\DateTimeImmutable $timestamp) {
            $this->created_at_timestamp = new \DateTimeImmutable($timestamp, new \DateTimeZone('UTC'));
        }
    }
    public \DateTimeImmutable $deleted_at_timestamp {
        set(string|\DateTimeImmutable $timestamp) {
            $this->deleted_at_timestamp = new \DateTimeImmutable($timestamp, new \DateTimeZone('UTC'));
        }
    }
    public \DateTime $bumped_at_timestamp {
        set(string|\DateTime $timestamp) {
            $this->bumped_at_timestamp = new \DateTime($timestamp, new \DateTimeZone('UTC'));
        }
    }

    public bool $is_locked = false;
    public bool $is_stickied = false;
    public bool $is_deleted = false;
    public bool $is_reviewed = false;

    protected function __construct(int $board_id, int $post_id, ?object &$db = null)
    {
        $this->board_id = $board_id;
        $this->post_id  = $post_id;
        if (!is_null($db)) $this->db = $db;
    }

    protected function loadPostFields()
    {
        $post_query = $this->db->select("posts")
            ->fields("posts")
            ->condition("post_id", $this->post_id)
            ->condition("board_id", $this->board_id)
            ->execute()
            ->fetchAssoc();

        foreach ($post_query as $key => $value) {
            if (!is_null($value)) $this->$key = $value;
        }
    }

    protected function validatePost() 
    {
        $post_exists = $this->db->select("posts")
            ->fields("posts")
            ->condition("post_id", $this->post_id)
            ->condition("board_id", $this->board_id)
            ->countQuery()
            ->execute()
            ->fetchField();
        return ($post_exists == 1);
    }

    public function delete()
    {
        if (!isset($this->db)) return false;
        $this->is_reviewed = 1;
        $this->is_deleted = 1;
        $this->deleted_at_timestamp = date('Y-m-d H:i:s');

        $fields = [
            "is_reviewed" => $this->is_reviewed,
            "is_deleted" => $this->is_deleted,
            "deleted_at_timestamp" => $this->deleted_at_timestamp->format('Y-m-d H:i:s'),
        ];

        $results = $this->db->update("posts")
            ->fields($fields)
            ->condition('post_id', $this->post_id)
            ->condition('board_id', $this->board_id)
            ->execute();
        
        $this->deletePostFiles();

        return ($results > 0);
    }

    protected function deletePostFiles()
    {
        $post_files = $this->db->select("post_files")
            ->fields("post_files", ["file_board", "file_name"])
            ->condition("file_board", $this->board_id)
            ->condition("file_post", $this->post_id)
            ->execute();
        
        while ($row = $post_files->fetch()) {
            PostAttachment::deleteFile($row->file_board, $row->file_name, $this->db); 
        }
    }

    public static function loadFromDb(array $identifiers, object &$db)
    {
        if (!array_key_exists('board_id', $identifiers) || !array_key_exists('post_id', $identifiers)) return null;

        $post           = new Post($identifiers['board_id'], $identifiers['post_id'], $db);
        if (!$post->validatePost()) return null;

        $post->loadPostFields();
        return $post;
    }

    public static function loadFromAssoc(array $assoc)
    {
        $post = new Post($assoc['board_id'], $assoc['post_id']);

        foreach ($assoc as $key => $value) {
            if (!is_null($value)) $post->$key = $value;
        }
        
        return $post;
    }

    public static function getRecentPosts(object &$db, int $rows_to_return = 50, int $page = 0)
    {
        $recent_posts = [];
        $results = $db->select("posts")
            ->fields("posts")
            ->condition("is_deleted", false)
            ->orderBy("created_at_timestamp", "DESC")
            ->range(($page * $rows_to_return), $rows_to_return)
            ->execute();
        
        while ($row = $results->fetchAssoc()) {
            $recent_posts[] = Post::loadFromAssoc($row);
        }

        return $recent_posts;
    }
}
