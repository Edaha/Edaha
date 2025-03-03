{% if kxEnv("misc:boardlist") %}
	{%~ for sect in boardlist ~%}
		[
	{% for brd in sect.boards %}
		<a title="{{brd.board_desc}}" href="{{ kxEnv('paths:boards:path') }}/{{ brd.board_name }}/">{{brd.board_name}}</a>{% if not loop.last %} / {% endif %}
	{% endfor %}
		 ]
	{%~ endfor ~%}
{# {else}
	{if is_file($boardlist)}
		{include $boardlist}
	{/if}#}
{% endif %} 