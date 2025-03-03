{% extends "global_wrapper.tpl" %}

{% block title %}Manage - Log In{% endblock %}
{% block extrajs %}
  {% verbatim %}
    $(document).ready(function() {
      if ( top != self ) {
          top.location.href = window.location.href;
      }
      document.managelogin.username.focus();
    });
  {% endverbatim %}
{% endblock %}
{% block css %}
  {{ parent() }}
  <link rel="stylesheet" type="text/css" media='screen' href="{{ kxEnv("paths:boards:path") }}/public/css/manage.css">
{% endblock %}
{% block content %}
<form name='managelogin' action='{{ kxEnv("paths:script:path") }}/manage.php?app=core&amp;module=login&amp;section=login&amp;do=login-validate' method='post'>
<input type='hidden' name='qstring' id='qstring' value='{{query_string}}' />
<div id='login'>{% if message %} <div id='login_error'>{{ message }}</div>{% endif %}	<div id='login_controls'>
		<label for='username'>Username</label>
		<input type='text' size='20' id='username' name='username' value=''>

		
		<label for='password'>Password</label>
		<input type='password' size='20' id='password' name='password' value=''>	</div>
	<div id='login_submit'>
		<input type='submit' class='button' value="Log In" />
	</div>
</div>
</form>	
{% endblock %}
