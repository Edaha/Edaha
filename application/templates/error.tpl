<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{kxEnv site:name}</title>
<link rel="shortcut icon" href="{$webpath}/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{kxEnv paths:boards:path}/css/menu_global.css" />
{loop $styles}
	<link rel="{if $ neq 'css:menudefault'|kxEnv}alternate {/if}stylesheet" type="text/css" href="{$webfolder}css/site_{$}.css" title="{$|capitalize}" />
	<link rel="{if $ neq 'css:menudefault'|kxEnv}alternate {/if}stylesheet" type="text/css" href="{$webfolder}css/sitemenu_{$}.css" title="{$|capitalize}" />
{/loop}

<style type="text/css">{literal}
body {
	width: 100% !important;
}
{/literal}</style>
</head>
<body>
<h1 style="font-size: 3em;">{t "Error"}</h1>
<br />
<h2 style="font-size: 2em;font-weight: bold;text-align: center;">
{$errormsg}
</h2>
{$errormsgext}
<div style="text-align: center;width: 100%;position: absolute;bottom: 10px;">
<br />
<div class="footer" style="clear: both;">
	{* I'd really appreciate it if you left the link to kusabax.org in the footer, if you decide to modify this. That being said, you are not bound by license or any other terms to keep it there *}
	<div class="legal">	- <a href="http://www.kusabax.org/" target="_top">kusaba x {kxEnv misc:version}</a> -
</div>
</div>
</body>
</html>
