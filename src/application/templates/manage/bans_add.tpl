{% import "manage/macros.tpl" as macros %}

{% extends "manage/wrapper.tpl" %}

{% block heading %}{% trans "Bans - Add" %}{% endblock %}

{% block managecontent %}

  <form action="{{ base_url }}app=core&amp;module=bans&amp;section=bans&amp;do=add&amp;action=post" method="post">
    {{ 
      macros.manageform(
        "bans",
        "Bans",
        true,
        { 
          'IP Address' : 
          { 
            'id' : 'ban_ip',
            'type' : 'text', 
            'desc' : "",
            'value' : entry.entry_name,
          },
          'Boards' : 
          {
            'id' : 'ban_boards',
            'type' : 'kx_boardlist',
            'sections': sections,
          },
          'Duration' : 
          { 
            'id' : 'ban_duration',
            'type' : 'text', 
            'desc' : "",
          },
          'Reason' : 
          { 
            'id' : 'ban_reason',
            'type' : 'text', 
            'desc' : "",
          },
          'Delete all posts from users' : 
          { 
            'id' : 'ban_deleteall',
            'type' : 'checkbox_single', 
            'desc' : "If checked, will delete all posts from the users who created the post (if Review Action is Delete)",
            'selected' : false,
          },
          'Allow Read' : 
          { 
            'id' : 'ban_allowread',
            'type' : 'checkbox_single', 
            'desc' : "If checked, will delete all posts from the users who created the post (if Review Action is Delete)",
            'selected' : false,
          },
          'Allow Appeal' : 
          { 
            'id' : 'ban_allow_appeal',
            'type' : 'checkbox_single', 
            'desc' : "",
          },
          'Notes' : 
          { 
            'id' : 'ban_notes',
            'type' : 'text', 
            'desc' : "",
          },
        },
      ) 
    }}
  </form>

{% endblock %}