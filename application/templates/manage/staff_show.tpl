{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Manage Staff" %}{% endblock %}

{% block managecontent %}
<form action="{{ base_url }}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act={% if _get.act == 'edit' %}edit&amp;id={{user.user_id}}{% else %}add{% endif %}" method="post">
  <fieldset id="staff_add">
    <legend>{% if _get.act == 'edit' %}{% trans "Edit user" %}{% else %}{% trans "Add new user" %}{% endif %}</legend>
    
    <label for="username">{% trans "Username" %}:</label>
      <input type="text" id="username" name="username" {% if _get.act == 'edit' %}value="{{user.user_name}}" disabled="disabled"{% endif %} /><br />
    <label for="pwd1">{% trans "Password" %}:</label>
      <input type="password" id="pwd1" name="pwd1" /><br />
    <label for="pwd2">{% trans "Reenter Password" %}:</label>
      <input type="password" id="pwd2" name="pwd2" /><br />
    <label for="type">{% trans "Type" %}:</label>
      <select id="type" name="type">
        <option value="1"{% if _get.act == 'edit' and user.user_type == 1 %} selected="selected"{% endif %}>{% trans "Administrator" %}</option>
        <option value="2"{% if _get.act == 'edit' and user.user_type == 2 %} selected="selected"{% endif %}>{% trans "Moderator" %}</option>
        <option value="0"{% if _get.act == 'edit' and user.user_type == 0 %} selected="selected"{% endif %}>{% trans "Janitor" %}</option>
      </select><br />
    
    <label for="submit">&nbsp;</label>
      <input type="submit" id="submit" value="{% trans "Submit" %}" />
  </fieldset>
</form>
  
<br />  
<table class="users" cellspacing="1px">
  <col class="col1" /> <col class="col2" />
  <col class="col1" /> <col class="col2" />
  <col class="col1" />
  <thead>
    <tr>
      <th>{% trans "Username" %}</th>
      <th>{% trans "Date Added" %}</th>
      <th>{% trans "Last active" %}</th>
      <th>{% trans "Usergroup" %}</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
  {% for member in staffmembers %}
    <tr>
      <td>{{ member.user_name }}</td>
      <td>{{ member.user_add_time|date_format("%b %d, %Y %H:%M") }}</td>
      <td>{{ member.user_last_active|date_format("%b %d, %Y %H:%M") }}</td>
      <td>{{ member.user_type }}</td>
      <td>[ <a href="{{ base_url }}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=edit&amp;id={{ member.user_id }}">{% trans "Edit" %}</a> ] [ <a href="{{ base_url }}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=del&amp;id={{ member.user_id }}">{% trans "Delete" %}</a> ]</td>
    </tr>
  {% endfor %}
  </tbody>
</table>
{% endblock %}