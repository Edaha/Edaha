<form id="delform" action="{kxEnv paths:script:path}/board.php" method="post">
<input type="hidden" name="board" value="{$board->board_name}" />
{foreach name=thread item=postsa from=$posts}
	{foreach key=postkey item=post from=$postsa}
		{if $post->post_parent eq 0}
			<span id="unhidethread{$post->post_id}{$board->board_name}" style="display: none;">
			{t "Thread"} <a href="{kxEnv paths:boards:folder}{$board->board_name}/res/{$post->post_id}.html">{$post->post_id}</a> {t "hidden."}
			<a href="#" onclick="javascript:togglethread('{$post->post_id}{$board->board_name}');return false;" title="{t "Un-Hide Thread"}">
				<img src="{$cwebpath}css/icons/blank.gif" border="0" class="unhidethread" alt="{t "Un-Hide Thread"}" />
			</a>
	</span>
	<div id="thread{$post->post_id}{$board->board_name}">
	<script type="text/javascript"><!--
		if (hiddenthreads.toString().indexOf('{$post->post_id}{$board->board_name}')!==-1) {
			document.getElementById('unhidethread{$post->post_id}{$board->board_name}').style.display = 'block';
			document.getElementById('thread{$post->post_id}{$board->board_name}').style.display = 'none';
		}
		//--></script>
			<a name="s{$.foreach.thread.iteration}"></a>
			{$post|print_r}
			{if ($post->file_name.0 neq '' || $post->file_type.0 neq '' ) && (($post->videobox eq '' && $post->file_name.0 neq '') && $post->file_name.0 neq 'removed')}
				<span class="filesize">
				{if $post->file_type.0 eq 'mp3'}
					{t "Audio"}
				{else}
					{t "File"}
				{/if}
				{if $post->file_type.0 neq 'jpg' && $post->file_type.0 neq 'gif' && $post->file_type.0 neq 'png' && $post->videobox eq ''}
					<a 
					{if 'posts:newwindow'|kxEnv}
						target="_blank" 
					{/if}
					href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
				{else}
					<a href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}" onclick="javascript:expandimg('{$post->post_id}', '{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}', '{$file_path}/thumb/{$post->file_name.0}s.{$post->file_type.0}', '{$post->post_image_width.0}', '{$post->post_image_height.0}', '{$post->post_thumb_width.0}', '{$post->post_thumb_height.0}');return false;">
				{/if}
				{if isset($post->post_id3.0.comments_html)}
					{if $post->post_id3.0.comments_html.artist.0 neq ''}
					{$post->post_id3.0.comments_html.artist.0}
						{if $post->post_id3.0.comments_html.title.0 neq ''}
							- 
						{/if}
					{/if}
					{if $post->post_id3.0.comments_html.title.0 neq ''}
						{$post->post_id3.0.comments_html.title.0}
					{/if}
					</a>
				{else}
					{$post->file_name.0}.{$post->file_type.0}</a>
				{/if}
				- ({$post->file_size_formatted.0}
				{if $post->post_id3.0.comments_html.bitrate neq 0 || $post->post_id3.0.audio.sample_rate neq 0}
					{if $post->post_id3.0.audio.bitrate neq 0}
						- {round($post->post_id3.0.audio.bitrate / 1000)} kbps
						{if $post->post_id3.0.audio.sample_rate neq 0}
							- 
						{/if}
					{/if}
					{if $post->post_id3.0.audio.sample_rate neq 0}
						{$post->post_id3.0.audio.sample_rate / 1000} kHz
					{/if}
				{/if}
				{if $post->post_image_width.0 > 0 && $post->post_image_height.0 > 0}
					, {$post->post_image_width.0}x{$post->post_image_height.0}
				{/if}
				{if $post->file_original.0 neq '' && $post->file_original.0 neq $post->file_name.0}
					, {$post->file_original.0}.{$post->file_type.0}
				{/if}
				)
				{if $post->post_id3.0.playtime_string neq ''}
					{t "Length"}: {$post->post_id3.0.playtime_string}
				{/if}
				</span>
				{if 'display:thumbmsg'|kxEnv}
					<span class="thumbnailmsg"> 
					{if $post->file_type.0 neq 'jpg' && $post->file_type.0 neq 'gif' && $post->file_type.0 neq 'png' && $post->videobox eq ''}
						{t "Extension icon displayed, click image to open file."}
					{else}
						{t "Thumbnail displayed, click image for full size."}
					{/if}
					</span>
				{/if}
				<br />
			{/if}
			{if $post->videobox eq '' && $post->file_name.0 neq '' && ( $post->file_type.0 eq 'jpg' || $post->file_type.0 eq 'gif' || $post->file_type.0 eq 'png')}
				{if $post->file_name.0 eq 'removed'}
					<div class="nothumb">
						{t "File<br />Removed"}
					</div>
				{else}
					<a {if 'posts:newwindow'|kxEnv}target="_blank"{/if} href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
					<span id="thumb{$post->post_id}"><img src="{$file_path}/thumb/{$post->file_name.0}s.{$post->file_type.0}" alt="{$post->post_id}" class="thumb" height="{$post->post_thumb_height.0}" width="{$post->post_thumb_width.0}" /></span>
					</a>
				{/if}
			{elseif $post->nonstandard_file.0 neq ''}
				{if $post->file_name.0 eq 'removed'}
					<div class="nothumb">
						{t "File<br />Removed"}
					</div>
				{else}
					<a {if 'posts:newwindow'|kxEnv}target="_blank"{/if} href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
					<span id="thumb{$post->post_id}"><img src="{$post->nonstandard_file.0}" alt="{$post->post_id}" class="thumb" height="{$post->post_thumb_height.0}" width="{$post->post_thumb_width.0}" /></span>
					</a>
				{/if}
			{/if}
			<a name="{$post->post_id}"></a>
			<label>
			<input type="checkbox" name="post[]" value="{$post->post_id}" />
			{if $post->post_subject neq ''}
				<span class="filetitle">
					{$post->post_subject}
				</span>
			{/if}
			{strip}
				<span class="postername">
				{if $post->post_email && $board->anonymous}
					<a href="mailto:{$post->post_email}">
				{/if}
				{if $post->post_name eq '' && $post->post_tripcode eq ''}
					{$board->anonymous}
				{elseif $post->post_name eq '' && $post->post_tripcode neq ''}
				{else}
					{$post->post_name}
				{/if}
				{if $post->post_email neq '' && $board->anonymous neq ''}
					</a>
				{/if}

				</span>

				{if $post->post_tripcode neq ''}
					<span class="postertrip">!{$post->post_tripcode}</span>
				{/if}
			{/strip}
			{if $post->post_authority eq 1}
				<span class="admin">
					&#35;&#35;&nbsp;{t "Admin"}&nbsp;&#35;&#35;
				</span>
			{elseif $post->post_authority eq 4}
				<span class="mod">
					&#35;&#35;&nbsp;{t "Super Mod"}&nbsp;&#35;&#35;
				</span>
			{elseif $post->post_authority eq 2}
				<span class="mod">
					&#35;&#35;&nbsp;{t "Mod"}&nbsp;&#35;&#35;
				</span>
			{/if}
			{$post->post_timestamp_formatted}
			</label>
			<span class="reflink">
				{$post->reflink}
			</span>
			{if $board->showid}
				ID: {$post->post_ip_md5|substr:0:6}
			{/if}
			<span class="extrabtns">
			{if $post->post_locked eq 1}
				<img style="border: 0;" src="{$boardpath}css/locked.gif" alt="{t "Locked"}" />
			{/if}
			{if $post->post_stickied eq 1}
				<img style="border: 0;" src="{$boardpath}css/sticky.gif" alt="{t "Stickied"}" />
			{/if}
			<span id="hide{$post->post_id}"><a href="#" onclick="javascript:togglethread('{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}{$board->board_name}');return false;" title="Hide Thread"><img src="{$boardpath}css/icons/blank.gif" border="0" class="hidethread" alt="hide" /></a></span>
			{if 'extra:watchthreads'|kxEnv}
				<a href="#" onclick="javascript:addtowatchedthreads('{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}','{$board->board_name}');return false;" title="Watch Thread"><img src="{$boardpath}css/icons/blank.gif" border="0" class="watchthread" alt="watch" /></a>
			{/if}
			{if 'extra:expand'|kxEnv && $post->replies && ($post->replies + 'display:replies'|kxEnv) < 300}
				<a href="#" onclick="javascript:expandthread('{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}','{$board->board_name}');return false;" title="Expand Thread"><img src="{$boardpath}css/icons/blank.gif" border="0" class="expandthread" alt="expand" /></a>
			{/if}
			{if 'extra:quickreply'|kxEnv}
				<a href="#postbox" onclick="javascript:quickreply('{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}');" title="{t "Quick Reply"}"><img src="{$boardpath}css/icons/blank.gif" border="0" class="quickreply" alt="quickreply" /></a>
			{/if}
			</span>
			<span id="dnb-{$board->board_name}-{$post->post_id}-y"></span>
			[<a href="{kxEnv paths:boards:folder}{$board->board_name}/res/{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}.html">{t "Reply"}</a>]
			{if 'extra:firstlast'|kxEnv && (($post->post_stickied eq 1 && $post->replies + 'display:stickyreplies'|kxEnv > 50) || ($post->post_stickied eq 0 && $post->replies + 'display:replies'|kxEnv > 50))}
				{if (($post->post_stickied eq 1 && $post->replies + 'display:stickyreplies'|kxEnv > 100) || ($post->post_stickied eq 0 && $post->replies + 'display:replies'|kxEnv > 100))}
					[<a href="{kxEnv paths:boards:folder}{$board->board_name}/res/{if $post->post_parent eq 0}{$post->post_id}{else}{$post->post_parent}{/if}-100.html">{t "First 100 posts"}</a>]
				{/if}
				[<a href="{kxEnv paths:boards:folder}{$board->board_name}/res/{$post->post_id}+50.html">{t "Last 50 posts"}</a>]
			{/if}
			<br />
		{else}
			<table>
				<tbody>
				<tr>
					<td class="doubledash">
						&gt;&gt;
					</td>
					<td class="reply" id="reply{$post->post_id}">
						<a name="{$post->post_id}"></a>
						<label>
						<input type="checkbox" name="post[]" value="{$post->post_id}" />
						
						
						{if $post->post_subject neq ''}
							<span class="filetitle">
								{$post->post_subject}
							</span>
						{/if}
						{strip}
							<span class="postername">
							{if $post->post_email && $board->anonymous}
								<a href="mailto:{$post->post_email}">
							{/if}
							{if $post->post_name eq '' && $post->post_tripcode eq ''}
								{$board->anonymous}
							{elseif $post->post_name eq '' && $post->post_tripcode neq ''}
							{else}
								{$post->post_name}
							{/if}
							{if $post->post_email neq '' && $board->anonymous neq ''}
								</a>
							{/if}

							</span>

							{if $post->post_tripcode neq ''}
								<span class="postertrip">!{$post->post_tripcode}</span>
							{/if}
						{/strip}
						{if $post->post_authority eq 1}
							<span class="admin">
								&#35;&#35;&nbsp;{t "Admin"}&nbsp;&#35;&#35;
							</span>
						{elseif $post->post_authority eq 4}
							<span class="mod">
								&#35;&#35;&nbsp;{t "Super Mod"}&nbsp;&#35;&#35;
							</span>
						{elseif $post->post_authority eq 2}
							<span class="mod">
								&#35;&#35;&nbsp;{t "Mod"}&nbsp;&#35;&#35;
							</span>
						{/if}

						{$post->post_timestamp_formatted}
						</label>

						<span class="reflink">
							{$post->reflink}
						</span>
						{if $board->showid}
							ID: {$post->post_ip_md5|substr:0:6}
						{/if}
						<span class="extrabtns">
						{if $post->post_locked eq 1}
							<img style="border: 0;" src="{$boardpath}css/locked.gif" alt="{t "Locked"}" />
						{/if}
						{if $post->post_stickied eq 1}
							<img style="border: 0;" src="{$boardpath}css/sticky.gif" alt="{t "Stickied"}" />
						{/if}
						</span>
						<span id="dnb-{$board->board_name}-{$post->post_id}-n"></span>
						{if count($post->file_name) eq 1 && ($post->file_name.0 neq '' || $post->file_type.0 neq '' ) && (($post->videobox eq '' && $post->file_name.0 neq '') && $post->file_name.0 neq 'removed')}
							<br /><span class="filesize">
							{if $post->file_type.0 eq 'mp3'}
								{t "Audio"}
							{else}
								{t "File"}
							{/if}
							{if $post->file_type.0 neq 'jpg' && $post->file_type.0 neq 'gif' && $post->file_type.0 neq 'png' && $post->videobox eq ''}
								<a {if 'posts:newwindow'|kxEnv}target="_blank" {/if}href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
							{else}
								<a href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}" onclick="javascript:expandimg('{$post->post_id}', '{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}', '{$file_path}/thumb/{$post->file_name.0}s.{$post->file_type.0}', '{$post->post_image_width.0}', '{$post->post_image_height.0}', '{$post->post_thumb_width.0}', '{$post->post_thumb_height.0}');return false;">
							{/if}
							{if isset($post->post_id3.0.comments_html)}
								{if $post->post_id3.0.comments_html.artist.0 neq ''}
								{$post->post_id3.0.comments_html.artist.0}
									{if $post->post_id3.0.comments_html.title.0 neq ''}
										- 
									{/if}
								{/if}
								{if $post->post_id3.0.comments_html.title.0 neq ''}
									{$post->post_id3.0.comments_html.title.0}
								{/if}
								</a>
							{else}
								{$post->file_name.0}.{$post->file_type.0}</a>
							{/if}
							- ({$post->file_size_formatted.0}
							{if $post->post_id3.0.comments_html.bitrate neq 0 || $post->post_id3.0.audio.sample_rate neq 0}
								{if $post->post_id3.0.audio.bitrate neq 0}
									- {round($post->post_id3.0.audio.bitrate / 1000)} kbps
									{if $post->post_id3.0.audio.sample_rate neq 0}
										- 
									{/if}
								{/if}
								{if $post->post_id3.0.audio.sample_rate neq 0}
									{$post->post_id3.0.audio.sample_rate / 1000} kHz
								{/if}
							{/if}
							{if $post->post_image_width.0 > 0 && $post->post_image_height.0 > 0}
								, {$post->post_image_width.0}x{$post->post_image_height.0}
							{/if}
							{if $post->file_original.0 neq '' && $post->file_original.0 neq $post->file_name.0}
								, {$post->file_original.0}.{$post->file_type.0}
							{/if}
							)
							{if $post->post_id3.0.playtime_string neq ''}
								{t "Length"}: {$post->post_id3.0.playtime_string}
							{/if}
							</span>
							{if 'display:thumbmsg'|kxEnv}
								<span class="thumbnailmsg"> 
								{if $post->file_type.0 neq 'jpg' && $post->file_type.0 neq 'gif' && $post->file_type.0 neq 'png' && $post->videobox eq ''}
									{t "Extension icon displayed, click image to open file."}
								{else}
									{t "Thumbnail displayed, click image for full size."}
								{/if}
								</span>
							{/if}
							<br />
						{/if}
						{if count($post->file_name) gt 1}
							{foreach key=fileskey name=filesloop item=file from=$post->file_name}
								{if $post->file_name.$fileskey neq '' && ( $post->file_type.$fileskey eq 'jpg' || $post->file_type.$fileskey eq 'gif' || $post->file_type.$fileskey eq 'png')}
								{if $fileskey % 3 eq 0}<br style="clear:both" />{/if}
									<div style="float:left">
									<span class="{if $.foreach.filesloop.first || $fileskey % 3 eq 0}multithumbfirst{else}multithumb{/if}"><a href="{$file_path}/src/{$post->file_name.$fileskey}.{$post->file_type.$fileskey}" onclick="javascript:expandimg('{$post->post_id}-{$fileskey}', '{$file_path}/src/{$post->file_name.$fileskey}.{$post->file_type.$fileskey}', '{$file_path}/thumb/{$post->file_name.$fileskey}s.{$post->file_type.$fileskey}', '{$post->post_image_width.$fileskey}', '{$post->post_image_height.$fileskey}', '{$post->post_thumb_width.$fileskey}', '{$post->post_thumb_height.$fileskey}');return false;">({if $post->post_image_width.$fileskey > 0 && $post->post_image_height.$fileskey > 0}{$post->post_image_width.$fileskey}x{$post->post_image_height.$fileskey}{else}{$post->file_size_formatted.$fileskey}{/if})</a></span><br />
									<a {if 'posts:newwindow'|kxEnv}target="_blank"{/if} href="{$file_path}/src/{$post->file_name.$fileskey}.{$post->file_type.$fileskey}">
									<span id="thumb{$post->post_id}-{$fileskey}"><img class="{if $.foreach.filesloop.first || $fileskey % 3 eq 0}multithumbfirst{else}multithumb{/if}" src="{$file_path}/thumb/{$post->file_name.$fileskey}s.{$post->file_type.$fileskey}" alt="{$post->post_id}" height="{$post->post_thumb_height.$fileskey}" width="{$post->post_thumb_width.$fileskey}" title="{$post->file_name.$fileskey}.{$post->file_type.$fileskey} - ({$post->file_size_formatted.$fileskey}{if $post->post_image_width.$fileskey > 0 && $post->post_image_height.$fileskey > 0}, {$post->post_image_width.$fileskey}x{$post->post_image_height.$fileskey}{/if}{if $post->file_original.$fileskey neq '' && $post->file_original.$fileskey neq $post->file_name.$fileskey}, {$post->file_original.$fileskey}.{$post->file_type.$fileskey}){/if}" /></span>
									</a></div>
								{elseif $post->nonstandard_file neq ''}
								{if $fileskey eq 3}<br style="clear:left" />{/if}
									<div style="float:left">
									<span class="{if $.foreach.filesloop.first || $fileskey % 3 eq 0}multithumbfirst{else}multithumb{/if}"><a {if 'posts:newwindow'|kxEnv}target="_blank" {/if}href="{$file_path}/src/{$post->file_name.$fileskey}.{$post->file_type.$fileskey}">{$post->file_type.$fileskey|upper} ({$post->file_size_formatted.$fileskey})</a></span><br />
									<a {if 'posts:newwindow'|kxEnv}target="_blank" {/if}href="{$file_path}/src/{$post->file_name.$fileskey}.{$post->file_type.$fileskey}">
									<span id="thumb{$post->post_id}-{$fileskey}"><img class="{if $.foreach.filesloop.first || $fileskey % 3 eq 0}multithumbfirst{else}multithumb{/if}" src="{$post->nonstandard_file.$fileskey}" alt="{$post->post_id}" height="{$post->post_thumb_height.$fileskey}" width="{$post->post_thumb_width.$fileskey}" title="{if isset($post->post_id3.$fileskey.comments_html)}{if $post->post_id3.$fileskey.comments_html.artist.0 neq ''}{$post->post_id3.$fileskey.comments_html.artist.0}{if $post->post_id3.$fileskey.comments_html.title.0 neq ''} - {/if}{/if}{if $post->post_id3.$fileskey.comments_html.title.0 neq ''}{$post->post_id3.$fileskey.comments_html.title.0}{/if}{else}{$post->file_name.$fileskey}.{$post->file_type.$fileskey}{/if} - ({$post->file_size_formatted.$fileskey}{if $post->post_id3.$fileskey.comments_html.bitrate neq 0 || $post->post_id3.$fileskey.audio.sample_rate neq 0}{if $post->post_id3.$fileskey.audio.bitrate neq 0} - {round($post->post_id3.$fileskey.audio.bitrate / 1000)} kbps{if $post->post_id3.$fileskey.audio.sample_rate neq 0} - {/if}{/if}{if $post->post_id3.$fileskey.audio.sample_rate neq 0}{$post->post_id3.$fileskey.audio.sample_rate / 1000} kHz{/if}{/if}{if $post->file_original.$fileskey neq '' && $post->file_original.$fileskey neq $post->file_name.$fileskey}, {$post->file_original.$fileskey}.{$post->file_type.$fileskey}{/if}){if $post->post_id3.$fileskey.playtime_string neq ''} {t "Length"}: {$post->post_id3.$fileskey.playtime_string}{/if}" /></span>
									</a></div>
								{/if}
								{if $.foreach.filesloop.last}
								<br style="clear:both" />
								{/if}
							{/foreach}
						{elseif count($post->file_name) eq 1 && $post->videobox eq '' && $post->file_name.0 neq '' && ( $post->file_type.0 eq 'jpg' || $post->file_type.0 eq 'gif' || $post->file_type.0 eq 'png')}
							{if $post->file_name.0 eq 'removed'}
								<div class="nothumb">
									{t "File<br />Removed"}
								</div>
							{else}
								<a {if 'posts:newwindow'|kxEnv}target="_blank"{/if} href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
								<span id="thumb{$post->post_id}"><img src="{$file_path}/thumb/{$post->file_name.0}s.{$post->file_type.0}" alt="{$post->post_id}" class="thumb" height="{$post->post_thumb_height.0}" width="{$post->post_thumb_width.0}" /></span>
								</a>
							{/if}
						{elseif $post->nonstandard_file.0 neq ''}
							{if $post->file_name.0 eq 'removed'}
								<div class="nothumb">
									{t "File<br />Removed"}
								</div>
							{else}
								<a {if 'posts:newwindow'|kxEnv}target="_blank"{/if} href="{$file_path}/src/{$post->file_name.0}.{$post->file_type.0}">
								<span id="thumb{$post->post_id}"><img src="{$post->nonstandard_file.0}" alt="{$post->post_id}" class="thumb" height="{$post->post_thumb_height.0}" width="{$post->post_thumb_width.0}" /></span>
								</a>
							{/if}
						{/if}
		{/if}
		{if is_array($post->file_type) && in_array("mp3", $post->file_type)}
			{foreach key=fkey name=filesloop item=file from=$post->file_name}
				{if $fkey eq 0 && count($post->file_name) gt 1}
					<br /><span class="multithumbfirst">
				{/if}
				{if $post->file_type.$fkey eq 'mp3'}
					<!--[if !IE]> -->
					<object type="application/x-shockwave-flash" data="{$webpath}/inc/player/player.swf?playerID={$post->post_id}&amp;soundFile={$file_path}/src/{$post->file_name.$fkey|utf8_encode|urlencode}.mp3{if $post->post_id3.$fkey.comments_html.artist.0 neq ''}&amp;artists={$post->post_id3.$fkey.comments_html.artist.0}{/if}{if $post->post_id3.$fkey.comments_html.title.0 neq ''}&amp;titles={urlencode(mb_convert_encoding(html_entity_decode($post->post_id3.$fkey.comments_html.title.0), "UTF-8"))}{/if}&amp;wmode=transparent" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,22,87" width="290" height="24">
					<param name="wmode" value="transparent" />
					<!-- <![endif]-->
					<!--[if IE]>
					<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,22,87" width="290" height="24">
						<param name="movie" value="{$webpath}/inc/player/player.swf?playerID={$post->post_id}&amp;soundFile={$file_path}/src/{$post->file_name.$fkey|utf8_encode|urlencode}.mp3{if $post->post_id3.$fkey.comments_html.artist.0 neq ''}&amp;artists={$post->post_id3.$fkey.comments_html.artist.0}{/if}{if $post->post_id3.$fkey.comments_html.title.0 neq ''}&amp;titles={urlencode(mb_convert_encoding(html_entity_decode($post->post_id3.$fkey.comments_html.title.0), "UTF-8"))}{/if}&amp;wmode=transparent" />
						<param name="wmode" value="transparent" />
					<!-->
					</object>
					<!-- <![endif]-->
					{if count($post->file_name) gt 1}
						<br />
					{/if}
				{/if}
				{if $fkey gt 0 && $.foreach.filesloop.last}
					</span><br style="clear:both" />
				{/if}
			{/foreach}
		{/if}
		<blockquote>
		{if $post->videobox}
			{$post->videobox}
		{/if}
		{$post->post_message}
		</blockquote>
		{if not $post->post_stickied && $post->post_parent eq 0 && (($board->maxage > 0 && ($post->post_timestamp + ($board->maxage * 3600)) < (time() + 7200 ) ) || ($post->post_delete_time > 0 && $post->post_delete_time <= (time() + 7200)))}
			<span class="oldpost">
				{t "Marked for deletion (old)"}
			</span>
			<br />
		{/if}
		{if $post->post_parent eq 0}
			<div id="replies{$post->post_id}{$board->board_name}">
			{if $post->replies}
				<span class="omittedposts">
					{if $post->post_stickied eq 0}
						{$post->replies} 
						{if $post->replies eq 1}
							{t "Post" lower="yes"} 
						{else}
							{t "Posts" lower="yes"} 
						{/if}
					{else}
						{$post->replies}
						{if $post->replies eq 1}
							{t "Post" lower="yes"} 
						{else}
							{t "Posts" lower="yes"} 
						{/if}
					{/if}
					{if $post->images > 0}
						{t "and"} {$post->images}
						{if $post->images eq 1}
							{t "Image" lower="yes"} 
						{else}
							{t "Images" lower="yes"} 
						{/if}
					{/if}
					{t "omitted"}. {t "Click Reply to view."}
					</span>
				{/if}
			{else}
				</td>
			</tr>
		</tbody>
		</table>
		
		{/if}
	{/foreach}
			</div>
			</div>
		{if $locale eq 'he'}
			<br clear="right" />
		{else}
			<br clear="left" />
		{/if}
		<hr />
{/foreach}
