{% macro manageform(id, name, submit, entries) %}
    <fieldset id="{{id}}">
      <legend>{% trans name %}</legend>
      {% for name, entry in entries %}
        <label for="{{entry.id}}">{% trans name %}:</label>
        {% if entry.type == "text"  or entry.type == "password" %}
          <input type="{{entry.type}}" id="{{entry.id}}" name="{{entry.id}}" value="{{entry.value}}" />
        {% elseif entry.type == "textarea" %}
          <textarea id="{{entry.id}}" name="{{entry.id}}" rows="25" cols="80">{{entry.value}}</textarea><br /><br />
        {% endif %}
        {% if entry.desc %}
          {% set entrydesc = entry.desc %}
          <div class="desc">{% trans entrydesc %}</div><br />
        {% endif %}
      {% endfor %}
      {% if submit %}
        <label for="submit">&nbsp;</label>
        <input type="submit" id="submit" value="{% trans "Submit" %}" />
      {% endif %}
    </fieldset>
{% endmacro %}