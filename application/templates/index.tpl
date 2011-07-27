{% extends "global_wrapper.tpl"%}

{% block css %}
  {% for style in styles %}
    <link rel="{% if style != 'css:menudefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/site_{{ style }}.css" title="{{ style|capitalize }}" />
  {% endfor %}
{% endblock %}

{% block content %}
  <h1>{% kxEnv "site:name" %}</h1>
  {% if 'site:slogan'|kxEnv != '' %}
  <h3>{% kxEnv "site:slogan" %}</h3>
  {% endif %}
  
  <div class="menu" id="topmenu">
    <ul>
			{% strip %}<li class="{% if _get.p == '' %}current {% else %}tab {% endif %}first">{% if _get.p != '' %}<a href="{% kxEnv "paths:main:path" %}/index.php">{% endif %}{% trans "News" %}{% if _get.p != '' %}</a>{% endif %}</li>{% endstrip %}
			{% strip %}<li class="{% if _get.p == 'faq' %}current{% else %}tab{% endif %}">{% if _get.p != 'faq' %}<a href="{% kxEnv "paths:main:path" %}/index.php?p=faq">{% endif %}{% trans "FAQ" %}{% if _get.p != 'faq' %}</a>{% endif %}</li>{% endstrip %}
			{% strip %}<li class="{% if _get.p == 'rules' %}current{% else %}tab{% endif %}">{% if _get.p != 'rules' %}<a href="{% kxEnv "paths:main:path" %}/index.php?p=rules">{% endif %}{% trans "Rules" %}{% if _get.p != 'rules' %}</a>{% endif %}</li>{% endstrip %}
    </ul>
    <br />
  </div>
  
  <div class="recentimages">
    <h2>Recent Images</h2>
{% for image in images %}
    <div class="imagewrap">
      <a href="{% kxEnv "paths:main:path" %}/{{ image.boardname }}/res/{% if image.parentid == 0 %}{{ image.id }}{% else %}{{ image.parentid }}{% endif %}.html#{{ image.id }}">
      <img src="{% kxEnv "paths:main:path" %}/thumb/{{ image.file }}s.{{ image.file_type }}" alt="{{ image.file }}s.{{ image.file_type }}" width="{{ image.thumb_w }}" height="{{ image.thumb_h }}" /><br />
    </div>
{% endfor %}
  </div>
  
{% for item in entries %}
  <div class="content">
		<h2><span class="newssub">{{ item.entry_subject }} {% if _get.p == '' %} by {% if item.entry_email != '' %} <a href="mailto:{{ item.entry_email }}">{% endif %} {{ item.poster }} {% if item.entry_email != '' %} </a>{% endif %}  - {{ item.entry_time|date("d/m/y @ h:i a T") }} {% endif %} </span>
		<span class="permalink"><a href="#{{ item.id }}">#</a></span></h2>
		{{ item.entry_message }}
    {% if _get.view != 'all' and _get.p == '' %}<br /><a href="{% kxEnv "paths:main:path" %}/index.php?view=all">More entries</a>{% endif %}
    <br />
	</div>
{% endfor %}

<!-- TODO: Board list -->

{% endblock %}