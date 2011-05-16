{% macro manageform(id, name, submit, entries) %}
    <fieldset id="{{id}}">
      <legend>{% trans name %}</legend>
      {% for name, entry in entries %}
        <label for="{{entry.id}}">{% trans name %}:</label>
        {% if entry.type == "text"  or entry.type == "password" %}
          <input type="{{entry.type}}" id="{{entry.id}}" name="{{entry.id}}" value="{{entry.value}}" /><br />
        {% elseif entry.type == "textarea" %}
          <textarea id="{{entry.id}}" name="{{entry.id}}" rows="25" cols="80">{{entry.value}}</textarea><br />
        {% elseif entry.type == "select" %}
          <select id="{{entry.id}}" name="{{entry.id}}">
            {% for key, option in entry.value %}
              <option value="{{option.value}}" {% if option.selected %}selected=selected{% endif %}>{% trans key %}</option>
            {% endfor %}
          </select><br />
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