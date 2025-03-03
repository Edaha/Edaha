{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Filetype Management" %}{% endblock %}

{% block managecontent %}

<form action="{{ base_url }}app=board&amp;module=attachments&amp;section=filetypes&amp;do={% if _get.do != 'edit' %}add{% else %}edit-post&amp;id={{ filetype.type_id }}{% endif %}" method="post">
  {{ macros.manageform("filetype", "Add Filetype", true,
                        { 'File Extension' : { 'id' : 'ext', 'type' : 'text', 'desc' : "The extension of the file.",  'value' : filetype.type_ext } ,
                          'MIME Type' : { 'id' : 'mime', 'type' : 'text', 'desc' : "The MIME type of the uploaded file.", 'value' : filetype.type_mime } ,
                          'Image' : { 'id' : 'image', 'type' : 'text', 'desc' : "The image to be displayed. Needs to be placed in /public/images/",  'value' : filetype.type_image } ,
                          'Image Width' : { 'id' : 'image_width', 'type' : 'text', 'desc' : "The width of the above image.", 'value' : filetype.type_image_width } ,
                          'Image Height' : { 'id' : 'image_height', 'type' : 'text', 'desc' : "The height of the above image.", 'value' : filetype.type_image_height } 
                        }
                      ) 
  }}
</form>


<table class="stats">
  <tr>
    <th>{% trans "Image" %}</th>
    <th>{% trans "File Extension" %}</th>
    <th>{% trans "MIME Type" %}</th>
    <th>{% trans "Actions" %}</th>
  </tr>
{% for type in filetypes %}
  <tr>
    <td>{{ type.type_image }}</td>
    <td>{{ type.type_ext }}</td>
    <td>{{ type.type_mime }}</td>
    <td><a href="{{ base_url }}app=board&amp;module=attachments&amp;section=filetypes&amp;do=edit&amp;id={{ type.type_id }}"><img src="{% kxEnv "paths:boards:path" %}/public/manage/edit.png" width="16" height="16" alt="Edit" /></a>&nbsp;<a href="{{ base_url }}app=board&amp;module=attachments&amp;section=filetypes&amp;do=del&amp;id={{ type.type_id }}"><img src="{% kxEnv "paths:boards:path" %}/public/manage/delete.png" width="16" height="16" alt="Delete" /></a></td>
  </tr>
{% endfor %}
</table>

{% endblock %}