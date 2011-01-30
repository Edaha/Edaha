<div class="postarea">
<a id="postbox"></a>
<form name="postform" id="postform" action="{kxEnv paths:script:path}/board.php" method="post" enctype="multipart/form-data"
{if $board->enablecaptcha eq 1}
	onsubmit="return checkcaptcha('postform');"
{/if}
>
<input type="hidden" name="board" value="{$board->board_name}" />
<input type="hidden" name="replythread" value="<!sm_threadid>" />
{if $board->maximagesize > 0}
	<input type="hidden" name="MAX_FILE_SIZE" value="{$board->maximagesize}" />
{/if}
<input type="text" name="email" size="28" maxlength="75" value="" style="display: none;" />
<table class="postform">
	<tbody>
	{if $board->forcedanon neq 1}
		<tr>
			<td class="postblock">
				{t "Name"}</td>
			<td>
				<input type="text" name="name" size="28" maxlength="75" accesskey="n" />
			</td>
		</tr>
	{/if}
	<tr>
		<td class="postblock">
			{t "Email"}</td>
		<td>
			<input type="text" name="em" size="28" maxlength="75" accesskey="e" />
		</td>
	</tr>
	{if $board->enablecaptcha eq 1}
		<tr>
			<td class="postblock'">
				<a href="#" onclick="javascript:document.getElementById('captchaimage').src = '{kxEnv paths:script:path}/captcha.php?' + Math.random();return false;"><img id="captchaimage" src="{kxEnv paths:script:path}/captcha.php" border="0" width="90" height="25" alt="Captcha image"></a>
			</td>
			<td>
				<input type="text" name="captcha" size="28" maxlength="10" accesskey="c" />
			</td>
		</tr>
	{/if}
	<tr>
		<td class="postblock">
			{t "Subject"}</td>
		<td>
			{strip}<input type="text" name="subject" size="35" maxlength="75" accesskey="s" />&nbsp;<input type="submit" value="
			{if 'extra:quickreply'|kxEnv && $replythread eq 0}
				{t "Submit"}" accesskey="z" />&nbsp;(<span id="posttypeindicator">{t "new thread"}</span>)
			{elseif 'extra:quickreply'|kxEnv && $replythread neq 0}
				{t "Reply"}" accesskey="z" />&nbsp;(<span id="posttypeindicator">{t "reply to"} <!sm_threadid></span>)
			{else}
				{t "Submit"}" accesskey="z" />
			{/if}{/strip}
		</td>
	</tr>
	<tr>
		<td class="postblock">
			{t "Message"}
		</td>
		<td>
			<textarea name="message" cols="48" rows="4" accesskey="m"></textarea>
		</td>
	</tr>
	{if $board->uploadtype eq 0 || $board->uploadtype eq 1}
		{if $board->maxfiles gt 1 && $replythread neq 0}
			{section name=files loop=$board->maxfiles}
				<tr id="file{$.section.files.iteration}"{if !$.section.files.first} style="display:none"{/if}>
					<td class="postblock">
						{t "File"} {$.section.files.iteration}
					</td>
					<td>				
					<input{if !$.section.files.last} onchange="$('#file{$.section.files.iteration + 1}').show()"{/if} type="file" name="imagefile[]" size="35" accesskey="f" />
					{if $.section.files.first && $replythread eq 0 && $board->enablenofile eq 1 }
						[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {t "No File"}</label>]
					{/if}
					</td>
				</tr>
			{/section}
		{else}
			<tr>
				<td class="postblock">
					{t "File"}
				</td>
				<td>				
				<input type="file" name="imagefile[]" size="35" accesskey="f" />
				{if $replythread eq 0 && $board->enablenofile eq 1 }
					[<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><label for="nofile"> {t "No File"}</label>]
				{/if}
				</td>
			</tr>
		{/if}

	{/if}
	{if ($board->uploadtype eq 1 || $board->uploadtype eq 2) && $board->embeds_allowed neq ''}
		<tr>
			<td class="postblock">
				{t "Embed"}
			</td>
			<td>
				<input type="text" name="embed" size="28" maxlength="75" accesskey="e" />&nbsp;<select name="embedtype">
				{foreach name=embed from=$embeds item=embed}
					{if in_array($embed.filetype,explode(',' $board->embeds_allowed))}
						<option value="{$embed.name|lower}">{$embed.name}</option>
					{/if}
				{/foreach}
				</select>
				<a class="rules" href="#postbox" onclick="window.open('{$webpath}/embedhelp.php','embedhelp','toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=300,height=210');return false;">Help</a>
			</td>
		</tr>
	{/if}
		<tr>
			<td class="postblock">
				{t "Password"}
			</td>
			<td>
				<input type="password" name="postpassword" size="8" accesskey="p" />&nbsp;{t "(for post and file deletion)"}
			</td>
		</tr>
		<tr id="passwordbox"><td></td><td></td></tr>
		<tr>
			<td colspan="2" class="rules">
				<ul style="margin-left: 0; margin-top: 0; margin-bottom: 0; padding-left: 0;">
					<li>{t "Supported file types are"}:
					{if $board->filetypes_allowed neq ''}
						{foreach name=files item=filetype from=$board->filetypes_allowed}
							{$filetype.0|upper}{if $.foreach.files.last}{else}, {/if}
						{/foreach}
					{else}
						{t "None"}
					{/if}
					</li>
					<li>{t "Maximum file size allowed is"} {math "round(x/1024)" x=$board->maximagesize} KB.</li>
					<li>{t "Images greater than %1x%2 pixels will be thumbnailed." arg1='images:thumbw'|kxEnv arg2='images:thumbh'|kxEnv}</li>
					<li>{t "Currently %1 unique user posts." arg1=$board->uniqueposts}
					{if $board->enablecatalog eq 1} 
						<a href="{kxEnv paths:boards:folder}{$board->board_name}/catalog.html">{t "View catalog"}</a>
					{/if}
					</li>
				</ul>
			{if 'extra:blotter'|kxEnv && $blotter}
				<br />
				<ul style="margin-left: 0; margin-top: 0; margin-bottom: 0; padding-left: 0;">
				<li style="position: relative;">
					<span style="color: red;">
				{t "Blotter updated"}: {$blotter_updated|date_format:"%Y-%m-%d"}
				</span>
					<span style="color: red;text-align: right;position: absolute;right: 0px;">
						<a href="#" onclick="javascript:toggleblotter(true);return false;">{t "Show/Hide"}</a> <a href="{$webpath}/blotter.php">{t "Show All"}</a>
					</span>
				</li>
				{$blotter}
				</ul>
				<script type="text/javascript"><!--
				if (getCookie('ku_showblotter') == '1') {
					toggleblotter(false);
				}
				--></script>
			{/if}
			</td>
		</tr>
	</tbody>

</table>
</form>
<hr />
{if $topads neq ''}
	<div class="content ads">
		<center> 
			{$topads}
		</center>
	</div>
	<hr />
{/if}
</div>
<script type="text/javascript"><!--
				set_inputs("postform");
				//--></script>
