<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"">
<head>
	<title>{kxEnv site:name}</title>
{for style $styles}
	<link rel="{if $styles[$style] neq 'css:menudefault'|kxEnv}alternate {/if}stylesheet" type="text/css" href="{$webpath}/css/site_{$styles[$style]}.css" title="{$styles[$style]|capitalize}" />
{/for}
</head>
<body>
	<h1>{kxEnv site:name}</h1>
{if 'site:slogan'|kxEnv neq ''}
	<h3>{kxEnv site:slogan}</h3>
{/if}
	
	<div class="menu" id="topmenu">
		<ul>
			{strip}<li class="{if $.get.p eq ''}current {else}tab {/if}first">{if $.get.p neq ''}<a href="{$webpath}/index.php">{/if}{t "News"}{if $.get.p neq ''}</a>{/if}</li>{/strip}
			{strip}<li class="{if $.get.p eq 'faq'}current{else}tab{/if}">{if $.get.p neq 'faq'}<a href="{$webpath}/index.php?p=faq">{/if}{t "FAQ"}{if $.get.p neq 'faq'}</a>{/if}</li>{/strip}
			{strip}<li class="{if $.get.p eq 'rules'}current{else}tab{/if}">{if $.get.p neq 'rules'}<a href="{$webpath}/index.php?p=rules">{/if}{t "Rules"}{if $.get.p neq 'rules'}</a>{/if}</li>{/strip}
		</ul>
		<br />
	</div>

	<div class="recentimages">
		<h2>Recent Images</h2>
{foreach item=image from=$images}
			<div class="imagewrap">
				<a href="{$webpath}/{$image->boardname}/res/{if $image->parentid eq 0}{$image->id}{else}{$image->parentid}{/if}.html#{$image->id}">
					<img src="{$webpath}/{$image->boardname}/thumb/{$image->file}s.{$image->file_type}" alt="{$image->file}s.{$image->file_type}" width="{$image->thumb_w}" height="{$image->thumb_h}" /><br />
				</a>
			</div>
{/foreach}
	</div>
	
{foreach item=entry from=$entries}
	<div class="content">
		<h2><span class="newssub">{$entry->subject|stripslashes} by {$entry->poster|stripslashes} - {$entry->timestamp|date_format:"%D @ %I:%M %p %Z"}</span>
		<span class="permalink"><a href="#{$entry->id}">#</a></span></h2>
		{$entry->message|stripslashes}
		{if $.get.view neq 'all' and $.get.p eq ''}<br /><a href="{$webpath}/index.php?view=all">More entries...</a>{/if}
		<br />
	</div>
{/foreach}

{if $.get.p eq '' and $.get.view neq 'all'}
	<br />
	<div class="content">
{foreach item=section from=$sections}
		<div class="section"{if $.foreach.default.index % 3 eq 0 } style="clear: left;"{/if}>
			<div class="heading">
				{$section->name}
			</div>
			<div class="boards">
				<ul>
{foreach item=board from=$boards}{if $board->section eq $section->id}
					<li class="{if $board->trial eq 1}trial{/if}{if $board->popular eq 1} pop{/if}"><a href="{kxEnv paths:boards:path}/{$board->name}">{$board->desc}</a></li>
{/if}{/foreach}
				</ul>
			</div>
		</div>
{/foreach}
	</div>
{/if}

</body>
</html>