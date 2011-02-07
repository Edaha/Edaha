<?php

class manage_core_index_index extends kxCmd {

  public function exec( kxEnv $environment ) {
    $dbsize = 0;
  	switch ($this->db->driver()) {
    	case 'mysql':
      	$dwoo_data['dbtype'] = 'MySQL';
        $results = $this->db->query("SHOW TABLE STATUS");
        foreach ($results as $line) {
        	$dbsize += ($line->data_length+$line->index_length);
        }
        
      break;
      case 'pgsql':
      	$dwoo_data['dbtype'] = 'PostgreSQL';
        $dbsize = $this->db->query("SELECT pg_database_size('".substr(kxEnv::get("kx:db:dsn"), (strpos(kxEnv::get("kx:db:dsn"), "dbname=")+7), strlen(kxEnv::get("kx:db:dsn")))."')")->execute()->fetchColumn();
      break;
      case 'sqlite':
      	$dwoo_data['dbtype'] = 'SQLite';
        $dbsize = filesize(substr(kxEnv::get("kx:db:dsn"), (strpos(kxEnv::get("kx:db:dsn"), "sqlite:")+7), strlen(kxEnv::get("kx:db:dsn"))));
      break;
      default:
      	$dwoo_data['dbtype'] = $this->db->driver();
    }
    $dwoo_data['dbsize'] = kxFunc::convertBytes($dbsize);
    $dwoo_data['dbversion'] = substr($this->db->version(), 0, strrpos($this->db->version(), '-') !== FALSE ? strrpos($this->db->version(), '-') : strlen($this->db->version()));
    
    $dwoo_data['stats']['numboards'] = $this->db->select("boards")
    																	 ->countQuery()
                                       ->execute()
                                       ->fetchField();
    $dwoo_data['stats']['totalposts'] = $this->db->select("posts")
    																	 ->countQuery()
                                       ->execute()
                                       ->fetchField();
  	kxTemplate::output("manage_index", $dwoo_data);
  }
}