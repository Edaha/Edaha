{% extends "global_wrapper.tpl" %}

{% block css %}
  <link rel="stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/menu_global.css" />
  {% for style in styles %}
    <link rel="{% if style != 'css:menudefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/site_{{ style }}.css" title="{{ style|capitalize }}" />
    <link rel="{% if style != 'css:menudefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/sitemenu_{{ style }}.css" title="{{ style|capitalize }}" />
  {% endfor %}

  {% raw %}
    <style type="text/css">
    body {
      width: 100% !important;
    }
    </style>
  {% endraw %}
  {{parent()}}
{% endblock %}

{% block title %}{% kxEnv 'site:name' %}{% endblock %}

{% block content %}
  <h1 style="font-size: 3em;">{% trans "Error" %}</h1>
  <br />
  <h2 style="font-size: 2em;font-weight: bold;text-align: center;">
    {{ errormsg }}
  </h2>
    {{ errormsgext }}
  <div style="text-align: center;width: 100%;position: absolute;bottom: 10px;">
    <br />
    <div class="footer" style="clear: both;">
      {# I'd really appreciate it if you left the link to kusabax.org in the footer, if you decide to modify this. That being said, you are not bound by license or any other terms to keep it there #}
      <div class="legal">	- <a href="http://www.kusabax.org/" target="_top">Edaha 1.0</a> -
    </div>
  </div>
{% endblock %}
