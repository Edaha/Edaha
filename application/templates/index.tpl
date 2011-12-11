{% extends "global_wrapper.tpl"%}

{% block css %}
{% for style in styles %}
  <link rel="{% if style != 'css:menudefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/{{ style }}/site.css" title="{{ style|capitalize }}" />
{% endfor %}
{% endblock %}

{% block content %}
  <header role="banner">
    <h1>{% kxEnv "site:name" %}</h1>
    {% if 'site:slogan'|kxEnv != '' %}
    <h6>{% kxEnv "site:slogan" %}</h6>
    {% endif %}
  </header>
  
  <section id="recent">
    <section id="posts">
      <h3>Recent Posts</h3>
      
    </section>
    
    <section id="images">
      <h3>Recent Images</h3>
      
    </section>
    <br class="clear" />
  </section>
  
  <section id="news">
    <h2>News</h2>
    
    {% for item in entries %}
    <article>
      <h4>
        <span class="newssub">{{ item.entry_subject }} {% if _get.p == '' %} by {% if item.entry_email != '' %} <a href="mailto:{{ item.entry_email }}">{% endif %} {{ item.poster }} {% if item.entry_email != '' %} </a>{% endif %}  - {{ item.entry_time|date("d/m/y @ h:i a T") }} {% endif %} </span>
        <span class="permalink"><a href="#{{ item.id }}">#</a></span>
      </h4>
      </header>
      <p>
        {{ item.entry_message }}
      </p>
    </article>
    {% endfor %} 
    
    {% if _get.view != 'all' and _get.p == '' %}<br /><a href="{% kxEnv "paths:main:path" %}/index.php?view=all">More entries</a>{% endif %}
  </section>
  
  <section id="boardlist">
    <h2>Boards</h2>
    
{% for section in sections %}
    <article>
      <h4>{{ section.name }}<span class="section_toggle" onclick="kusaba.toggle(this, '{{ section.abbreviation }}');">&nbsp;+&nbsp;</span></h4>
      
      <ul id="{{ section.abbreviation }}">
{% for board in section.boards %}
        <li><a href="{% kxEnv "paths:board:path" %}/{{ board.board_name }}/" title="{% kxEnv "site:name" %} - {{ board.board_desc }}">{{ board.board_desc }}</a></li>
{% else %}
        <li>No boards</li>
{% endfor %}
      </ul>
    </article>
{% endfor %}
  </section>
  
  <br class="clear" />
  <footer>
{% if 'misc:boardlist'|kxEnv %}
	{% for section in sections %}
		[
	{% for board in section.boards %}
		<a title="{{board.board_desc}}" href="{kxEnv "paths:boards:folder"}{{board.board_name}}/">{{board.board_name}}</a>{% if not loop.last %} / {% endif %}
	{% endfor %}
		 ]
	{% endfor %}
{% endif %} 
  </footer>
  
  <!--    
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
    
  -->

{% endblock %}