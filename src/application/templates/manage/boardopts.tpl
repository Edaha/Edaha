{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Board Options" %}{% endblock %}

{% block extrahead %}
{% if boardredirect %}
<meta http-equiv="refresh" content="3;{{ base_url }}app=board&amp;module=board&amp;section=board&amp;do=board" />
{% endif %}
{% endblock %}

{% block managecontent %}

{% if _get.do == 'edit' %}
<form action="{{ base_url }}app=board&amp;module=board&amp;section=boardopts&amp;do=post&amp;id={{ board_options.board_id }}" method="post">
  <fieldset id="board_properties">
    <input type="hidden" name="board" value="{{ board_options.board_name }}">
    <legend>{% trans "Board Properties" %}</legend>
    
    <label>{% trans "Directory" %}:</label>
    <span id="dir">/{{ board_options.board_name }}/</span>
    <span class="desc"><a href="#" title="{% trans "The directory of the board. This cannot be changed." %}">?</a></span>
    <br>
    
    <label for="title">{% trans "Title" %}:</label>
    <input type="text" name="title" id="title" value="{{ board_options.board_desc }}">
    <span class="desc"><a href="#" title="{% trans "The title of the board." %}">?</a></span>
    <br>
    
    <label for="locale">{% trans "Locale" %}:</label>
    <input type="text" name="locale" id="locale" value="{{ board_options.board_locale }}">
    <span class="desc"><a href="#" title="{% trans "Locale to use on this board. If left blank, the default locale from configuration will be used." %}">?</a></span>
    <br>
  </fieldset>
  
  <fieldset id="display_properties">
    <legend>{% trans "Display Properties" %}</legend>
    
    <label for="type">{% trans "Board Type" %}:</label>
    <select name="type" id="type">
      {% for type in board_types %}
      <option value="{{ type.module_file }}"{% if board_options.board_type == type.module_file %} selected="selected"{% endif %}>{{ type.module_name }}</option>
     {% endfor %}
    </select>
    <span class="desc"><a href="#" title="{% trans "The type of imageboard desired. A Normal Imageboad will feature image and extended-format posts. An Oekaki Imageboard will allow users to draw their own images for their posts. An Upload Imageboard will be styled more towards file uploads. A Text Board will allow only text posts." %}">?</a></span>
    <br>
    
    <label for="uploadtype">{% trans "Embedding Type" %}</label>
    <select name="upload type" id="uploadtype">
      <option value="0"{% if board_options.board_upload_type == 0 %} selected="selected"{% endif %}>{% trans "No Embedding" %}</option>
      <option value="1"{% if board_options.board_upload_type == 1 %} selected="selected"{% endif %}>{% trans "Files and Embedding" %}</option>
      <option value="2"{% if board_options.board_upload_type == 2 %} selected="selected"{% endif %}>{% trans "Embedding Only" %}</option>
    </select>
    <span class="desc"><a href="#" title="{% trans "Whether or not to allow embedding of videos." %}">?</a></span>
    <br>
    
    {{ macros.sectionlist(sections, 'board_section', board_options.board_section) }}
    <span class="desc"><a href="#" title="{% trans "The section the board is in. This is used for displaying the list of boards on the top and bottom of pages." %}">?</a></span>
    <br>
    
    <label for="order">{% trans "Order" %}:</label>
    <input type="text" name="order" id="order" value="{{ board_options.board_order }}">
    <span class="desc"><a href="#" title="{% trans "Position at which the board is to be shown in the menu." %}">?</a></span>
    <br>
    
    <label for="header_image">{% trans "Header Image" %}:</label>
    <input type="text" name="header_image" id="header_image" value = "{{ board_options.board_header_image }}">
    <span class="desc"><a href="#" title="{% trans "Overrides the header set in the config file. Leave blank to use configured global header image." %}">?</a></span>
    <br>
    
    <label for="include_header">{% trans "Include Header" %}:</label>
    <textarea name="include_header" id="include_header" rows="12" cols="80">{{ board_options.board_include_header }}</textarea>
    <span class="desc"><a href="#" title="{% trans "Raw HTML to be included at the top of the board pages." %}">?</a></span>
    <br>
    
    <label for="anonymous">{% trans "Anonymous Poster Name" %}:</label>
    <input type="text" name="anonymous" id="anonymous" value = "{{ board_options.board_anonymous }}">
    <span class="desc"><a href="#" title="{% trans "Name to display when the user does not supply one." %}">?</a></span>
    <br>
    
    <!-- Default Style -->
    
  </fieldset>
  
  <fieldset id="filetypes">
    <legend>{% trans "Filetypes" %}</legend>
    
    <!-- Regular Filetypes -->
    {% for type in filetypes %}
    <label>{{ type.type_ext }}</label>
    <input type="checkbox" name="filetypes[]" value="{{ type.type_id }}"{% if type.type_id in board_options.board_filetypes %} checked="checked"{% endif %}><br>
    {% endfor %}
    
    <!-- Embeds -->
    
  </fieldset>
  
  <fieldset id="limits">
    <legend>{% trans "Limits" %}</legend>
    
    <label for="max_upload_size">{% trans "Maximum Upload Size" %}:</label>
    <input type="text" name="max_upload_size" id="max_upload_size" value="{{ board_options.board_max_upload_size }}">
    <span class="desc"><a href="#" title="{% trans "Maximum size of uploaded files (in kilobytes)." %}">?</a></span>
    <br>
    
    <label for="max_message_length">{% trans "Maximum Message Length" %}:</label>
    <input type="text" name="max_message_length" id="max_message_length" value="{{ board_options.board_max_message_length }}">
    <span class="desc"></span>
    <br>
    
    <label for="max_pages">{% trans "Maximum Board Pages" %}:</label>
    <input type="text" name="max_pages" id="max_pages" value="{{ board_options.board_max_pages }}">
    <span class="desc"></span>
    <br>
    
    <label for="max_age">{% trans "Maximum Thread Age" %}:</label>
    <input type="text" name="max_age" id="max_age" value="{{ board_options.board_max_age }}">
    <span class="desc"></span>
    <br>
    
    <label for="mark_page">{% trans "Mark Page" %}:</label>
    <input type="text" name="mark_page" id="mark_page" value="{{ board_options.board_mark_page }}">
    <span class="desc"><a href="#" title="{% trans "Threads reaching this page will be marked to be deleted in two hours." %}">?</a></span>
    <br>
    
    <label for="max_replies">{% trans "Maximum Thread Replies" %}:</label>
    <input type="text" name="max_replies" id="max_replies" value="{{ board_options.board_max_replies }}">
    <span class="desc"><a href="#" title="{% trans "The number of replies a thread can have before automatically saging to the back of the board." %}">?</a></span>
    <br>
    
    <label for="max_files">{% trans "Maximum File Attachments" %}:</label>
    <input type="text" name="max_files" id="max_files" value="{{ board_options.board_max_files }}">
    <span class="desc"><a href="#" title="{% trans "The number of replies a thread can have before automatically saging to the back of the board." %}">?</a></span>
    <br>
    
  </fieldset>

  <fieldset id="general_options">
    <legend>{% trans "General Options" %}</legend>
    
    <label for="locked">{% trans "Locked" %}:</label>
    <input type="checkbox" name="locked" id="locked"{% if board_options.board_locked %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, only administrators and mods will be able to post on this board." %}">?</a></span>
    <br>
    
    <label for="show_id">{% trans "Show ID" %}:</label>
    <input type="checkbox" name="show_id" id="show_id"{% if board_options.board_show_id %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, each post will include a unique ID for that poster." %}">?</a></span>
    <br>
    
    <label for="compact_list">{% trans "Compact List" %}:</label>
    <input type="checkbox" name="compact_list" id="compact_list"{% if board_options.board_compact_list %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, the list of threads at the top of the page will be formatted differently (Textboard Only)." %}">?</a></span>
    <br>
    
    <label for="reporting">{% trans "Enable Reporting" %}:</label>
    <input type="checkbox" name="reporting" id="reporting"{% if board_options.board_reporting %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, users will be able to report posts on this board." %}">?</a></span>
    <br>
    
    <label for="captcha">{% trans "Enable ReCaptcha" %}:</label>
    <input type="checkbox" name="captcha" id="captcha"{% if board_options.board_captcha %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, ReCaptcha will be enabled on this board." %}">?</a></span>
    <br>
    
    <label for="archiving">{% trans "Enable Archiving" %}:</label>
    <input type="checkbox" name="archiving" id="archiving"{% if board_options.board_archiving %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, a deleted thread will be moved into the /arch/ directory within the board directory. See the Edaha Wiki for instructions in setting this up" %}">?</a></span>
    <br>
    
    <label for="catalog">{% trans "Enable Catalog" %}:</label>
    <input type="checkbox" name="catalog" id="catalog"{% if board_options.board_catalog %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, a catalog.html file will be built along with the board (Imageboard and variants only)." %}">?</a></span>
    <br>
    
    <label for="no_file">{% trans "No-file Posting" %}:</label>
    <input type="checkbox" name="no_file" id="no_file"{% if board_options.board_no_file %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, threads will be able to be posted without an initial file." %}">?</a></span>
    <br>
    
    <label for="redirect_to_thread">{% trans "Redirect to Thread" %}:</label>
    <input type="checkbox" name="redirect_to_thread" id="redirect_to_thread"{% if board_options.board_redirect_to_thread %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, users will be redirected to their thread after posting." %}">?</a></span>
    <br>
    
    <label for="forced_anon">{% trans "Forced Anonymous" %}:</label>
    <input type="checkbox" name="forced_anon" id="forced_anon"{% if board_options.board_forced_anon %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, users will be prevented from posting with a name." %}">?</a></span>
    <br>
    
    <label for="trial">{% trans "Trial" %}:</label>
    <input type="checkbox" name="trial" id="trial"{% if board_options.board_trial %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, this board will appear in italics in the menu." %}">?</a></span>
    <br>
    
    <label for="popular">{% trans "Popular" %}:</label>
    <input type="checkbox" name="popular" id="popular"{% if board_options.board_popular %} checked="checked"{% endif %}>
    <span class="desc"><a href="#" title="{% trans "If checked, this board will appear in bold in the menu." %}">?</a></span>
    <br>

  </fieldset>
  <input type="submit" value="Submit Changes">
  <input type="checkbox" name="rebuild_board">
  <span class="desc">{% trans "Rebuild board after submitting" %}</span>
</form>
{% endif %}
{% endblock %}
