<?php

class manage_core_index_index extends kxCmd {

  public function exec( kxEnv $environment ) {
    $twigData['dbtype'] = kxEnv::get('kx:db:adapter');
    $twigData['dbsize'] = 'todo';
    $twigData['dbversion'] = 'todo';
    $twigData['stats']['numboards'] = $this->entityManager->getRepository('Edaha\Entities\Board')->count();
    $twigData['stats']['totalposts'] = $this->entityManager->getRepository('Edaha\Entities\Post')->count();
    $twigData['stats']['edahaversion'] = 'todo';
    kxTemplate::output("manage/index", $twigData);
  }
}