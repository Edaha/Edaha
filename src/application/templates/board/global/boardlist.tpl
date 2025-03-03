{% if kxEnv("misc:boardlist") %}
	{% for sect in boardlist %}
		[
	{% for brd in sect %}
		<a title="{{brd.desc}}" href="{kxEnv "paths:boards:folder"}{{brd.name}}/">{{brd.name}}</a>{% if not loop.last %} / {% endif %}
	{% endfor %}
		 ]
	{% endfor %}
{# {else}
	{if is_file($boardlist)}
		{include $boardlist}
	{/if}#}
{% endif %} 