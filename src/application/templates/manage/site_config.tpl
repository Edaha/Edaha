{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Edit Settings" %}{% endblock %}

{% block managecontent %}
    <ul id="config_tabs">
    {% for group in config_groups %}
      <li><a href="#{{ group.group_short_name }}">{{ group.group_name }}</a></li>
    {% endfor %}
    </ul>

    <form action="{{ base_url }}app=core&amp;module=site&amp;section=config&amp;do=save" method="post">
    {% for group in config_groups %}
      <fieldset id="{{ group.group_short_name }}">
        <legend>{{ group.group_name }}</legend>
        
        {% for option in group.options %}
        {% set config_name = option.config_name %}
        <label for="{{option.config_variable}}">{% trans config_name %}</label>
        {% if option.config_type == "input" or option.config_type == "numeric" %}
        <input type="text" name="config[{{option.config_variable}}]" id="{{option.config_variable}}" value="{{kxEnv(option.config_variable)|e}}" />
        {% elseif option.config_type == "text" %}
        <input type="text" name="config[{{option.config_variable}}]" id="{{option.config_variable}}" value="{{kxEnv(option.config_variable)|e}}" />
        {% elseif option.config_type == "true_false" %}
        <input type="radio" name="config[{{option.config_variable}}]" value="1"{% if kxEnv(option.config_variable) == true %} checked="checked"{%endif %} /> {% trans "Enabled" %}
        <input type="radio" name="config[{{option.config_variable}}]" value="0"{% if kxEnv(option.config_variable) == false %} checked="checked"{%endif %} /> {% trans "Disabled" %}
        {#<select name="config[{{option.config_variable}}]" id="{{option.config_variable}}">
          <option value="true" {% if kxEnv(option.config_variable) == true %} selected="selected"{%endif %} >
            {% trans "True" %}
          </option>
          <option value="false" {% if kxEnv(option.config_variable) == false %} selected="selected"{%endif %} >
            {% trans "False" %}
          </option>
        </select>#}
        {% elseif option.config_type == "textarea" %}
        <textarea name="config[{{option.config_variable}}]" id="{{option.config_variable}}" rows="3" cols="40">{{kxEnv(option.config_variable)|e}}</textarea>
        {% endif %}
        {% set config_desc = option.config_description %}
        <span class="desc"><a href="#" title="{% trans config_desc %}">?</a></span><br />
        {% endfor %}
        <label></label><input type="submit" value="Update" />
      </fieldset>
    {% endfor %}
    </form>
{% endblock %}