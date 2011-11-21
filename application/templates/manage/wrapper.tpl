{% extends "global_wrapper.tpl" %}
{% block title %}{% trans "Edaha Management" %}{% endblock %}
{% block css %}
  <link href="{% kxEnv "paths:boards:path" %}/public/css/manage.css" rel="stylesheet" type="text/css" />
  {{ parent() }}
{% endblock %}
{% block content %}
    <section class="content_wrap">
      <header id="">
        <section id="top">
          {% trans "Edaha Management" %}
        </section>
        
        <div class="login">
          {% trans %}Logged in as {% endtrans %}<span class='strong'>{{name}}</span> [<a href="{{base_url}}&amp;module=login&amp;do=logout">{%trans "Log Out" %}</a>]
        </div>
        
        <nav>
          <ul>
            <li><a href="{{base_url}}">{% trans "Main" %}</a></li>
            <li class="{% if current_app == "core" %}selected{% endif %}"><a href="{{base_url}}app=core&amp;module=site&section=front&do=news">{% trans "Site" %}</a></li>
            <li class="{% if current_app == "board" %}selected{% endif %}"><a href="{{base_url}}app=board&amp;module=board&section=board">{% trans "Board" %}</a></li>
            <li class="{% if current_app == "apps" %}selected{% endif %}"><a href="#">{% trans "Addons" %}</a></li>
          </ul>
        </nav>
      </header>
    
      <section class="content">
        <section class="sidebar">
          {% include "manage/menu.tpl" %}
        </section>
      
        <section class="col_r">
          <h1>{% block heading %}{% endblock %}</h1>
          
          {% block managecontent %}{% endblock %}
        </section>
      </section>
      <footer>
        
      </footer>
    </section>
{% endblock %}
