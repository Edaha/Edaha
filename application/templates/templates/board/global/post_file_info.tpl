{% macro fileinfo(post, id, link) %}
  {% if link and post.file_type.0 != 'jpg' and post.file_type.0 != 'gif' and post.file_type.0 != 'png' and post.videobox == '' %}
    <a {% if 'posts:newwindow'|kxEnv %}target="_blank" {% endif %}href="{{file_path}}/src/{{post.file_name.0}}.{{post.file_type.0}}">
  {% else %}
    <a href="{{file_path}}/src/{{post.file_name.0}}.{{post.file_type.0}}" id="expands_{{post.post_id}}_{{post.file_image_width.0}}_{{post.file_image_height.0}}_{{post.thumb_w.0}}_{{post.thumb_h.0}}">
  {% endif %}
  {% if post.post_id3[id].comments_html.artist.0 %}
  {{post.post_id3[id].comments_html.artist.0}} 
    {% if post.post_id3[id].comments_html.title.0 %}
      - 
    {% endif %}
    {% if post.post_id3[id].comments_html.title.0 %}
      {{post.post_id3[id].comments_html.title.0}} 
    {% endif %}
  {% else %}
    {{post.file_name.0}}.{{post.file_type.0}} 
  {% endif %}
  {% if link %}
    </a>
  {% endif %}
  - ({{post.file_size_formatted.0}}
  {% if post.post_id3[id].comments_html.bitrate or post.post_id3[id].audio.sample_rate %}
    {% if post.post_id3[id].audio.bitrate %}
      - {{(post.post_id3[id].audio.bitrate / 1000)|round}} kbps
      {% if post.post_id3[id].audio.sample_rate %}
        - 
      {% endif %}
    {% endif %}
    {% if post.post_id3[id].audio.sample_rate %}
      {{post.post_id3[id].audio.sample_rate / 1000}} kHz
    {% endif %}
  {% endif %}
  {% if post.file_image_width.0 and post.file_image_height.0 %}
    , {{post.file_image_width.0}}x{{post.file_image_height.0}}
  {% endif %}
  {% if post.file_original.0 and post.file_original.0 != post.file_name.0 %}
    , {{post.file_original.0}}.{{post.file_type.0}}
  {% endif %}
  )
  {% if post.post_id3[id].playtime_string %}
    {% trans "Length" %}: {{post.post_id3[id].playtime_string}}
  {% endif %}
{% endmacro %}
{% if post.file_name|length == 1 and (post.file_name.0 != '' or post.file_type.0 != '' ) and ((post.videobox == '' and post.file_name.0 != '') and post.file_name.0 != 'removed') %}
  {% strip %}
    <div class="file_size">
      {% if post.file_type.0 == 'mp3' %}
        {% trans "Audio" %}
      {% else %}
        {% trans "File" %}
      {% endif %}
      {{ _self.fileinfo(post, 0, true) }}
    </div>
  {% endstrip %}
  {% if 'display:thumbmsg'|kxEnv %}
    <span class="thumbnailmsg"> 
    {% if post.file_type.0 != 'jpg' and post.file_type.0 != 'gif' and post.file_type.0 != 'png' and post.videobox == '' %}
      {% trans "Extension icon displayed, click image to open file." %}
    {% else %}
      {% trans "Thumbnail displayed, click image for full size." %}
    {% endif %}
    </span>
  {% endif %}
{% endif %}
{% if post.file_name|length > 1 %}
  {% for fileskey,file in post.file_name %}
    {% if fileskey % 3 %}<br style="clear:both" />{% endif %}
    {% set fileurl %}{{file_path}}/src/{{post.file_name[fileskey]}}.{{post.file_type[fileskey]}}{% endset %}
    {% if post.file_name[fileskey] and ( post.file_type[fileskey] == 'jpg' or post.file_type[fileskey] == 'gif' or post.file_type[fileskey] == 'png') %}
      {% set thumburl %}{{file_path}}/thumb/{{post.file_name[fileskey]}}s.{{post.file_type[fileskey]}}{% endset %}
    {% elseif post.nonstandard_file[fileskey] %}
      {% set thumburl %}{{ post.nonstandard_file[fileskey] }}{% endset %}
    {% endif %}
    {% spaceless %}
      <div style="float:left">
        <span>
          <a href="{{ fileurl }}"
          {% if post.nonstandard_file[fileskey] %}
            {% if 'posts:newwindow'|kxEnv %} target="_blank" {% endif %}>
            {{post.file_type[fileskey]|upper}} ({{post.file_size_formatted[fileskey]}})
          {% else %}
            id="expandimg_{{post.post_id}}-{{fileskey}}_{{post.file_image_width[fileskey]}}_{{post.file_image_height[fileskey]}}_{{post.thumb_w[fileskey]}}_{{post.thumb_h[fileskey]}}">
              ({% if post.file_image_width[fileskey] > 0 and post.file_image_height[fileskey] > 0 %}
                {{post.file_image_width[fileskey]}}x{{post.file_image_height[fileskey]}}
              {% else %}
                {{post.file_size_formatted[fileskey]}}
              {% endif %})
          {% endif %}
          </a>
        </span>
        <br />
        <a {% if 'posts:newwindow'|kxEnv %}target="_blank"{% endif %} href="{{ fileurl }}">
          <span id="thumb_{{post.post_id}}-{{fileskey}}">
            <img src="{{ thumburl }}" alt="{{post.post_id}}" height="{{post.thumb_h[fileskey]}}" width="{{post.thumb_w[fileskey]}}" title="{{ _self.fileinfo(post, fileskey) }}" />
          </span>
        </a>
      </div>
    {% endspaceless %}
    {% if loop.last %}
      <br style="clear:both" />
    {% endif %}
  {% endfor %}
{% elseif post.file_name|length == 1 %} 
  {% if post.file_name.0 == 'removed' %}
    <div class="nothumb">
      {% trans "File<br />Removed" %}
    </div>
  {% else %}
    {% if post.videobox == '' and post.file_name.0 and ( post.file_type.0 == 'jpg' or post.file_type.0 == 'gif' or post.file_type.0 == 'png') %}
      <div id="thumb_{{post.post_id}}" class="post_thumb">
        <a {% if 'posts:newwindow'|kxEnv %}target="_blank"{% endif %} href="{{file_path}}/src/{{post.file_name.0}}.{{post.file_type.0}}"><img src="{{file_path}}/thumb/{{post.file_name.0}}s.{{post.file_type.0}}" alt="{{post.post_id}}" class="thumb" height="{{post.thumb_h.0}}" width="{{post.thumb_w.0}}" /></a>
      </div>
    {% elseif post.nonstandard_file %}
      <div id="thumb_{{post.post_id}}" class="post_thumb">
      <a {% if 'posts:newwindow'|kxEnv %}target="_blank"{% endif %} href="{{file_path}}/src/{{post.file_name.0}}.{{post.file_type.0}}"><img src="{{post.nonstandard_file.0}}" alt="{{post.post_id}}" class="thumb" height="{{post.thumb_h.0}}" width="{{post.thumb_w.0}}" /></a></div>
    {% endif %}
  {% endif %}
{% endif %}