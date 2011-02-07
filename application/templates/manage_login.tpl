<html>
<head>
	<title>Manage - Log In</title>
  <script type='text/javascript'>
  {literal}
    if ( top != self ) {
      top.location.href = window.location.href;
    }
    function sf(){
      document.managelogin.username.focus();
    }
  {/literal}
  </script>
  <link rel="stylesheet" type="text/css" media='screen' href="{kxEnv paths:boards:path}/public/css/manage.css">
</head>
<body onload="sf();">
<form action='{kxEnv paths:script:path}/manage.php?app=core&amp;module=login&amp;do=login-validate' method='post'>
<input type='hidden' name='qstring' id='qstring' value='{$query_string}' />
<div id='login'>{if $message} <div id='login_error'>{$message}</div>{/if}	<div id='login_controls'>
		<label for='username'>Username</label>
		<input type='text' size='20' id='username' name='username' value=''>

		
		<label for='password'>Password</label>
		<input type='password' size='20' id='password' name='password' value=''>	</div>
	<div id='login_submit'>
		<input type='submit' class='button' value="Log In" />
	</div>
</div>
</form>	
</body>