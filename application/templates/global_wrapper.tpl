<!DOCTYPE html>
<html{{ htmloptions }} lang="en">
<head>
<title>{% block title %}{% kxEnv "site:name" %}{% endblock %}</title>
<link rel="shortcut icon" href="{% kxEnv "paths:main:path" %}/favicon.ico" />
{% block css %}
{% if locale == "ja" %}
	{% raw %}
	<style type="text/css">
		*{
			font-family: IPAMonaPGothic, Mona, 'MS PGothic', YOzFontAA97 !important;
			font-size: 1em;
		}
	</style>
	{% endraw %}
{% endif %}
{% endblock %}
{% block extrahead %}{% endblock %}
{% if locale != 'en' %}
  <link rel="gettext" type="application/x-po" href="{% kxEnv "paths:main:path" %}/inc/lang/{{locale}}/LC_MESSAGES/kusaba.po" />
{% endif %}
  <script type="text/javascript" src="{% kxEnv "paths:main:path" %}/lib/javascript/gettext.js"></script>
  <script type="text/javascript" src="{% kxEnv "paths:main:path" %}/lib/javascript/jquery-1.4.2.min.js"></script>
  <script type="text/javascript">
    kusaba = {% raw %}{}{% endraw %};
    kusaba.cgipath = '{% kxEnv "paths:cgi:path" %}';
    kusaba.webpath = '{% kxEnv "paths:main:path" %}';
    {% block extrajs %}{% endblock %}
  </script>
  <script type="text/javascript" src="{% kxEnv "paths:main:path" %}/lib/javascript/kusaba.js"></script>
</head>
<body>
{% block content %}{% endblock %}
</body>
</html>