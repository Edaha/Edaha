<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>{t "Edaha Management"}</title>
    <link href="{kxEnv paths:boards:path}/public/css/manage.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div class="header">
      <div class="herp">
        {t "Edaha Management"}
      </div>

      <br style="clear: both;" />
      <div class="login">
        {t "Logged in as <span class='strong'>%1</span>" arg1=$username} <a href="{$base_url}&amp;module=login&amp;do=logout">[ {t "Log Out"} ]</a>
      </div>
      <div class="tabs">
        <ul>
          <li class="{if !$current_app}selected{/if}"><a href="{$base_url}">{t "Main"}</a></li>
          <li class="{if $current_app eq "core"}selected{/if}"><a href="{$base_url}app=core&amp;module=site">{t "Site Management"}</a></li>
          <li class="{if $current_app eq "board"}selected{/if}"><a href="{$base_url}app=board">{t "Board Management"}</a></li>
          <li class="{if $current_app eq "apps"}selected{/if}"><a href="#">{t "Addons"}</a></li>
        </ul>
      </div>
    </div>
    
    <div class="main">

      <div class="menu">
 				<%MENU%>
      </div>
      
      <div class="content">
        <h1>{block "heading"}{/block}</h1>
        {if $notice_type and $notice}
        <div class="{$notice_type}">
          {$notice}
        </div>
        {/if}
        
        {block "content"}{/block}
      </div>

      <br style="clear: both;" />
    </div>
  </body>
</html>