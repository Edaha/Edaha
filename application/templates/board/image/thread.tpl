{% extends "board/image/post_box.tpl" %}
{% block boardcontent %}
  {{ parent() }}
  <form id="delform" action="{% kxEnv "paths:script:path" %}/board.board_php" method="post">
  <input type="hidden" name="board" value="{{board.board_name}}" />
  {% for post in posts %}
    {% if post.post_parent == 0 %}
      <div class="thread" id="thread_{{post.post_id}}_{{board.board_name}}">
        <div class="op" id="p{{post.post_id}}">
          {% include "board/global/post_file_info.tpl" %}
          <div class="post">
            {% include "board/global/post_header.tpl"%}
            <span id="dnb_{{board.board_name}}_{{post.post_id}}_y"></span>
    {% else %}
      {% if numimages > 0 and isexpand and loop.first %}
        <a href="#top" onclick="$('#thread{{post.post_parent}}_{{board.board_name}} [id^=expandimg], #thread{$post.parentid}{$board.name} .file_size a').click();return false;">{t "Expand all images"}</a>
      {% endif %}
      <div class="reply"  id="reply_{{post.post_id}}">
        <div class="post">
          <div class="doubledash">&gt;&gt;</div>
          {% include "board/global/post_header.tpl" %}
          <span id="dnb_{{board.board_name}}_{{post.post_id}}_n"></span>
    {% endif %}
    {% if "mp3" in post.file_type %}
      {% for fkey,file in post.file_name %}
        {% if fkey == 0 and post.file_name|length > 1 %}
          <br />
        {% endif %}
        {% if post.file_type[fkey] == 'mp3' %}
          <!--[if !IE]> -->
          <object type="application/x-shockwave-flash" data="{% kxEnv "paths:main:path" %}/inc/player/player.swf?playerID={{post.post_id}}&amp;soundFile={{file_path}}/src/{{post.file_name[fkey]|url_encode|e}}.mp3{% if post.post_id3[fkey].comments_html.artist.0 %}&amp;artists={{post.post_id3[fkey].comments_html.artist.0}}{% endif %}{% if post.post_id3[fkey].comments_html.title.0 %}&amp;titles={{post.post_id3[fkey].comments_html.title.0}}{% endif %}&amp;wmode=transparent" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,22,87" width="290" height="24">
          <param name="wmode" value="transparent" />
          <!-- <![endif]-->
          <!--[if IE]>
          <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,22,87" width="290" height="24">
            <param name="movie" value="{% kxEnv "paths:main:path" %}/inc/player/player.swf?playerID={{post.post_id}}&amp;soundFile={{file_path}}/src/{{post.file_name[fkey]|url_encode|e}}.mp3{% if post.post_id3[fkey].comments_html.artist.0 %}&amp;artists={{post.post_id3[fkey].comments_html.artist.0}}{% endif %}{% if post.post_id3[fkey].comments_html.title.0 %}&amp;titles={{post.post_id3[fkey].comments_html.title.0}}{% endif %}&amp;wmode=transparent" />
            <param name="wmode" value="transparent" />
          <!-->
          </object>
          <!-- <![endif]-->
          {% if post.file_name|length > 1 %}
            <br />
          {% endif %}
        {% endif %}
        {% if fkey > 0 and loop.last %}
          <br style="clear:both" />
        {% endif %}
      {% endfor %}
    {% endif %}  
    <p class="message">
      {% if post.videobox %}
        {{post.videobox}}
      {% endif %}
      {{post.post_message}}
    </p>
    {% if not post.post_stickied and post.post_parent == 0 and ((board.board_name > 0 and (post.post_timestamp + (board.board_name * 3600)) < ("now"|date("U") + 7200 ) ) or (post.post_delete_time > 0 and post.post_delete_time <= ("now"|date("U") + 7200))) %}
      <span class="oldpost">
        {% trans "Marked for deletion (old)" %}
      </span>
      <br />
    {% endif %}
    {% if post.post_parent == 0 %}
        </div>
      </div>
      <div id="replies_{{post.post_id}}_{{board.board_name}}">
      {# needs to be redone
      {% if post.replies %}
        <span class="omittedposts">
          {% if post.post_stickied == 0 %}
            {{post.replies}} 
            {% if post.replies == 1 %}
              {t "Post" lower="yes"} 
            {else}
              {t "Posts" lower="yes"} 
            {% endif %}
          {else}
            {$post.replies}
            {if $post.replies == 1}
              {t "Post" lower="yes"} 
            {else}
              {t "Posts" lower="yes"} 
            {% endif %}
          {% endif %}
          {if $post.images > 0}
            {t "and"} {$post.images}
            {if $post.images == 1}
              {t "Image" lower="yes"} 
            {else}
              {t "Images" lower="yes"} 
            {% endif %}
          {% endif %}
          {t "omitted"}. {t "Click Reply to view."}
        </span>
      {% endif %}
      #}
    {% else %}
      </div>
          </div>
    {% endif %}
  {% endfor %}
    </div>
    </div>
    {% if locale == 'he' %}
      <br clear="right" />
    {% else %}
      <br clear="left" />
    {% endif %}
    <hr />
{% endblock %}