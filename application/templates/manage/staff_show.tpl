{extends "wrapper.tpl"}

{block "heading"}{t "Manage Staff"}{/block}

{block "content"}
<form action="{$base_url}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act={if $.get.act eq 'edit'}edit&amp;id={$user->user_id}{else}add{/if}" method="post">
  <fieldset id="staff_add">
    <legend>{if $.get.act eq 'edit'}{t "Edit user"}{else}{t "Add new user"}{/if}</legend>
    
    <label for="username">{t Username}:</label>
      <input type="text" id="username" name="username" {if $.get.act eq 'edit'}value="{$user->user_name}" disabled="disabled"{/if} /><br />
    <label for="pwd1">{t Password}:</label>
      <input type="password" id="pwd1" name="pwd1" /><br />
    <label for="pwd2">{t "Reenter Password"}:</label>
      <input type="password" id="pwd2" name="pwd2" /><br />
    <label for="type">{t Type}:</label>
      <select id="type" name="type">
        <option value="1"{if $.get.act eq 'edit' and $user->user_type eq 1} selected="selected"{/if}>{t Administrator}</option>
        <option value="2"{if $.get.act eq 'edit' and $user->user_type eq 2} selected="selected"{/if}>{t Moderator}</option>
        <option value="0"{if $.get.act eq 'edit' and $user->user_type eq 0} selected="selected"{/if}>{t Janitor}</option>
      </select><br />
    
    <label for="submit">&nbsp;</label>
      <input type="submit" id="submit" value="{t Submit}" />
  </fieldset>
</form>
  
<br />  
<table class="users" cellspacing="1px">
  <col class="col1" /> <col class="col2" />
  <col class="col1" /> <col class="col2" />
  <col class="col1" />
  <thead>
    <tr>
      <th>{t Username}</th>
      <th>{t "Date Added"}</th>
      <th>{t "Last active"}</th>
      <th>{t Usergroup}</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
  {foreach item=member from=$staffmembers}
    <tr>
      <td>{$member->user_name}</td>
      <td>{date_format $member->user_add_time "%b %d, %Y %H:%M"}</td>
      <td>{date_format $member->user_last_active "%b %d, %Y %H:%M"}</td>
      <td>{$member->user_type}</td>
      <td>[ <a href="{$base_url}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=edit&amp;id={$member->user_id}">{t Edit}</a> ] [ <a href="{$base_url}app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=del&amp;id={$member->user_id}">Delete</a> ]</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/block}