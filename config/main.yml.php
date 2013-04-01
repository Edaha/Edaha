#!php 
#<?php header('HTTP/1.1 404 Not Found'); die(); ?>
---
all:
  kx:
    charset: utf-8
    db:
      dsn: 'mysql:dbname=edaha'
      username: edaha
      password: edaha
      prefix: 
      persistent: false
    site:
      name: Edaha
      slogan: '<em>slogan!</em>'
      headerimage:
      irc:
      banreason:
    paths:
      main:
        path: 
        domain:
      boards:
        path: 
      script:
        path:
      coral:
        web:
        boards:
    templates:
      dir: /application/templates/
      cachedir: /application/templates/compiled/
    css:
      imgstyles: edaha:burichan:futaba
      imgdefault: edaha
      txtstyles: futatxt:buritxt
      txtdefault: futatxt
      imgswitcher: true
      imgdropswitcher: false
      txtswitcher: true
      sitestyles: edaha:burichan:futaba
      sitedefault: edaha
      siteswitcher: false
    limits:
      threaddelay: 30
      replydelay: 7
      linelength: 150
    images:
      thumbw: 200
      thumbh: 200
      replythumbw: 125
      replythumbh: 125
      catthumbw: 50
      catthumbh: 50
      method: gd
      animated: false
    posts:
      newwindow: true
      makelinks: true
      emptythread:
      emptyreply:
    display:
      imgthreads: 10
      txtthreads: 15
      replies: 3
      stickyreplies: 1
      thumbmsg: false
      banmsg: '<br /><span class="banmsg">(USER WAS BANNED FOR THIS POST)</span>'
      traditionalread: false
      embedw: 200
      embedh: 164
    pages:
      first: board.html
      dirtitle: true
    tags:
    trips:
    extra:
      rss: true
      expand: true
      quickreply: true
      watchthreads: true
      firstlast: true
      blotter: true
      sitemap: false
      appeal: false
      postspy: false
    misc:
      modlogdays: 7
      randomseed: ENTER RANDOM LETTERS/NUMBERS HERE
      staticmenu: false
      boardlist: true
      locale: en
      charset: UTF-8
      timezone: TZ=US/Pacific
      dateformat: d/m/y(D)H:i
      debug: false
      version: 1.0
      apc: false
      