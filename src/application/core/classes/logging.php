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
    // Wrap in a try/finally so that if the module doesn't exist, we don't have an error
    try {
        $fields['user'] = $user_name;
        $fields['entry'] = $log_entry;
        $fields['source_module'] = $source;
        $fields['timestamp'] = time();
        $modlog = kxDB::getInstance()->insert("modlog")
                ->fields($fields)
                ->execute();
    } finally {
    }
  }
}