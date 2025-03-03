<a name="{{ post.post_id }}"></a>
<input type="checkbox" name="post[]" value="{{ post.post_id }}" />
{% if post.post_subject != '' %}
  <span class="subject">{{ post.post_subject }}</span>
{% endif %}
{% apply spaceless %}
  {% if post.post_authority == 1 %}
    <span class="capcode_admin">
  {% elseif post.post_authority == 2 %}
    <span class="capcode_mod">
  {% endif %}
  <span class="postername">
  {% if post.post_email and board.board_anonymous %}
    <a href="mailto:{{post.post_email}}">
  {% endif %}
  {% if post.post_name == '' and post.post_tripcode == '' %}
    {{board.board_anonymous}}
  {% elseif post.post_name == '' and post.post_tripcode != '' %}
  {% else %}
    {{post.post_name}}
  {% endif %}
  {% if post.post_email != '' and board.board_anonymous != '' %}
    </a>
  {% endif %}
  </span>

  {% if post.post_tripcode != '' %}
    <span class="postertrip">!{{post.post_tripcode}}</span>
  {% endif %}
{% endapply %}
{% if post.post_authority == 1 %}
  <span title="{{ kxEnv("site:name") }} {% trans "administrator" %}" class="capcode">
    &#35;&#35;&nbsp;{% trans "Admin" %}&nbsp;&#35;&#35;
  </span>
{% elseif post.post_authority == 2 %}
  <span title="{{ kxEnv("site:name") }} {% trans "moderator" %}" class="capcode">
    &#35;&#35;&nbsp;{% trans "Mod" %}&nbsp;&#35;&#35;
  </span>
{% endif %}
{% if post.post_authority >= 1 %}
  </span>
{% endif %}
{{post.timestamp_formatted}}
<span class="reflink">
  {{post.reflink|raw}}
</span>
{% if board.board_show_id %}
  ID: {{post.ipmd5|slice(0,5)}}
{% endif %}
<span id="dnb_{{board.board_name}}_{{post.post_id}}_y"></span>

<span class="extrabtns">
{% if post.post_locked == 1 %}
  <img style="border: 0;" src="{{boardpath}}css/locked.gif" alt="{% trans "Locked" %}" />
{% endif %}
{% if post.post_stickied == 1 %}
  <img style="border: 0;" src="{{boardpath}}css/sticky.gif" alt="{% trans "Stickied" %}" />
{% endif %}
<span id="hide_{{post.post_id}}"></span>
{% if kxEnv("extra:watchthreads") %}
  <span id="watch_{{post.post_id}}"></span>
{% endif %}
{% if kxEnv("extra:expand") and post.replies and (post.replies + kxEnv("display:replies")) < 300 %}
  <span id="expand_{{post.post_id}}"></span>
{% endif %}
{% if kxEnv("extra:quickreply") %}
  <span id="quickreply_{{post.post_id}}"></span>
{% endif %}
</span>

{% if post.replies > 1000 %}
  {% if kxEnv("display:traditionalread") %}
    &#91;<a href="{{ kxEnv("paths:main:path") }}/read.php/{{board.board_name}}/{{post.post_id}}/p1-100">{% trans "Reply" %}</a>&#93;
    &#91;<a href="{{ kxEnv("paths:main:path") }}/read.php/{{board.board_name}}/{{post.post_id}}/l50">{% trans "Last 50 posts" %}</a>&#93;
  {% else %}
    &#91;<a href="{{ kxEnv("paths:main:path") }}/read.php?b={{board.board_name}}&t={{post.post_id}}&p=p1-100">{% trans "Reply" %}</a>&#93;
    &#91;<a href="{{ kxEnv("paths:main:path") }}/read.php?b={{board.board_name}}&t={{post.post_id}}&p=l50">{% trans "Last 50 posts" %}</a>&#93;
  {% endif %}
{% else %}
  [<a href="{{ kxEnv("paths:boards:path") }}/{{board.board_name}}/res/{{post.post_id}}.html">{% trans "Reply" %}</a>]
  {% if kxEnv("extra:firstlast") and ((post.post_stickied == 1 and post.replies + kxEnv("display:stickyreplies") > 50) or (post.post_stickied == 0 and post.replies + kxEnv("display:replies") > 50)) %}
    {% if ((post.post_stickied == 1 and post.replies + kxEnv("display:stickyreplies") > 100) or (post.post_stickied == 0 and post.replies + kxEnv("display:replies") > 100)) %}
      [<a href="{{ kxEnv("paths:boards:path") }}/{{board.board_name}}/res/{% if post.post_parent == 0 %}{{post.post_id}}{% else %}{{post.post_parent}}{% endif %}-100.html">{% trans "First 100 posts" %}</a>]
    {% endif %}
    [<a href="{{ kxEnv("paths:boards:path") }}/{{board.board_name}}/res/{{post.post_id}}+50.html">{% trans "Last 50 posts" %}</a>]
  {% endif %}
{% endif %}