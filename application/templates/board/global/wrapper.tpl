{% extends "global_wrapper.tpl" %}

{% block title %}{{board.board_name}}{% if board.board_desc %} - {{board.board_desc}}{% endif %}{% endblock %}

{% block css %}
  <link rel="stylesheet" type="text/css" href="{% kxEnv "paths:boards:path" %}/public/css/img_global.css" />
  {% for style in ku_styles %}
    <link rel="{% if style != ku_defaultstyle %}alternate {% endif %}stylesheet" type="text/css" href="{% kxEnv "paths:boards:path" %}/public/css/{{ style }}.css" title="{{ style|capitalize }}" />
  {% endfor %}
  {% if locale == 'he' %}
    {% raw %}
      <style type="text/css">
        .thumb{
          float:right;
        }
      </style>
    {% endraw %}
  {% endif %}
  {{parent()}}
{% endblock %}
{% block extrajs %}
	kusaba.board   = '{{board.board_name}}';
	kusaba.kumod_set = false;
	kusaba.quick_reply = false;
	kusaba.lastid;
	kusaba.cgipath = '{% kxEnv "paths:cgi:path" %}';
	kusaba.webpath = '{% kxEnv "paths:main:path" %}';
  kusaba.hiddenthreads = $.cookie('hiddenthreads') ? $.cookie('hiddenthreads').split('!') : [];
{% endblock %}

{% block content %}
  {% block boardheader %}
    <div id="adminbar" class="adminbar">
    {% if "css:imgswitcher"|kxEnv %}
      {% if "css:imgdropswitcher"|kxEnv %}
        <select id="dropswitch">
          <option>{% trans "Styles" %}</option>
          {% for style in ku_styles %}
           <option value="{{ style|capitalize }}">{{ style|capitalize }}</option>
          {% endfor %}
        </select>
      {% else %}
        {% for style in ku_styles %}
          [<a href="#" id="{{ style|capitalize }}">{{ style|capitalize }}</a>]&nbsp;
        {% endfor %}
      {% endif %}
      {% if ku_styles|length > 0 %}
        -&nbsp;
      {% endif %}
    {% endif %}
    {% if "extra:watchthreads"|kxEnv %}
      [<a href="#" id="showwatchedthreads" title="{% trans "Watched Threads" %}">WT</a>]&nbsp;
    {% endif %}

    {% if "extra:postspy"|kxEnv %}
      [<a href="#" id="togglepostspy" title="{% trans "Post Spy" %}">PS</a>]&nbsp;
    {% endif %}

    [<a href="{% kxEnv "paths:main:path" %}" target="_top">{% trans "Home" %}</a>]&nbsp;[<a href="{% kxEnv "paths:cgi:path" %}/manage.php" target="_top">{% trans "Manage" %}</a>]
    </div>
    <div class="navbar">
      {%if "misc:boardlist"|kxEnv %}
        {% include "board/global/boardlist.tpl" %}
      {% endif %}
    </div>
    {% if "extra:watchthreads"|kxEnv %}
      <div id="watchedthreads"></div>
    {% endif %}

    <div class="logo">
      {% if 'site:header'|kxEnv and not board.board_image %}
        <img src="{% kxEnv 'site:header' %}" alt="{% trans "Logo" %}" /><br />
      {% elseif board.board_image and board.board_image != "none" %}
        <img src="{{board.board_image}}" alt="{% trans "Logo"%}" /><br />
      {% endif %}
	  <h1>
      {% if 'pages:dirtitle'|kxEnv %}
        /{{board.board_name}}/
      {% endif %}
	  {% if board.board_desc and 'pages:dirtitle'|kxEnv %} - {% endif %}
	  {% if board.board_desc %}{{board.board_desc}}{% endif %}
	  </h1>
    </div>
    {{board.board_include_header}}
    <hr />
  {% endblock %}
  {% block boardcontent %}{% endblock %}
  {% block boardfooter %}
    {% if not isread %}
      <div id="thread_controls">
        <div id="posts_delete">
          {% trans "Delete post"%}
          [<input type="checkbox" name="fileonly" id="fileonly" value="on" /><label for="fileonly">{% trans "File Only" %}</label>]<br />{% trans "Password" %}
          <input type="password" name="postpassword" size="8" />&nbsp;<input name="deletepost" value="{% trans "Delete" %}" type="submit" />
        </div>
        {% if board.board_enablereporting == 1 %}
          <div id="posts_report">
            {% trans "Report post" %}<br />
            {% trans "Reason" %}
            <input type="text" name="reportreason" size="10" />&nbsp;<input name="reportpost" value="{% trans "Report" %}" type="submit" />	
          </div>
        {% endif %}
      </div>
      </form>

      <script type="text/javascript"><!--
        kusaba.set_delpass("delform");
      //--></script>
    {% endif %}
    {% if replythread == 0%}
      <div id="paging">
        <ul>
          {% spaceless %}
            <li id="prev">
              {% if thispage == 0 %}
                {% trans "Previous" %}
              {% else %}
                <form method="get" action="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/{% if (thispage-1) != 0 %}{{thispage-1}}.html{% endif %}">
                  <input value="{% trans "Previous" %}" type="submit" /></form>
              {% endif %}
            </li>
          {% endspaceless %}
          {% spaceless %}
            {% for page in range(0, numpages) %}
              {% spaceless %}
                <li>&#91;
                  {% if page != thispage %}
                    <a href="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/{{page}}.html">
                  {% endif %}
                
                  {{ page }}
                
                  {% if page != thispage %}
                    </a>
                  {% endif %}
                &#93;</li>
              {% endspaceless %}
            {% endfor %}	
          {% endspaceless %}
          {% spaceless %}
            <li id="next">
              {% if thispage == numpages %}
                {% trans "Next" %}
              {% else %}
                <form method="get" action="{% kxEnv "paths:boards:path" %}/{{board.board_name}}/{{thispage+1}}.html"><input value="{% trans "Next" %}" type="submit" /></form>
              {% endif %}
            </li>
          {% endspaceless %}
        </ul>
      </div>
    {% endif %}
    <div id="footer">
      {% if boardlist %}
        <div class="navbar">
          {%if "misc:boardlist"|kxEnv %}
            {% include "board/global/boardlist.tpl" %}
          {% endif %}
        </div>
      {% endif %}
      <div id="disclaimer">
        {# I'd really appreciate it if you left the link to edaha.org in the footer, if you decide to modify this. That being said, you are not bound by license or any other terms to keep it there #}
        - <a href="http://edaha.org/" target="_top">Edaha 1.0</a>
        {% if executiontime %} + {% trans "Took" %} {{executiontime}}s -{% endif %}
        {% if botads %}
          <div class="content ads">
            <center> 
              {{botads}}
            </center>
          </div>
        {% endif %}
      </div>
    </div>
  {% endblock %}
{% endblock %}