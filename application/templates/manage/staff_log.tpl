{extends "wrapper.tpl"}

{block "heading"}{t "Moderator Log"}{/block}

{block "content"}
<table class="log">
  <caption>{t "Recent moderator actions"}{if $.get.view} <a href="{$base_url}app=core&amp;module=staff&amp;section=staff&amp;do=log">[Return]</a>{/if}</caption>
  <col class="col1" /><col class="col2" /><col class="col3" />
  <thead>
    <tr>
      <th>{t User}</th>
      <th>{t Time}</th>
      <th>{t Action}</th>
    </tr>
  </thead>
  <tbody>
    {foreach item=action from=$modlog}
    <tr>
      <td>{$action->user}</td>
      <td>{date_format $action->timestamp "%b %d, %Y %H:%M"}</td>
      <td>{$action->entry}</td>
    </tr>
    {/foreach}
  </tbody>
</table>
<br />

{if !$.get.view}
<table class="log">
  <col class="col1" /><col class="col2" /><col class="col3" />
  <thead>
    <tr>
      <th>{t User}</th>
      <th>{t "Actions Performed"}</th>
      <th>{t "View all"}</th>
    </tr>
  </thead>
  <tbody>
    {foreach item=user from=$staff}
    <tr>
      <td>{$user->user_name}</td>
      <td>{$user->total_actions}</td>
      <td>
        <a href="{$base_url}app=core&amp;module=staff&amp;section=staff&amp;do=log&amp;view={$user->user_id}">
          {t View}
        </a>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
{/if}
{/block}