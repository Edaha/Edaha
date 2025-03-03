<!DOCTYPE html>
<html{{ htmloptions }} lang="en">
<head>
<title>{% block title %}{{ kxEnv("site:name") }}{% endblock %}</title>
<link rel="shortcut icon" href="{{ kxEnv("paths:main:path") }}/favicon.ico" />
{% block css %}
{% if locale == "ja" %}
	{% verbatim %}
	<style type="text/css">
		*{
			font-family: IPAMonaPGothic, Mona, 'MS PGothic', YOzFontAA97 !important;
			font-size: 1em;
		}
	</style>
	{% endverbatim %}
{% endif %}
{% endblock %}
{% if locale is defined and locale is not empty and locale != 'en' %}
  <link rel="gettext" type="application/x-po" href="{{ kxEnv("paths:main:path") }}/application/lib/lang/{{locale}}/LC_MESSAGES/kusaba.po" />
{% elseif kxEnv("misc:locale") != 'en' %}
  <link rel="gettext" type="application/x-po" href="{{ kxEnv("paths:main:path") }}/application/lib/lang/{{ kxEnv("misc:locale") }}/LC_MESSAGES/kusaba.po" />
{% endif %}
  <script type="text/javascript" src="{{ kxEnv("paths:main:path") }}/application/lib/javascript/gettext.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script type="text/javascript">
    kusaba = {% verbatim %}{}{% endverbatim %};
    kusaba.cgipath = '{{ kxEnv("paths:cgi:path") }}';
    kusaba.webpath = '{{ kxEnv("paths:main:path") }}';
    {% block extrajs %}{% endblock %}
  </script>
  <script type="text/javascript" src="{{ kxEnv("paths:main:path") }}/application/lib/javascript/kusaba.js"></script>
  {% block extrahead %}{% endblock %}
</head>
<body>
{% block content %}{% endblock %}
</body>
</html>