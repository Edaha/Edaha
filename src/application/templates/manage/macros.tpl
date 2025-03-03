{% macro manageform(id, name, submit, entries) %}
    <fieldset id="{{id}}">
      <legend>{% trans name %}</legend>
      {% for name, entry in entries %}
        <label for="{{entry.id}}">{% trans name %}:</label>
        {% if entry.type == "text"  or entry.type == "password" %}
          <input type="{{entry.type}}" id="{{entry.id}}" name="{{entry.id}}" value="{{entry.value}}"{% if entry.disabled %} disabled="disabled"{% endif %}/>
        {% elseif entry.type == "textarea" %}
          <textarea id="{{entry.id}}" name="{{entry.id}}" rows="25" cols="65">{{entry.value}}</textarea>
        {% elseif entry.type == "select" %}
          <select id="{{entry.id}}" name="{{entry.id}}">
            {% for key, option in entry.value %}
              <option value="{{option.value}}" {% if option.selected %}selected=selected{% endif %}>{% trans key %}</option>
            {% endfor %}
          </select>
        {% endif %}
        {% if entry.desc %}
          {% set entrydesc = entry.desc %}
          <span class="desc"><a href="#" title="{% trans entrydesc %}">?</a></span><br />
        {% endif %}
        <br />
      {% endfor %}
      {% if submit %}
        <label for="submit">&nbsp;</label>
        <input type="submit" id="submit" value="{% trans "Submit" %}" />
      {% endif %}
    </fieldset>
{% endmacro %}

{% macro boardlist(sections, name, selected) %}
  <select name="{{ name }}[]" class="multiple" multiple="multiple">
  {% for section in sections %}
    {% if section.section_name is defined %}
      <optgroup label="{{ section.section_name }}">
        {% for board in section.boards %}
          <option value="{{ board.board_id }}"{% if board.board_id in selected %} selected="selected"{% endif %}>{{ board.board_desc }}</option>
        {% endfor %}
      </optgroup>
    {% else %}
      <option value="{{ section.board_id }}">{{ section.board_desc }}</option>
    {% endif %}
  {% endfor %}
  </select>
{% endmacro %}

{% macro sectionlist(sections, id, selected) %}
  <label for="{{ id }}">{% trans "Section" %}:</label>
  <select name="{{ id }}" id="{{ id }}">
    <option value="">{% trans "Hidden" %}</option>
  {% for section in sections %}
    <option value="{{ section.id }}"{% if selected == section.id %} selected="selected"{% endif %}>{{ section.section_name }}</option>
  {% endfor %}
  </select>
{% endmacro %}
