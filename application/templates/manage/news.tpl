{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "News Management" %}{% endblock %}

{% block managecontent %}

<form action="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news&amp;action=post" method="post">
  {{ macros.manageform("news_post", "Post news", true,
                       { 'Subject' : { 'id' : 'subject', 'type' : 'text', 'desc' : "Can not be left blank",  'value' : entry.entry_subject } ,
                         'Post'    : { 'id' : 'message', 'type' : 'textarea', 'value' : entry.entry_message } ,
                         'E-Mail'  : { 'id' : 'email', 'type' : 'text', 'desc' : "Can be left blank",  'value' : entry.entry_email } }
                       ) 
  }}
<input type="hidden" id="edit" name="edit" value="{{ entry.entry_id }}" />
<input type="hidden" id="type" name="type" value="0" />
</form>
  
<br />  
<table class="users" cellspacing="1px">
  <col class="col1" /> <col class="col2" />
  <col class="col1" /> <col class="col2" />
  <thead>
    <tr>
      <th>{% trans "Date Added" %}</th>
      <th>{% trans "Subject" %}</th>
      <th>{% trans "Message" %}</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
  {% for news in entries %}
    <tr>
      <td>{{ news.entry_time|date_format("%b %d, %Y %H:%M") }}</td>
      <td>{{ news.entry_subject }}</td>
      <td>{{ news.entry_message }}</td>
      <td>[ <a href="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news&amp;action=edit&amp;id={{ news.entry_id }}">{% trans "Edit" %}</a> ] [ <a href="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news&amp;action=del&amp;id={{ news.entry_id }}">{% trans "Delete" %}</a> ]</td>
    </tr>
  {% endfor %}
  </tbody>
</table>
{% endblock %}