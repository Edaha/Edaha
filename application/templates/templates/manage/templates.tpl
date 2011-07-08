{% extends "manage/wrapper.tpl" %}

{% block heading %}{%trans "Edit Templates" %}{% endblock %}

{% block managecontent %}
<style type=text/css> 
	
/* CSS Tree menu styles */
ol.tree
{
	padding: 0 0 0 30px;
	width: 300px;
}
	ol.tree li 
	{ 
		position: relative; 
		margin-left: -15px;
		list-style: none;
	}
	ol.tree li.file
	{
		margin-left: -1px !important;
	}
		ol.tree li.file a
		{
			background: url(document.png) 0 0 no-repeat;
			color: #fff;
			padding-left: 21px;
			text-decoration: none;
			display: block;
		}
		li.file a[href *= '.pdf']	{ background: url(document.png) 0 0 no-repeat; }
		li.file a[href *= '.html']	{ background: url(document.png) 0 0 no-repeat; }
		li.file a[href $= '.css']	{ background: url(document.png) 0 0 no-repeat; }
		li.file a[href $= '.js']		{ background: url(document.png) 0 0 no-repeat; }
	ol.tree li input
	{
		position: absolute;
		left: 0;
		margin-left: 0;
		opacity: 0;
		z-index: 2;
		cursor: pointer;
		height: 1em;
		width: 1em;
		top: 0;
	}
		ol.tree li input + ol
		{
			background: url(toggle-small-expand.png) 40px 0 no-repeat;
			margin: -0.938em 0 0 -44px; /* 15px */
			xdisplay: block;
			height: 1em;
		}
		li input + ol > li { height: 0; overflow: hidden; margin-left: -14px !important; padding-left: 1px; }
	ol.tree li label
	{
		background: url(folder-horizontal.png) 15px 1px no-repeat;
		cursor: pointer;
		display: block;
		padding-left: 37px;
	}

	ol.tree li input:checked + ol
	{
		background: url(toggle-small.png) 40px 5px no-repeat;
		margin: -1.25em 0 0 -44px; /* 20px */
		padding: 1.563em 0 0 80px;
		height: auto;
	}
		li input:checked + ol > li { height: auto; margin: 0 0 0.125em;  /* 2px */}
		li input:checked + ol > li:last-child { margin: 0 0 0.063em; /* 1px */ }</style>
{% macro list(folder) %}
    {% for key, entry in folder %}
      {% if entry|is_array %}
        <li>
          <label for="{{ key }}">{{ key }}</label> <input type="checkbox" id="{{ key }}" />
          <ol>
            {{ _self.list(entry) }}
          </ol>
        </li>
      {% else %}
        <li class="file"><a href="">{{ entry }}</a></li>
      {% endif %}
    {% endfor %}
{% endmacro %}

<ol class="tree">
  {{ _self.list(entries) }}
</ol>

{% endblock %}