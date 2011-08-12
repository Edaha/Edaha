{% extends "global_wrapper.tpl"%}

{% block css %}
{% for style in styles %}
  <link rel="{% if style != 'css:menudefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/site_{{ style }}.css" title="{{ style|capitalize }}" />
{% endfor %}
{% endblock %}

{% block content %}
  <div id="page_wrap">
    <header id="header">
      <hgroup>
        <h1>{% kxEnv "site:name" %}</h1>
        {% if 'site:slogan'|kxEnv != '' %}
        <h4>{% kxEnv "site:slogan" %}</h4>
        {% endif %}
      </hgroup>
    </header>
    
    <nav class="menu" id="topmenu">
      <ul>
  {% strip %}<li class="{% if _get.p == '' %}current {% else %}tab {% endif %}first">{% if _get.p != '' %}<a href="{% kxEnv "paths:main:path" %}/index.php">{% endif %}{% trans "News" %}{% if _get.p != '' %}</a>{% endif %}</li>{% endstrip %}
        {% strip %}<li class="{% if _get.p == 'faq' %}current{% else %}tab{% endif %}">{% if _get.p != 'faq' %}<a href="{% kxEnv "paths:main:path" %}/index.php?p=faq">{% endif %}{% trans "FAQ" %}{% if _get.p != 'faq' %}</a>{% endif %}</li>{% endstrip %}
        {% strip %}<li class="{% if _get.p == 'rules' %}current{% else %}tab{% endif %}">{% if _get.p != 'rules' %}<a href="{% kxEnv "paths:main:path" %}/index.php?p=rules">{% endif %}{% trans "Rules" %}{% if _get.p != 'rules' %}</a>{% endif %}</li>{% endstrip %}
      </ul>
      <br style="clear: both;" />
    </nav>
    
    <section class="leftcol recentimages">
      <header>
        <h2>{% trans "Recent Images" %}</h2>
      </header>
      <article>
  {% for image in images %}
        <div class="imagewrap">
          <a href="{% kxEnv "paths:main:path" %}/{{ image.boardname }}/res/{% if image.parentid == 0 %}{{ image.id }}{% else %}{{ image.parentid }}{% endif %}.html#{{ image.id }}">
          <img src="{% kxEnv "paths:main:path" %}/thumb/{{ image.file }}s.{{ image.file_type }}" alt="{{ image.file }}s.{{ image.file_type }}" width="{{ image.thumb_w }}" height="{{ image.thumb_h }}" /><br />
        </div>
  {% endfor %}
      </article>
    </section>
    
    <section class="rightcol boardlist">
      <header>
        <h2>{% trans "Boards" %}</h2>
      </header>
{% for section in sections %}
      <article>
        <header>
          <h3>{{ section.name }}</h3>
        </header>
        <ul class="{{ section.abbreviation }}">
{% for board in section.boards %}
          <li><a href="{% kxEnv "paths:board:path" %}/{{ board.board_name }}/" title="{% kxEnv "site:name" %} - {{ board.board_desc }}">{{ board.board_desc }}</a></li>
{% else %}
          <li>No boards</li>
{% endfor %}
        </ul>
      </article>
{% endfor %}
    </section>
    
    <section class="maincol news">
      <header>
        <h2>{% if _get.p == '' %}{% trans "News" %}{% elseif _get.p == 'faq' %}{% trans "FAQ" %}{% elseif _get.p == 'rules' %}{% trans "Rules" %}{% endif %}</h2>
      </header>
  {% for item in entries %}
      <article class="newspost">
        <header>
          <h3>
            <span class="newssub">{{ item.entry_subject }} {% if _get.p == '' %} by {% if item.entry_email != '' %} <a href="mailto:{{ item.entry_email }}">{% endif %} {{ item.poster }} {% if item.entry_email != '' %} </a>{% endif %}  - {{ item.entry_time|date("d/m/y @ h:i a T") }} {% endif %} </span>
            <span class="permalink"><a href="#{{ item.id }}">#</a></span>
          </h3>
        </header>
        <p>
          {{ item.entry_message }}
            {% if _get.view != 'all' and _get.p == '' %}<br /><a href="{% kxEnv "paths:main:path" %}/index.php?view=all">More entries</a>{% endif %}
        
        </p>
      </article>
  {% endfor %}
    </section>
  </div>

{% endblock %}