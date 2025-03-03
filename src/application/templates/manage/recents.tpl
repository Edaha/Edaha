{% import "manage/macros.tpl" as macros %}

{% macro create_point_link(post_board, post_thread_id, post_id, locale ='en') %}
  {% if post_thread_id == 0 %}
    {% set post_thread_id = post_id %}
  {% endif %}
  {% set point_href = "%s/%s/res/%s.html#%s"|format(
    kxEnv('kx:paths:boards:folder'), 
    post_board, 
    post_thread_id, 
    post_id
  )  %}
  {% set point_text = ">>>%s/%s"|format(post_board, post_id) %}
  <a href="{{ point_href }}">{{ point_text }}</a>
{% endmacro %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Recent Posts & Images" %}{% endblock %}

{% block managecontent %}
  <form action="{{ base_url }}app=board&amp;module=contentmoderation&amp;section={{ section }}&amp;do=process" method="post">
    {{ 
    macros.manageform("actions", "Review Actions", true,
      { 
        'Review Action':
        {
          'id': 'action',
          'type': 'select',
          'value': {
            'Approve': { value: 'approve' }, 
            'Delete':  { value: 'delete' }, 
          },
        },
        'Delete all posts from users' : 
        { 
          'id' : 'delete_posts',
          'type' : 'checkbox_single', 
          'desc' : "If checked, will delete all posts from the users who created the post (if Review Action is Delete)",
          'selected' : false,
        },
        'Ban users' : 
        { 
          'id' : 'ban_users',
          'type' : 'checkbox_single', 
          'desc' : "If checked, will ban all users who created the posts (if Review Action is Delete)",
          'selected' : false,
        },
      }
    ) 
  }}
    <fieldset id="posts">
      <legend>{% trans "Recent Posts & Images" %}</legend>
      <hr>
      {% if section == "posts" %}
        {% for post in recent_posts %}
          <div>
            <input type="checkbox" name="posts[]" value="{{ "%s|%s"|format(post.board_id, post.post_id) }}">
            {{ _self.create_point_link(post.board_name, post.parent_id, post.post_id) }}
            {{ post.post_message|striptags|raw }}
          </div>
          <hr>
        {% endfor %}
      {% elseif section == "images" %}
        {% for file in recent_images %}
          {% set thumburl = "%s/%s/thumb/%ss.%s" | format(
            kxEnv("paths:boards:path"),
            file.board_name,
            file.file_name,
            file.file_type,
          ) %}
          <div>
            <input type="checkbox" name="files[]" value="{{ "%s|%s|%s"|format(file.board_id, file.file_post, file.file_name) }}">
            {{ _self.create_point_link(file.board_name, file.post_parent, file.file_post) }}
            <img src="{{ thumburl }}">
          </div>
          <hr>
        {% endfor %}
      {% elseif section == "reports" %}
        {% for report in reports %}
          <div>
            <input type="checkbox" name="reports[]" value="{{ report.id }}">
            Reported by {{ report.ip }} at {{ report.timestamp|date }}, reason: {{ report.reason }}
            <br>
            {{ _self.create_point_link(report.post.board_name, report.post.post_parent, report.post.post_id) }}
            {{ report.post.post_message|striptags|raw }}
            {% for file in report.post.post_files %}
              {% set thumburl = "%s/%s/thumb/%ss.%s" | format(
                kxEnv("paths:boards:path"),
                report.post.board_name,
                file.file_name,
                file.file_type,
              ) %}
              <img src="{{ thumburl }}">
            {% endfor %}
          </div>
          <hr>
        {% endfor %}
      {% endif %}
      
    </fieldset>
  </form>
  
{% endblock %}