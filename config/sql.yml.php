#!php 
#<?php die('NOPE'); ?>
all:
  kx:
    db:
      prepared_statements:
        count_posts:
          sql: SELECT COUNT(*) FROM `posts` WHERE `removed` = '0'

        select_post:
          sql: SELECT * FROM `posts` WHERE id = :id
          required: [id]
