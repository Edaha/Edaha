{% extends "board/global/wrapper.tpl" %}
{% block extrajs %}
  {{parent()}}
  kusaba.thread = {{replythread}};
{% endblock %}

{% block boardcontent %}
  <div id="postarea">
    <a id="postbox"></a>
    <div id="postform">
      <form name="postform" id="posting_form" action="{{ kxEnv("paths:script:path") }}/index.php?app=core&module=post&section=post" method="post" enctype="multipart/form-data"
      {% spaceless %}
        {%if board.board_enablecaptcha == 1 %}
          onsubmit="return checkcaptcha('postform');"
        {% endif %}
        >
      {% endspaceless %}
      <input type="hidden" name="board" value="{{board.board_name}}" />
      <input type="hidden" name="replythread" value="{{replythread}}" />
      {% if board.board.board_max_upload_size > 0 %}
        <input type="hidden" name="MAX_FILE_SIZE" value="{{board.board_max_size}}" />
      {% endif %}
      <input type="text" name="email" size="28" maxlength="75" value="" style="display: none;" />
      <ol>
      {% if board.board_forcedanon != 1 %}
        <li>
          <label for="name">{% trans "Name" %}</label>
          <input type="text" id="name" name="name" size="28" maxlength="75" accesskey="n" />
        </li>
      {% endif %}
        <li>
          <label for="email">{% trans "Email" %}</label>
          <input type="text" id="email" name="em" size="28" maxlength="75" accesskey="e" />
        </li>
      {% if board.board_enablecaptcha == 1 %}
        <li>
          <a href="#" onclick="javascript:document.getElementById('captchaimage').src = '{{ kxEnv("paths:cgi:path") }}/captcha.php?' + Math.random();return false;"><img id="captchaimage" src="{{ kxEnv("paths:cgi:path") }}/captcha.php" border="0" width="90" height="25" alt="Captcha image"></a>
          <input type="text" name="captcha" size="28" maxlength="10" accesskey="c" />
        </li>
      {% endif %}
        <li>
          <label for="subject">{% trans "Subject" %}</label>
          {% strip %}<input type="text" id="subject" name="subject" size="35" maxlength="75" accesskey="s" />&nbsp;
            <input type="submit" value="
            {% if kxEnv("extra:quickreply") and replythread == 0 %}
              {% trans "Submit" %}" accesskey="z" />
              <span id="posttypeindicator">&nbsp;({% trans "new thread" %})</span>
            {% elseif kxEnv("extra:quickreply") and replythread != 0 %}
              {% trans "Reply" %}" accesskey="z" />
              <span id="posttypeindicator">&nbsp;({% trans %}reply to {{replythread}}{% endtrans %})</span>
            {% else %}
              {% trans "Submit" %}" accesskey="z" />
          {% endif %}
        </li>{% endstrip %}
        <li>
          <label for="message" id="message_label">{% trans "Message" %}</label>
          <textarea id="message" name="message" cols="48" rows="4" accesskey="m"></textarea>
        </li>
      {% if board.board_upload_type == 0 or board.board_upload_type == 1 or board.board_upload_type == 2 %}
        {% if board.board_max_files > 1 and replythread != 0 %}
          {% for i in range(1,board.board_max_files) %}
            <li id="file{{ i }}"{% if not loop.first %} style="display:none"{% endif %}>
              <label for="file{{ i }}">{% trans "File" %} {{ i }}</label>
              <input{% if not loop.last %} onchange="$('#file{{ i + 1}}').show()"{% endif %} id="file{{ i }}" type="file" name="imagefile[]" size="35" accesskey="f" />
              {% if loop.first and replythread == 0 and board.board_enablenofile == 1 %}
                <input type="checkbox" name="nofile" id="nofile" accesskey="q" />[<span id="nofile"> {% trans "No File" %}]</span>
              {% endif %}
            </li>
          {% endfor %}
        {% else %}
          <li>
            <label for="file">{% trans "File" %}</label>
            <input id="file" type="file" name="imagefile[]" size="35" accesskey="f" />
            {% if replythread == 0 and board.board_enablenofile == 1 %}
              [<input type="checkbox" name="nofile" id="nofile" accesskey="q" /><span id="nofile"> {% trans "No File" %}</span>]
            {% endif %}
          </li>
        {% endif %}
      {% endif %}
      {% if (board.board_upload_type == 1 or board.board_upload_type == 2) and board.board_embeds_allowed != '' %}
        <li>
          <label for="embed">{% trans "Embed" %}</label>	
          <input id="embed" type="text" name="embed" size="28" maxlength="75" accesskey="e" />&nbsp;<select id="embedtype" name="embedtype">
          {% for embed in embeds %}
            {% if embed.filetype in board.board_embeds_allowed %}
              <option value="{{embed.name|lower}}">{{embed.name}}</option>
            {% endif %}
          {% endfor %}
          </select>&nbsp;
          <a id="embedhelp" href="#postbox" onclick="window.open('{{ kxEnv("paths:main:path") }}/embedhelp.php','embedhelp','toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=300,height=210');return false;">Help</a>
        </li>
      {% endif %}
        <li>
          <label for="password">{% trans "Password" %}</label>
          <input id="password" type="password" name="postpassword" size="8" accesskey="p" /><small>({% trans "for post and file deletion" %})</small>
        </li>
      </ol>
      <div id="rules">
        <ul style="margin-left: 0; margin-top: 0; margin-bottom: 0; padding-left: 0;">
          <li>{% trans "Supported file types are" %}:
          {% if board.board_filetypes_allowed != '' %}
            {% for filetype in board.board_filetypes_allowed %}
              {{filetype|upper}}{% if not loop.last %}, {% endif %}
            {% endfor %}
          {% else %}
            {% trans "None" %}
          {% endif %}
          </li>
          <li>{% trans "Maximum file size allowed is" %} {{(board.board.board_max_upload_size/1024)|round}} KB.</li>
          {% set thumbw %}{{kxEnv("images:thumbw")}}{% endset %}
          {% set thumbh %}{{kxEnv("images:thumbh")}}{% endset %}
          {% set uniqueposts %}{{board.board_uniqueposts}}{% endset %}
          <li>{% trans %}Images greater than {{thumbw}}x{{thumbh}} pixels will be thumbnailed.{% endtrans %}</li>
          <li>{% trans %}Currently {{uniqueposts}} unique user posts.{% endtrans %}
          {% if board.board_enablecatalog == 1 %} 
            <a href="{{ kxEnv("paths:boards:path") }}/{{board.board_name}}/catalog.html">{% trans "View catalog" %}</a>
          {% endif %}
          </li>
        </ul>
        {% if kxEnv("extra:blotter") and blotter %}
          <br />
          <ul style="margin-left: 0; margin-top: 0; margin-bottom: 0; padding-left: 0;">
          <li style="position: relative;">
            <span style="color: red;">
          {% trans "Blotter updated" %}: {{blotter_updated|date("d/m/y")}}
          </span>
            <span style="color: red;text-align: right;position: absolute;right: 0px;">
              <a href="#" onclick="javascript:toggleblotter(true);return false;">{% trans "Show/Hide" %}</a> <a href="{{ kxEnv("paths:main:path") }}/blotter.php">{% trans "Show All" %}</a>
            </span>
          </li>
          {{blotter}}
          </ul>
          <script type="text/javascript"><!--
          if ($.cookie('ku_showblotter') == '1') {
            toggleblotter(false);
          }
          --></script>
        {% endif %}
    </div>
    </form>
    </div>

  <hr />
  {% if topads %}
    <div class="content notice">
      <center> 
        {{topads}}
      </center>
    </div>
    <hr />
  {% endif %}
  </div>
  <script type="text/javascript"><!--
    kusaba.set_inputs("postform");
    //--></script>
{% endblock %}