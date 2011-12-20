{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Edit Settings" %}{% endblock %}

{% block managecontent %}
    <form action="{{ base_url }}app=core&amp;module=site&amp;section=config&amp;do=save" method="post">
    {% for option in options %}    
      {% if option.config_variable|kxEnv != NULL %}
        {% if option.config_type != prevcat %}
        {% endif %}
        {% set config_name = option.config_name %}
        <label for="{{option.config_variable}}">{% trans config_name %}</label>
        {% if option.config_type == "input" %}
          <input type="text" name="{{option.config_variable}}" id="{{option.config_variable}}" value="{{option.config_variable|kxEnv|e}}" />
        {% elseif option.config_type == "text" %}
          <input type="text" name="{{option.config_variable}}" id="{{option.config_variable}}" value="{{option.config_variable|kxEnv}}" />
        {% elseif option.config_type == "true_false" %}
          <select name="{{option.config_variable}}" id="{{option.config_variable}}">
            <option value="1" {% if option.config_variable|kxEnv == true %} selected="selected"{%endif %} >
              {% trans "True" %}
            </option>
            <option value="0" {% if option.config_variable|kxEnv == false %} selected="selected"{%endif %} >
              {% trans "False" %}
            </option>
        {% elseif option.config_type == "textarea" %}
          <textarea name="{{option.config_variable}}" id="{{option.config_variable}}" rows="3" cols="40">{{option.config_variable|kxEnv|e}}</textarea>
        {% endif %}
      {% set config_desc = option.config_description %}
      <br /><span class="desc">{% trans config_desc %}</span><br />
      {% endif %}
    {% endfor %}
    <input type="submit" name="submit" value="Update" />
    </form>
{% endblock %}