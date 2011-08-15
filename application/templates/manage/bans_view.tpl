{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Bans - View" %}{% endblock %}

{% block managecontent %}

  <form action="{{ base_url }}app=core&amp;module=site&amp;section=bans&amp;do=view&amp;action=filter" method="post">
    <fieldset name="bans_filter">
      <legend>{% trans "Filter" %}</legend>
      <label for="filter1input">Filter 1:</label>  
      <input type="text" name="filter1input" id="filter1input" /> &nbsp;
      <select name="filter1select" id="filter1select">
        <option value="opt1">Option 1</option>
        <option value="opt2">Option 2</option>
      </select>
      <br />
      
      <label for="filter2input">Filter 2:</label>  
      <input type="text" name="filter2input" id="filter2input" /> &nbsp;
      <select name="filter2select" id="filter2select">
        <option value="opt1">Option 1</option>
        <option value="opt2">Option 2</option>
      </select>
      <br />
      
      <label for="filter_submit">&nbsp;</label>
      <input type="submit" name="filter_submit" id="filter_submit" />
      
    </fieldset>
    {# macros.manageform("bans_filter", "Filter", true, 
                         { 'Filter 1' : { 'id' : 'filter1', 'type' : 'text' } }
                        )
    #}                         
  </form>
  
  <p>
    <form action="{{ base_url }}app=core&amp;module=site&amp;section=bans&amp;do=view&amp;action=delete" method="post">
      <table class="users" cellspacing="1px">
        <col class="col1" /><col class="col2" />
        <col class="col1" /><col class="col2" />
        <col class="col1" /><col class="col2" />
        <col class="col1" /><col class="col2" />
        
        <thead>
          <tr>
            <th>{% trans "IP Address" %}</th>
            <th>{% trans "Banned from" %}</th>
            <th>{% trans "Reason" %}</th>
            <th>{% trans "Staff Note" %}</th>
            <th>{% trans "Added On" %}</th>
            <th>{% trans "Expires On" %}</th>
            <th>{% trans "Added By" %}</th>
            <th>{% trans "Delete" %}</th>
          </tr>
        </thead>
        
        {# Need to finalize banlist table and then finish this. For now, fake data #}
        <tbody>
          <tr>
            <td>192.168.2.105</td>
            <td>All Boards</td>
            <td>Spamming</td>
            <td>Constant Spammer</td>
            <td>August 14, 2011, 8:30PM</td>
            <td>December 21, 2012, 12:00AM</td>
            <td>admin</td>
            <td><input type="checkbox" name="delete_bans[]" id="delete_bans[]" /></td>
          </tr>
        </tbody>
      </table>
    </form>
  </p>
{% endblock %}