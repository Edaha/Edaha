{% extends "global_wrapper.tpl"%}

{% block title %}
{% kxEnv "site:name" %}{% if _get.view != '' %} - {{ _get.view|capitalize }}{% endif %}{% if _get.page != '' %} - News - Page {{ _get.page }}{% endif %}
{% endblock %}

{% block css %}
  <link rel="stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/site_global.css" />
{% for style in styles %}
  <link rel="{% if style != 'css:sitedefault'|kxEnv %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:main:path" %}/public/css/{{ style }}/site.css" title="{{ style|capitalize }}" />
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
      <ul>
      {% for post in recentposts %}
        <li><a class="ref|{{ post.board_name }}|{% if post.post_parent == 0 %}{{ post.post_id }}{% else %}{{ post.post_parent }}{% endif %}|{{ post.post_id }}" href="{% kxEnv "paths:main:path" %}/{{ post.board_name }}/res/{% if post.post_parent == 0 %}{{ post.post_id }}.html{% else %}{{ post.post_parent }}.html#{{ post.post_id }}{% endif %}">&gt;&gt;&gt;/{{ post.board_name }}/{{ post.id }}</a> - {{ post.post_message|striptags }}{#% if strlen(post.post_message) > 60 %}...{% endif %#}<br /></li>
      {% endfor %}
      </ul>
    </section>
    
    <section id="images">
      <h3>Recent Images</h3>
      <ul>
      {% for post in recentimages %}
        <li><a class="ref|{{ post.board_name }}|{% if post.post_parent == 0 %}{{ post.post_id }}{% else %}{{ post.post_parent }}{% endif %}|{{ post.post_id }}" href="{% kxEnv "paths:main:path" %}/{{ post.board_name }}/res/{% if post.post_parent == 0 %}{{ post.post_id }}.html{% else %}{{ post.post_parent }}.html#{{ post.post_id }}{% endif %}">
        <img alt="&gt;&gt;&gt;/{{ post.board_name }}/{{ post.post_id }}" src="{% kxEnv "paths:main:path" %}/{{ post.board_name}}/thumb/{{ post.file_name }}s.{{ post.file_type }}"></a></li>
      {% endfor %}
      </ul>
    </section>
    <br class="clear" />
  </section>
  
  <div class="wrap">
  <section id="news">
    <header>
      <ul>
        <li{% if _get.view == '' %} class="selected"{% endif %}>{% if _get.view != '' %}<a href="{% kxEnv "paths:main:path" %}/">{% endif %}News{% if _get.view != '' %}</a>{% endif %}</li>
        <li{% if _get.view == 'faq' %} class="selected"{% endif %}>{% if _get.view != 'faq' %}<a href="{% kxEnv "paths:main:path" %}/index.php?view=faq">{% endif %}FAQ{% if _get.view != 'faq' %}</a>{% endif %}</li>
        <li{% if _get.view == 'rules' %} class="selected"{% endif %}>{% if _get.view != 'rules' %}<a href="{% kxEnv "paths:main:path" %}/index.php?view=rules">{% endif %}Rules{% if _get.view != 'rules' %}</a>{% endif %}</li>
      </ul>
      <br class="clear" />
    </header>
    
{% for item in entries %}
    <article>
      <h4 id="id{{ item.entry_id }}">
        <span class="newssub">{{ item.entry_subject }} {% if _get.p == '' %} by {% if item.entry_email != '' %} <a href="mailto:{{ item.entry_email }}">{% endif %} {{ item.entry_name }} {% if item.entry_email != '' %} </a>{% endif %}  - {{ item.entry_time|date("d/m/y @ h:i a T") }} {% endif %} </span>
        <a class="permalink" href="#id{{ item.entry_id }}">#</a>
      </h4>
      
      <p>
        {{ item.entry_message }}
      </p>
    </article>
{% endfor %} 
    
{% if _get.view == '' %}
    <footer>
  {% for i in 0..pages %}
      [ {% if _get.page != i %}<a href="{% kxEnv "paths:main:path" %}/index.php?page={{ i }}">{% endif %}{{ i }}{% if _get.page != i %}</a>{% endif %} ]
  {% endfor %}
    </footer>
{% endif %}
  </section>
  
  <!--<section id="rbanner">
    &nbsp;
  </section>-->
  
  <section id="boardlist">
    <h2>Boards</h2>
    
{% for section in sections %}
    <article>
      <h4><span class="section_toggle" onclick="kusaba.toggle(this, '{{ section.section_abbreviation }}');">&nbsp;{% if section.section_hidden == 1 %}&#43;{% else %}&minus;{% endif %}&nbsp;</span>{{ section.section_name }}</h4>
      
      <ul {% if section.section_hidden == 1 %} style="display: none;"{% endif %}id="{{ section.section_abbreviation }}">
{% for board in section.boards %}
        <li><a href="{% kxEnv "paths:boards:path" %}/{{ board.board_name }}/" title="{% kxEnv "site:name" %} - {{ board.board_desc }}">&bull; {{ board.board_desc }}</a></li>
{% else %}
        <li>No boards</li>
{% endfor %}
      </ul>
      <br class="clear" />
    </article>
{% endfor %}
  </section>
  </div>
  
  {# Little divs needed to make the columns equal height #}
  <div class="wrap hfix">
  <div class="lcol"></div>
  <div class="rcol"></div>
  </div>
  
  <footer>
{% if 'misc:boardlist'|kxEnv %}
    <section id="foot_boardlist">
    {% for section in sections %}
      [
    {% for board in section.boards %}
      <a title="{{board.board_desc}}" href="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/">{{board.board_name}}</a>{% if not loop.last %} / {% endif %}
    {% endfor %}
       ]
    {% endfor %}
{% endif %}
    </section>
    <section id="powered_by">
      Powered by Edaha
    </section>
  </footer>

{% endblock %}