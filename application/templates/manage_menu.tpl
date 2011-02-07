{foreach key=key item=item from=$menu}
      <div class="section">
        <h2>{$key}</h2>
        <ul>
        {foreach item=section from=$item}
            <li><a href="{$base_url}{$section.section}&amp;{$section.url}">{$section.title}</a></li>
        {/foreach}
        </ul>
      </div>
{/foreach}