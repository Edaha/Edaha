<?php

class manage_core_index_index extends kxCmd {

  public function exec( kxEnv $environment ) {
    $dbsize = 0;
    switch ($this->db->driver()) {
      case 'mysql':
        $twigData['dbtype'] = 'MySQL';
        $results = $this->db->query("SHOW TABLE STATUS");
        foreach ($results as $line) {
          $dbsize += ($line->data_length+$line->index_length);
        }
        
      break;
      case 'pgsql':
        $twigData['dbtype'] = 'PostgreSQL';
        $results = $this->db->query("SELECT pg_database_size('".substr(kxEnv::get("kx:db:dsn"), (strpos(kxEnv::get("kx:db:dsn"), "dbname=")+7), strlen(kxEnv::get("kx:db:dsn")))."')");
        foreach($results as $line) {
          $dbsize += $line->pg_database_size;
        }
      break;
      case 'sqlite':
        $twigData['dbtype'] = 'SQLite';
        $dbsize = filesize(substr(kxEnv::get("kx:db:dsn"), (strpos(kxEnv::get("kx:db:dsn"), "sqlite:")+7), strlen(kxEnv::get("kx:db:dsn"))));
      break;
      default:
        $twigData['dbtype'] = $this->db->driver();
    }
    $twigData['dbsize'] = kxFunc::convertBytes($dbsize);
    $twigData['dbversion'] = substr($this->db->version(), 0, strrpos($this->db->version(), '-') !== FALSE ? strrpos($this->db->version(), '-') : strlen($this->db->version()));
    
    $twigData['stats']['numboards'] = $this->db->select("boards")
                                       ->countQuery()
                                       ->execute()
                                       ->fetchField();
    $twigData['stats']['totalposts'] = $this->db->select("posts")
                                       ->countQuery()
                                       ->execute()
                                       ->fetchField();
    $twigData['stats']['edahaversion'] = kxEnv::get("cache:version");
    kxTemplate::output("manage/index", $twigData);
  }
}