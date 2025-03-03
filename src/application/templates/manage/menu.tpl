{% for module, items in menu %}
  {% for name, item in items %}
        <section>
          <h2>{{name}}</h2>
          <ul>
          {% for section in item %}
            {% set module_url = (section.module != '') ? section.module : module %}
            {% set app_url    = (section.app    != '') ? section.app    : constant('KX_CURRENT_APP') %}
            <li><a href="{{base_url}}app={{ app_url }}&amp;module={{module_url}}&amp;section={{section.section}}&amp;{{section.url}}">{{section.title}}</a></li>
          {% endfor %}
          </ul>
        </section>
  {% endfor %}
{% endfor %}