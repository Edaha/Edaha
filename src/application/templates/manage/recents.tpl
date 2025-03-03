{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Recent Posts & Images" %}{% endblock %}

{% block managecontent %}
  <form action="{{ base_url }}app=board&amp;module=recents&amp;section=recents&amp;do=process" method="post">
    <fieldset id="posts">
      <legend>{% trans "Recent Posts & Images" %}</legend>
      <label for="delete">{{ "Delete"|trans }}</label>
      <input type="radio" name="action" id="delete" value="delete">
      <br>
      <label for="approve">{{ "Approve"|trans }}</label>
      <input type="radio" name="action" id="approve" value="approve">
      <br>
      <input type="submit" name="submit" value="{{ "Submit"|trans }}">
      {% for post in recent_posts %}
        <div>
          <input type="checkbox" name="posts[]" value="{{ "%s|%s"|format(post.board_id, post.post_id) }}">
          {{ ">>>/%s/%d: %s"|format(
            post.board_name,
            post.post_id,
            post.post_message
          )|striptags|raw
        }}
        </div>
        <hr>
      {% endfor %}
      
    </fieldset>
  </form>
  
{% endblock %}