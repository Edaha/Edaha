{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Board Management" %}{% endblock %}

{% block managecontent %}

<form action="{{ base_url }}app=board&amp;module=board&amp;do=board&amp;action=post" method="post">
  {{ macros.manageform("boards", "Add Board", true,
                       { 'Board Name' : { 'id' : 'name', 'type' : 'text', 'desc' : "The directory of the board. <b>Only put in the letter(s) of the board directory, no slashes!</b>
",  'value' : entry.entry_name } ,
                         'Board Description'    : { 'id' : 'description', 'type' : 'text', 'desc' : "The name of the board", 'value' : entry.entry_description } ,
                         'First Post ID'  : { 'id' : 'start', 'type' : 'text', 'desc' : "The first post of this board will recieve this ID.",  'value' : entry.entry_start } }
                       ) 
  }}
<input type="hidden" id="del" name="del" value="{{ entry.entry_id }}" />
<input type="hidden" name="directory" id="directory" value="" />
</form>

<br />
{{ macros.manageform("boards", "Delete Board", true,
                       { 'Boards'                : { 'id' : 'boards', 'type' : 'select',  'value' : 
                          { 
				
                          } } 
                       }
                      ) 
  }}
{% endblock %}
