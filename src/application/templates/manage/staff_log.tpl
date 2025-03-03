{% extends "manage/wrapper.tpl" %}

{% block heading %}{%trans "Moderator Log" %}{% endblock %}

{% block managecontent %}

<table class="log">
  <caption>{% trans "Recent moderator actions" %}{% if _get.view %} <a href="{{ base_url }}app=core&amp;module=staff&amp;section=staff&amp;do=log">[Return]</a>{%endif%}</caption>
  <col class="col1" /><col class="col2" /><col class="col3" />
  <thead>
    <tr>
      <th>{% trans "User" %}</th>
      <th>{% trans "Time" %}</th>
      <th>{% trans "Action" %}</th>
    </tr>
  </thead>
  <tbody>
    {% for action in modlog %}
    <tr>
      <td>{{ action.user }}</td>
      <td>{{action.timestamp|date_format("%b %d, %Y %H:%M") }}</td>
      <td>{{action.entry}}</td>
    </tr>
    {% endfor %}
  </tbody>
</table>
<br />

{% if not _get.view %}
<table class="log">
  <col class="col1" /><col class="col2" /><col class="col3" />
  <thead>
    <tr>
      <th>{% trans "User" %}</th>
      <th>{% trans "Actions Performed" %}</th>
      <th>{% trans "View all" %}</th>
    </tr>
  </thead>
  <tbody>
    {% for user in staff %}
    <tr>
      <td>{{ user.user_name }}</td>
      <td>{{ user.total_actions }}</td>
      <td>
        <a href="{{ base_url }}app=core&amp;module=staff&amp;section=staff&amp;do=log&amp;view={{ user.user_id }}">
          {% trans "View" %}
        </a>
      </td>
    </tr>
    {% endfor %}
  </tbody>
</table>
{% endif %}
{%endblock%}