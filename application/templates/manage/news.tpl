{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "News Management" %}{% endblock %}

{% block managecontent %}

<form action="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news-post" method="post">
  {{ macros.manageform("news_post", "Post news", true,
                       { 'Subject' : { 'id' : 'subject', 'type' : 'text', 'desc' : "Can not be left blank",  'value' : news.entry_subject } ,
                         'Post'    : { 'id' : 'news', 'type' : 'textarea', 'value' : news.entry_message } ,
                         'E-Mail'  : { 'id' : 'email', 'type' : 'text', 'desc' : "Can be left blank",  'value' : news.entry_email } }
                       ) 
  }}
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
  {% for entry in news %}
    <tr>
      <td>{{ news.entry_time|date_format("%b %d, %Y %H:%M") }}</td>
      <td>{{ news.entry_subject }}</td>
      <td>{{ news.entry_message }}</td>
      <td>[ <a href="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news-edit&amp;id={{ news.entry_id }}">{% trans "Edit" %}</a> ] [ <a href="{{ base_url }}app=core&amp;module=site&amp;section=front&amp;do=news-del&amp;id={{ news.entry_id }}">{% trans "Delete" %}</a> ]</td>
    </tr>
  {% endfor %}
  </tbody>
</table>
{% endblock %}