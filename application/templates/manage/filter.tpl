{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Filters" %}{% endblock %}

{% block managecontent %}
  <form action="{{ base_url }}app=board&amp;module=filter&amp;section=filter&amp;do={% if _get.do == 'edit' %}edit-post&amp;id={{ edit_filter.filter_id }}{% else %}add{% endif %}" method="post">
    <fieldset id="bans">
      <legend>{% trans "Filters" %}</legend>
      
      <div class="bans_left">
        {{ macros.boardlist(sections, 'filter_boards', edit_filter.filter_boards) }}
      </div>
      
      <div class="bans_right">
        <!-- IP, Bantype, DeleteAll/AllowRead -->
        <label for="filter_word">{% trans "Word" %}:</label>
        <input type="text" name="filter_word" id="filter_word" value="{{ edit_filter.filter_word }}" /><br />
        
        <label for="filter_replace">{% trans "Replace With" %}:</label> <input type="text" name="filter_replacement" id="filter_replacement" value="{{ edit_filter.filter_replacement }}" />
        <span class="desc"><a href="#" title="{% trans "Only valid if \'Replace word\' is checked as a punishment" %}">?</a></span><br />
        
        <label for="filter_ban_duration">{% trans "Ban Duration" %}:</label> <input type="text" name="filter_ban_duration" id="filter_ban_duration" value="{{ edit_filter.filter_ban_duration }}" />
        <span class="desc"><a href="#" title="{% trans "Only valid if \'Ban user\' is checked as a punishment." %} {% trans "Enter number of seconds or values like \'1 year\' or \'Forever\'" %}">?</a></span><br />
        
        <label for="filter_regex">{% trans "Regular expression" %}:</label>
        <input type="checkbox" name="filter_regex" id="filter_regex"{% if edit_filter.filter_regex %} checked="checked"{% endif %} />
        <span class="desc"><a href="#" title="{% trans "Surround your filter with delimiters if you check this" %}">?</a></span><br />
        
        <label>{% trans "Replace" %}: <input type="checkbox" name="filter_actions[]" value="1"{% if edit_filter.filter_type b-and 1 %} checked="checked"{% endif %}></label>
        <span class="desc"><a href="#" title="{% trans "Replaces the word" %}">?</a></span>
        
        <label>{% trans "Report" %}: <input type="checkbox" name="filter_actions[]" value="2"{% if edit_filter.filter_type b-and 2 %} checked="checked"{% endif %}></label>
        <span class="desc"><a href="#" title="{% trans "Automatically reports the post for later review" %}">?</a></span><br />
        
        <label>{% trans "Delete" %}: <input type="checkbox" name="filter_actions[]" value="4"{% if edit_filter.filter_type b-and 4 %} checked="checked"{% endif %}></label>
        <span class="desc"><a href="#" title="{% trans "Deletes the entire post" %}">?</a></span>
        
        <label>{% trans "Ban" %}: <input type="checkbox" name="filter_actions[]" value="8"{% if edit_filter.filter_type b-and 8 %} checked="checked"{% endif %}></label>
        <span class="desc"><a href="#" title="{% trans "Automatically bans the poster" %}">?</a></span><br />
        
        <label for="ban_submit">&nbsp;</label> <input type="submit" name="ban_submit" id="ban_submit" />
      </div>
    </fieldset>
  </form>
  
  <table id="filters">
    <tr>
      <th>{% trans "Word" %}</th>
      <th>{% trans "Actions" %}</th>
      <th>{% trans "Text Replacement" %}</th>
      <th>{% trans "Ban Duration" %}</th>
      <th>{% trans "Active On" %}<!-- Apply directly where it hurts --></th>
      <th>{% trans "Date Added" %}</th>
      <th>&nbsp;</th>
    </tr>
    {% for filter in filters %}
    <tr>
      <td>{{ filter.filter_word }}</td>
      <td>{% if filter.filter_type b-and 1 %}{% trans "Replace," %} {% endif %}{% if filter.filter_type b-and 2 %}{% trans "Report," %} {% endif %}{% if filter.filter_type b-and 4 %}{% trans "Delete," %} {% endif %}{% if filter.filter_type b-and 8 %}{% trans "Ban" %}{% endif %}</td>
      <td>{% if filter.filter_replacement %}{{ filter.filter_replacement }}{% else %}N/A{% endif %}</td>
      <td>{% if filter.filter_ban_duration is not null %}{% if filter.filter_ban_duration == 0 %}{% trans "Forever" %}{% else %}{{ filter.filter_ban_duration }}{% endif %}{% else %}N/A{% endif %}</td>
      <td>
      {% for board in filter.boards %}
        /{{ board }}/, 
      {% else %}
        {% trans "No boards" %}
      {% endfor %}
      </td>
      <td>{{ filter.filter_added }}</td>
      <td>
        <a href="{{ base_url }}app=board&amp;module=filter&amp;section=filter&amp;do=edit&amp;id={{ filter.filter_id }}">
          <img src="{% kxEnv "paths:boards:path" %}/public/manage/edit.png" width="16" height="16" alt="Edit" />
        </a>
        <a href="{{ base_url }}app=board&amp;module=filter&amp;section=filter&amp;do=del&amp;id={{ filter.filter_id }}">
          <img src="{% kxEnv "paths:boards:path" %}/public/manage/delete.png" width="16" height="16" alt="Delete" />
        </a>
      </td>
    </tr>
    {% else %}
    <tr>
      <td colspan="6">{% trans "No filters added" %}</td>
    </tr>
    {% endfor %}
  </table>
{% endblock %}