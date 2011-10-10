{% extends "manage/wrapper.tpl" %}

{% block heading %}Statistics{% endblock %}

{% block managecontent %}
<table class="stats">

  <col class="col1" /><col class="col2" /><col class="col1" /><col class="col2" />
  <thead>
    <tr>
      <th colspan="4">Statistics</th>
    </tr>
  </thead>
  <tbody>
    <tr>

      <td>Installation Date: </td>
      <td class="strong">{{stats.installation_date}}</td>
      <td>Database Type: </td>
      <td class="strong">{{dbtype}}</td>
    </tr>
    <tr>
      <td>Edaha Version: </td>

      <td class="strong">{{stats.edahaversion}} {% if newversion %}<a href="#" class="warning" title="{trans "A new version of Edaha is available"}">[ ! ]</a>{% endif %}</td>
      <td>Database Version: </td>
      <td class="strong">{{dbversion}}</td>
    </tr>
    <tr>
      <td>Number of Boards: </td>

      <td class="strong">{{stats.numboards}}</td>
      <td>Database Size: </td>
      <td class="strong">{{dbsize}}</td>
    </tr>
    <tr>
      <td>Total Posts: </td>

      <td class="strong">{{stats.totalposts}}</td>
      <td>PHP Version: </td>
      <td class="strong">{{constant('PHP_VERSION')}}</td>
    </tr>
  </tbody>
</table>
{% endblock %}