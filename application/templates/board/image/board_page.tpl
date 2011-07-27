{% extends "board/image/post_box.tpl" %}
{% block boardcontent %}
  {{ parent() }}
  <form id="delform" action="{% kxEnv "paths:script:path" %}/board.board_php" method="post">
  <input type="hidden" name="board" value="{{board.board_name}}" />
  {% for thread in posts %}
    {% set iteration = loop.index0 %}
    {% for post in thread %}
      {% if post.post_parent == 0 %}
        <div class="thread" id="thread_{{post.post_id}}_{{board.board_name}}">
          <span id="unhidethread{{post.post_id}}{{board.board_name}}" style="display: none;">
            {% trans "Thread" %} <a href="{%kxEnv "paths:boards:path" %}/{{board.board_name}}/res/{{post.post_id}}.html">{{post.post_id}}</a> {% trans "hidden." %}
            <a href="#" id="togglethread" onclick="javascript:togglethread('{{post.post_id}}{{board.board_name}}');return false;" title="{% trans "Un-Hide Thread" %}">
              <img src="{% kxEnv "paths:main:path" %}css/icons/blank.gif" border="0" class="unhidethread" alt="{% trans "Un-Hide Thread" %}" />
            </a>
          </span>
          <script type="text/javascript"><!--
            if (kusaba.hiddenthreads.toString().indexOf('{{post.post_id}}{{board.board_name}}')!==-1) {
              document.getElementById('unhidethread{{post.post_id}}{{board.board_name}}').style.display = 'block';
              document.getElementById('thread{{post.post_id}}{]board.board_name}}').style.display = 'none';
            }
          //--></script>
          <a name="s{{iteration}}"></a>
          <div class="op" id="p{{post.post_id}}">
            {% include "board/global/post_file_info.tpl" %}
            <div class="post">
              {% include "board/global/post_header.tpl"%}
              <span class="extrabtns">
              {% if post.post_locked == 1 %}
                <img style="border: 0;" src="{{boardpath}}css/locked.gif" alt="{% trans "Locked" %}" />
              {% endif %}
              {% if post.post_stickied == 1 %}
                <img style="border: 0;" src="{{boardpath}}css/sticky.gif" alt="{% trans "Stickied" %}" />
              {% endif %}
              <span id="hide_{{post.post_id}}"></span>
              {% if 'extra:watchthreads'|kxEnv %}
                <span id="watch_{{post.post_id}}"></span>
              {% endif %}
              {% if 'extra:expand'|kxEnv and post.replies and (post.replies + 'display:replies'|kxEnv) < 300 %}
                <span id="expand_{{post.post_id}}"></span>
              {% endif %}
              {% if 'extra:quickreply'|kxEnv %}
                <span id="quickreply_{{post.post_id}}"></span>
              {% endif %}
              </span>
              <span id="dnb_{{board.board_name}}_{{post.post_id}}_y"></span>
              {% if post.replies > 1000 %}
                {% if 'display:traditionalread'|kxEnv %}
                  &#91;<a href="{% kxEnv "paths:main:path" %}/read.php/{{board.board_name}}/{{post.post_id}}/p1-100">{% trans "Reply" %}</a>&#93;
                  &#91;<a href="{% kxEnv "paths:main:path" %}/read.php/{{board.board_name}}/{{post.post_id}}/l50">{% trans "Last 50 posts" %}</a>&#93;
                {% else %}
                  &#91;<a href="{% kxEnv "paths:main:path" %}/read.php?b={{board.board_name}}&t={{post.post_id}}&p=p1-100">{% trans "Reply" %}</a>&#93;
                  &#91;<a href="{% kxEnv "paths:main:path" %}/read.php?b={{board.board_name}}&t={{post.post_id}}&p=l50">{% trans "Last 50 posts" %}</a>&#93;
                {% endif %}
              {% else %}
                [<a href="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/res/{{post.post_id}}.html">{% trans "Reply" %}</a>]
                {% if 'extra:firstlast'|kxEnv and ((post.post_stickied == 1 and post.replies + 'display:stickyreplies'|kxEnv > 50) or (post.post_stickied == 0 and post.replies + 'display:replies'|kxEnv > 50)) %}
                  {% if ((post.post_stickied == 1 and post.replies + 'display:stickyreplies'|kxEnv > 100) or (post.post_stickied == 0 and post.replies + 'display:replies'|kxEnv > 100)) %}
                    [<a href="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/res/{% if post.post_parent == 0 %}{{post.post_id}}{% else %}{{post.post_parent}}{% endif %}-100.html">{% trans "First 100 posts" %}</a>]
                  {% endif %}
                  [<a href="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/res/{{post.post_id}}+50.html">{% trans "Last 50 posts" %}</a>]
                {% endif %}
              {% endif %}
      {% else %}
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
  {% endfor %}
{% endblock %}