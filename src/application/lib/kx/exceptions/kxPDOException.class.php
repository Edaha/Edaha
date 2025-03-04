<?php

class kxPDOException extends kxDBException {
  public function __construct($message) {
    parent::__construct(sprintf("Wrapped PDO Exception: [%s]", $message));
  }
}
