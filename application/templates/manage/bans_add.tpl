{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Bans - Add" %}{% endblock %}

{% block managecontent %}

  <form action="{{ base_url }}app=core&amp;module=site&amp;section=bans&amp;do=add&amp;action=post" method="post">
    <fieldset id="bans">
      <legend>{% trans "Bans" %}</legend>
      
      <div class="bans_left">
        <select name="boards[]" class="multiple" multiple="multiple">
{% for section in sections %}
          <optgroup label="{{ section.name }}">
{% for board in section.boards %}
            <option value="{{ board.board_name }}">{{ board.board_desc }}</option>
{% endfor %}
          </optgroup>
{% endfor %}
        </select>
      </div>
      
      <div class="bans_right">
        <!-- IP, Bantype, DeleteAll/AllowRead -->
        <label for="ban_ip">{% trans "IP" %}:</label> <input type="text" name="ban_ip" id="ban_ip" />
        <label for="ban_deleteall">{% trans "Delete all posts" %}?</label> <input type="checkbox" name="ban_deleteall" id="ban_deleteall" /><br />
        <label for="ban_type">{% trans "Type" %}:</label> <input type="text" name="ban_type" id="ban_type" />
        <label for="ban_allowreadl">{% trans "Allow read" %}?</label> <input type="checkbox" name="ban_allowread" id="ban_allowread" checked="checked" /><br />
        <!-- Duration, Appeal -->
        <label for="ban_duration">{% trans "Duration" %}:</label> <input type="text" name="ban_duration" id="ban_duration" />
        <label for="ban_reason">{% trans "Reason" %}:</label> <input type="text" name="ban_reason" id="ban_reason" /><br />
        <label for="ban_appeal">{% trans "Appeal" %}:</label> <input type="text" name="ban_appeal" id="ban_appeal" />
        <label for="ban_note">{% trans "Note" %}:</label> <input type="text" name="ban_note" id="ban_note" /><br />
        <label for="ban_submit">&nbsp;</label> <input type="submit" name="ban_submit" id="ban_submit" />
      </div>
    </fieldset>
  </form>

{% endblock %}