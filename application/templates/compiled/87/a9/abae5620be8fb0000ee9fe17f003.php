<?php

/* manage/staff_log.tpl */
class __TwigTemplate_87a9abae5620be8fb0000ee9fe17f003 extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'heading' => array($this, 'block_heading'),
            'managecontent' => array($this, 'block_managecontent'),
        );
    }

    public function getParent(array $context)
    {
        if (null === $this->parent) {
            $this->parent = $this->env->loadTemplate("manage/wrapper.tpl");
        }

        return $this->parent;
    }

    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_heading($context, array $blocks = array())
    {
        echo _gettext("Moderator Log");    }

    // line 5
    public function block_managecontent($context, array $blocks = array())
    {
        // line 6
        echo "
<table class=\"log\">
  <caption>";
        // line 8
        echo _gettext("Recent moderator actions");        if ($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "view", array(), "any", false, 8)) {
            echo " <a href=\"";
            echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
            echo "app=core&amp;module=staff&amp;section=staff&amp;do=log\">[Return]</a>";
        }
        echo "</caption>
  <col class=\"col1\" /><col class=\"col2\" /><col class=\"col3\" />
  <thead>
    <tr>
      <th>";
        // line 12
        echo _gettext("User");        echo "</th>
      <th>";
        // line 13
        echo _gettext("Time");        echo "</th>
      <th>";
        // line 14
        echo _gettext("Action");        echo "</th>
    </tr>
  </thead>
  <tbody>
    ";
        // line 18
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['modlog']) ? $context['modlog'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['action']) {
            // line 19
            echo "    <tr>
      <td>";
            // line 20
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['action']) ? $context['action'] : null), "user", array(), "any", false, 20), "html");
            echo "</td>
      <td>";
            // line 21
            echo twig_escape_filter($this->env, $this->env->getExtension('DateFormat')->strftime($this->getAttribute((isset($context['action']) ? $context['action'] : null), "timestamp", array(), "any", false, 21), "%b %d, %Y %H:%M"), "html");
            echo "</td>
      <td>";
            // line 22
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['action']) ? $context['action'] : null), "entry", array(), "any", false, 22), "html");
            echo "</td>
    </tr>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['action'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 25
        echo "  </tbody>
</table>
<br />

";
        // line 29
        if ((!$this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "view", array(), "any", false, 29))) {
            // line 30
            echo "<table class=\"log\">
  <col class=\"col1\" /><col class=\"col2\" /><col class=\"col3\" />
  <thead>
    <tr>
      <th>";
            // line 34
            echo _gettext("User");            echo "</th>
      <th>";
            // line 35
            echo _gettext("Actions Performed");            echo "</th>
      <th>";
            // line 36
            echo _gettext("View all");            echo "</th>
    </tr>
  </thead>
  <tbody>
    ";
            // line 40
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context['staff']) ? $context['staff'] : null));
            foreach ($context['_seq'] as $context['_key'] => $context['user']) {
                // line 41
                echo "    <tr>
      <td>";
                // line 42
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_name", array(), "any", false, 42), "html");
                echo "</td>
      <td>";
                // line 43
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['user']) ? $context['user'] : null), "total_actions", array(), "any", false, 43), "html");
                echo "</td>
      <td>
        <a href=\"";
                // line 45
                echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
                echo "app=core&amp;module=staff&amp;section=staff&amp;do=log&amp;view=";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_id", array(), "any", false, 45), "html");
                echo "\">
          ";
                // line 46
                echo _gettext("View");                // line 47
                echo "        </a>
      </td>
    </tr>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 51
            echo "  </tbody>
</table>
";
        }
    }

    public function getTemplateName()
    {
        return "manage/staff_log.tpl";
    }
}
