<script type="text/javascript" src="{$cwebpath}lib/javascript/jquery-1.3.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="{$cwebpath}css/img_global.css" />
{loop $ku_styles}
	<link rel="{if $ neq $__.ku_defaultstyle}alternate {/if}stylesheet" type="text/css" href="{$__.cwebpath}css/{$}.css" title="{$|capitalize}" />
{/loop}
{if $locale eq 'ja'}
	{literal}
	<style type="text/css">
		*{
			font-family: IPAMonaPGothic, Mona, 'MS PGothic', YOzFontAA97 !important;
			font-size: 1em;
		}
	</style>
	{/literal}
{/if}
{if $locale eq 'he'}
        {literal}
        <style type="text/css">
                .thumb{
			float:right;
                }
        </style>
        {/literal}
{/if}

{if 'extra:rss'|kxEnv}
	<link rel="alternate" type="application/rss+xml" title="RSS" href="{kxEnv paths:boards:path}/{$board->board_name}/rss.xml" />
{/if}
<script type="text/javascript"><!--
		var ku_boardspath = '{kxEnv paths:boards:path}';
		var ku_cgipath = '{kxEnv paths:script:path}';
		var style_cookie = "kustyle";
{if $replythread > 0}
		var ispage = false;
{else}
		var ispage = true;
{/if}
//--></script>
<script type="text/javascript" src="{$cwebpath}lib/javascript/kusaba.js"></script>
<script type="text/javascript"><!--
	var hiddenthreads = getCookie('hiddenthreads').split('!');
//--></script>
</head>
<body>
<div class="adminbar">
{if 'css:imgswitcher'|kxEnv}
	{if 'css:imgdropswitcher'|kxEnv}
		<select onchange="javascript:if(selectedIndex != 0)set_stylesheet(options[selectedIndex].value);return false;">
			<option>{t "Styles"}</option>
		{loop $ku_styles}
			<option value="{$|capitalize}">{$|capitalize}</option>;
		{/loop}
		</select>
	{else}
		{loop $ku_styles}
			[<a href="#" onclick="javascript:set_stylesheet('{$|capitalize}');return false;">{$|capitalize}</a>]&nbsp;
		{/loop}
	{/if}
	{if count($ku_styles) > 0}
		-&nbsp;
	{/if}
{/if}
{if 'extra:watchthreads'|kxEnv}
	[<a href="#" onclick="javascript:showwatchedthreads();return false" title="{t "Watched Threads"}">WT</a>]&nbsp;
{/if}

{if 'extra:postspy'|kxEnv}
	[<a href="#" onclick="javascript:togglePostSpy();return false" title="{t "Post Spy"}">PS</a>]&nbsp;
{/if}

[<a href="{$webpath}" target="_top">{t "Home"}</a>]&nbsp;[<a href="{kxEnv paths:script:path}/manage.php" target="_top">{t "Manage"}</a>]
</div>
<div class="navbar">
{if 'misc:boardlist'|kxEnv}
	{foreach name=sections item=sect from=$boardlist}
		[
	{foreach name=brds item=brd from=$sect}
		<a title="{$brd.desc}" href="{kxEnv paths:boards:folder}{$brd.name}/">{$brd.name}</a>{if $.foreach.brds.last}{else} / {/if}
	{/foreach}
		 ]
	{/foreach}
{else}
	{if is_file($boardlist)}
		{include $boardlist}
	{/if}
{/if}
</div>
{if ('extra:watchthreads'|kxEnv) && not $isoekaki && not $hidewatchedthreads}
				<script type="text/javascript"><!--
				if (getCookie('showwatchedthreads') == '1') {
				document.write('<div id="watchedthreads" style="top: {$ad_top}px; left: 25px;" class="watchedthreads"><div class="postblock" id="watchedthreadsdraghandle" style="width: 100%;">{t "Watched Threads"}<\/div><span id="watchedthreadlist"><\/span><div id="watchedthreadsbuttons"><a href="#" onclick="javascript:hidewatchedthreads();return false;" title="{t "Hide the watched threads box"}"><img src="{$cwebpath}css/icons/blank.gif" border="0" class="hidewatchedthreads" alt="hide" /><\/a>&nbsp;<a href="#" onclick="javascript:getwatchedthreads(\'0\', \'{$board->board_name}\');return false;" title="{t "Refresh watched threads"}"><img src="{$cwebpath}css/icons/blank.gif" border="0" class="refreshwatchedthreads" alt="refresh" /><\/a><\/div><\/div>');
				watchedthreadselement = document.getElementById('watchedthreads');
				watchedthreadselement.style.top = getCookie('watchedthreadstop');
				watchedthreadselement.style.left = getCookie('watchedthreadsleft');
				watchedthreadselement.style.width = Math.max(250,getCookie('watchedthreadswidth')) + 'px';
				watchedthreadselement.style.height = Math.max(75,getCookie('watchedthreadsheight')) + 'px';
				getwatchedthreads('<!sm_threadid>', '{$board->board_name}');
			}
			//--></script>
{/if}

<div class="logo">
{if 'site:header'|kxEnv neq '' && $board->image eq ''}
	<img src="{kxEnv site:header}" alt="{t "Logo"}" /><br />
{elseif $board->image neq '' && $board->image neq "none"}
	<img src="{$board->image}" alt="{t "Logo"}" /><br />
{/if}
{if 'pages:dirtitle'|kxEnv}
	/{$board->board_name}/ - 
{/if}
{$board->desc}</div>
{$board->includeheader}
<hr />
