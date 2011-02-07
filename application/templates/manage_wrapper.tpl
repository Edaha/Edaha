<html>
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
				<%CONTENT%>
      </div>

      <br style="clear: both;" />
    </div>
  </body>
</html>