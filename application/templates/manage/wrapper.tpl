{% extends "global_wrapper.tpl" %}
{% block title %}{% trans "Edaha Management" %}{% endblock %}
{% block css %}
  <link href="{% kxEnv "paths:boards:path" %}/public/css/manage.css" rel="stylesheet" type="text/css" />
  {{ parent() }}
{% endblock %}
{% block content %}
    <div class="header">
      <div class="herp">
        {% trans "Edaha Management" %}
      </div>

       <br style="clear: both;" />
      <div class="login">
        {% trans %}Logged in as {% endtrans %}<span class='strong'>{{name}}</span> [<a href="{{ base_url }}&amp;module=login&amp;do=logout">{%trans "Log Out" %}</a>]
      </div>

      <div class="tabs">
        <ul>
          <li class="{% if not current_app %}selected{% endif %}"><a href="{{ base_url }}">{% trans "Main" %}</a></li>
          <li class="{% if current_app == "core" %}selected{% endif %}"><a href="{{ base_url }}app=core&amp;module=site&section=front&do=news">{% trans "Site Management" %}</a></li>
          <li class="{% if current_app == "board" %}selected{% endif %}"><a href="{{ base_url }}app=board&amp;module=board&section=board">{% trans "Board Management" %}</a></li>
          <li class="{% if current_app == "apps" %}selected{% endif %}"><a href="#">{% trans "Addons" %}</a></li>
        </ul>
      </div>
    </div>
    
    <div class="main">

      <div class="menu">
 				{% include "manage/menu.tpl" %}
      </div>
      
      <div class="content">
        <h1>{% block heading %}{% endblock %}</h1>
        {% if notice_type and notice %}
        <div class="{{notice_type}}">
          {{notice}}
        </div>
        {% endif %}
        
        {% block managecontent %}{% endblock %}
      </div>

      <br style="clear: both;" />
    </div>
{% endblock %}
