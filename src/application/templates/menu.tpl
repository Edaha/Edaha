{% extends "global_wrapper.tpl" %}

{% block title %}{{ kxEnv("site:name") }} Navigation{% endblock %}

{% block css %}
  <link rel="stylesheet" type="text/css" href="{{ kxEnv("paths:main:path") }}/public/css/menu_global.css" />
  {% for style in styles %}
    <link rel="{% if style != kxEnv("css:sitedefault") %}alternate {% endif %}stylesheet" type="text/css" href="{{ kxEnv("paths:main:path") }}/public/css/site_{{ style }}.css" title="{{ style|capitalize }}" />
    <link rel="{% if style != kxEnv("css:sitedefault") %}alternate {% endif %}stylesheet" type="text/css" href="{{ kxEnv("paths:main:path") }}/public/css/sitemenu_{{ style }}.css" title="{{ style|capitalize }}" />
  {% endfor %}

  {% raw %}
    <style type="text/css">
    body {
      width: 100% !important;
    }
    </style>
  {% endraw %}
  {{parent()}}

  <base target="main" />
{% endblock %}

{% block content %}
  <h1>{{ kxEnv("site:name") }}</h1>
  <ul>
  <li><a href="{{ kxEnv("paths:main:path") }}" target="_top">{% trans "Front Page" %}</a></li>
  {% if kxEnv("css:menuswitcher") %} 
    <li id="sitestyles"><a onclick="javascript:showstyleswitcher();" href="#" target="_self">[{t "Site Styles"}]</a></li>
  {% endif %}

  </ul>
  {% for sect in boards %}
    <h2>
    <span class="plus" onclick="toggle(this, '{{sect.section_abbrev}}');" title="{% trans "Click to show/hide" %}">{% if sect.section_hidden == 1 %}+{% else %}&minus;{% endif %}</span>&nbsp;
    {{sect.name}}</h2>
    <div id="{{sect.section_abbrev}}"{% if sect.hidden == 1 %} style="display: none;"{% endif %}>
    <ul>
      {% for brd in sect.boards %}
        <li><a href="{{ kxEnv("paths:boards:path") }}/{{brd.board_name}}/" class="boardlink{% if brd.board_trial == 1 %} trial{% endif %}{% if brd.board_popular == 1 %} pop{% endif %}">
        /{{brd.board_name}}/ - 
        {{brd.board_desc}}
        {% if brd.board_locked == 1 %}
          <img src="{kxEnv "paths:main:path"}/public/locked.gif" border="0" alt="{t "Locked"}">
        {% endif %}
        </a></li>
    {% else %}
      <li>{% trans "No visible boards" %}</li>
    {% endfor %}
    </ul>
    </div>
  {% else %}
    <ul>
      <li>{% trans "No visible boards" %}</li>
    </ul>
  {% endfor %}
  {% if kxEnv("site:irc") %}
      <h2>
      &nbsp;IRC</h2>
    <ul>
      <li>{kxEnv "site:irc"}</li>
    </ul>
  {% endif %}
{% endblock %}
