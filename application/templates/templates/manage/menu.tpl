{% for module, items in menu %}
  {% for name, item in items %}
      <div class="section">
        <h2>{{name}}</h2>
        <ul>
        {% for section in item %}
            <li><a href="{{base_url}}app={{ constant('KX_CURRENT_APP')}}&amp;module={{module}}&amp;section={{section.section}}&amp;{{section.url}}">{{section.title}}</a></li>
        {% endfor %}
        </ul>
      </div>
  {% endfor %}
{% endfor %}