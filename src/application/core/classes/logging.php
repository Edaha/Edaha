<?php
// Stub
class logging
{
  protected $environment;
  protected $db;
  protected $request;

  public function __construct(kxEnv $environment)
  {
    $this->environment = $environment;
    $this->db = kxDB::getInstance();
    $this->request = kxEnv::$request;
  }

  public static function addLogEntry($user_name, $log_entry, $source)
  {
    $fields['user'] = $user_name;
    $fields['entry'] = $log_entry;
    $fields['source_module'] = $source;
    $fields['timestamp'] = time();
    $modlog = kxDB::getInstance()->insert("modlog")
      ->fields($fields)
      ->execute();
  }
  public static function addReport($board_id, $post_ids, $reason)
  {
    foreach ($post_ids as $post_id) {
      kxDb::getInstance()->insert("reports")
        ->fields([
          'board_id' => (int) $board_id,
          'post_id' => (int) $post_id,
          'timestamp' => time(),
          'ip' => $_SERVER['REMOTE_ADDR'],
          'reason' => $reason,
        ])
        ->execute();
    }
  }
}
