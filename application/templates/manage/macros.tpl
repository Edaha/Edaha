{% macro manageform(id, name, submit, entries) %}
    <fieldset id="{{id}}">
      <legend>{% trans name %}</legend>
      {% for name, entry in entries %}
        <label for="{{entry.id}}">{% trans name %}:</label>
        {% if entry.type == "text"  or entry.type == "password" %}
          <input type="{{entry.type}}" id="{{entry.id}}" name="{{entry.id}}" value="{{entry.value}}" />
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
          <span class="desc">{% trans entrydesc %}</span><br />
        {% endif %}
        <br />
      {% endfor %}
      {% if submit %}
        <label for="submit">&nbsp;</label>
        <input type="submit" id="submit" value="{% trans "Submit" %}" />
      {% endif %}
    </fieldset>
{% endmacro %}