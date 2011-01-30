{if not $isread}
	<table class="userdelete">
	<tbody>
	<tr>
	<td>
	{t "Delete post"}
	[<input type="checkbox" name="fileonly" id="fileonly" value="on" /><label for="fileonly">{t "File Only"}</label>]<br />{t "Password"}
	<input type="password" name="postpassword" size="8" />&nbsp;<input name="deletepost" value="{t "Delete"}" type="submit" />

	{if $board->enablereporting eq 1}
		</td>
		</tr>
		<tr>
		<td>
		{t "Report post"}<br />
		{t "Reason"}
		<input type="text" name="reportreason" size="10" />&nbsp;<input name="reportpost" value="{t "Report"}" type="submit" />	
	{/if}

	</td>
	</tr>
	</tbody>
	</table>
	</form>

	<script type="text/javascript"><!--
		set_delpass("delform");
	//--></script>
{/if}
{if $replythread eq 0}
	<table border="1">
	<tbody>
		<tr>
			<td>
				{if $thispage eq 0}
					{t "Previous"}
				{else}
					<form method="get" action="{kxEnv paths:boards:folder}{$board->board_name}/{if ($thispage-1) neq 0}{$thispage-1}.html{/if}">
						<input value="{t "Previous"}" type="submit" /></form>
				{/if}
			</td>
			<td>
				&#91;{if $thispage neq 0}<a href="{kxEnv paths:boards:path}/{$board->board_name}/">{/if}0{if $thispage neq 0}</a>{/if}&#93;
				{section name=pages loop=$numpages}
				{strip}
					&#91;
					{if $.section.pages.iteration neq $thispage}<a href="{kxEnv paths:boards:folder}{$board->board_name}/{$.section.pages.iteration}.html">
					{/if}
					
					{$.section.pages.iteration}
					
					{if $.section.pages.iteration neq $thispage}
					</a>
					{/if}
					&#93;
				{/strip}
				{/section}	
			</td>
			<td>
				{if $thispage eq $numpages}
					{t "Next"}
				{else}
					<form method="get" action="{kxEnv paths:boards:path}/{$board->board_name}/{$thispage+1}.html"><input value="{t "Next"}" type="submit" /></form>
				{/if}
	
			</td>
		</tr>
	</tbody>
	</table>
{/if}
<br />
{if $boardlist}
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
{/if}
<br />
<div class="footer" style="clear: both;">
	{* I'd really appreciate it if you left the link to kusabax.org in the footer, if you decide to modify this. That being said, you are not bound by license or any other terms to keep it there *}
	- <a href="http://www.kusabax.org/" target="_top">kusaba x {kxEnv misc:version}</a>
	{if $executiontime neq ''} + {t "Took"} {$executiontime}s -{/if}
	{if $botads neq ''}
		<div class="content ads">
			<center> 
				{$botads}
			</center>
		</div>
	{/if}
</div>
