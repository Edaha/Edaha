{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Board Options" %}{% endblock %}

{% block managecontent %}

<form action="{{ base_url }}app=board&amp;module=board&amp;section=boardopts&amp;do=edit&amp;id={{ id }}&amp;action=post" method="post">
  {{ macros.manageform("boards", "Edit Board", true,
                       { 'Board Name' : { 'id' : 'name', 'type' : 'text', 'desc' : "The directory of the board. <b>Only put in the letter(s) of the board directory, no slashes!</b>
",  'value' : entry.entry_name } ,
                         'Board Description'    : { 'id' : 'description', 'type' : 'text', 'desc' : "The name of the board", 'value' : entry.entry_description } ,
                         'First Post ID'  : { 'id' : 'start', 'type' : 'text', 'desc' : "The first post of this board will recieve this ID.",  'value' : entry.entry_start } }
                       ) 
  }}
</form>
{% endblock %}
