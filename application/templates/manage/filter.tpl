{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Filters" %}{% endblock %}

{% block managecontent %}
  <form action="{{ base_url }}app=core&amp;module=filter&amp;section=filter&amp;do=add&amp;action=post" method="post">
    <fieldset id="bans">
      <legend>{% trans "Filters" %}</legend>
      
      <div class="bans_left">
        {{ macros.boardlist(sections) }}
      </div>
      
      <div class="bans_right">
        <!-- IP, Bantype, DeleteAll/AllowRead -->
        <label for="filter_word">{% trans "Word" %}:</label> <input type="text" name="filter_word" id="filter_word" /><br />
        <label for="filter_replace">{% trans "Replace With" %}:</label> <input type="text" name="filter_word" id="filter_word" /><br />
        <span class="desc">{% trans "Only valid if \"Replace word\" is checked as a punishment" %}</span><br />
        <label for="filter_replace">{% trans "Ban Duration" %}:</label> <input type="text" name="filter_replace" id="filter_replace" /><br />
        <span class="desc">{% trans "Only valid if \"Ban user\" is checked as a punishment" %}<br />
        {% trans "Enter number of seconds or values like \"1 year\" or \"Forever\"" %}</span><br />
        <label for="filter_regex">{% trans "Regular expression" %}:</label><input type="checkbox" name="filter_regex" id="filter_regex" /><br />
        <span class="desc">{% trans "Surround your filter with delimiters if you check this" %}</span><br />
        <label for="ban_submit">&nbsp;</label> <input type="submit" name="ban_submit" id="ban_submit" />
      </div>
    </fieldset>
  </form>

{% endblock %}