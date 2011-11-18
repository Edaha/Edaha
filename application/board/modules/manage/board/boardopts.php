<?php

class manage_board_board_boardopts extends kxCmd {
  public function exec(kxEnv $environment){
    kxTemplate::output("manage/boardopts", array());
  }
}