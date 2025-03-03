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