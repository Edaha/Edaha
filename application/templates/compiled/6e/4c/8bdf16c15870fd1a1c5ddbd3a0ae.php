<?php

/* manage/staff_show.tpl */
class __TwigTemplate_6e4c8bdf16c15870fd1a1c5ddbd3a0ae extends Twig_Template
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

        // line 1
        $context['macros'] = $this->env->loadTemplate("manage/macros.tpl", true);
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 5
    public function block_heading($context, array $blocks = array())
    {
        echo _gettext("Manage Staff");    }

    // line 7
    public function block_managecontent($context, array $blocks = array())
    {
        // line 8
        echo "<form action=\"";
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "act", array(), "any", false, 8) == "edit")) {
            echo "edit&amp;id=";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_id", array(), "any", false, 8), "html");
        } else {
            echo "add";
        }
        echo "\" method=\"post\">
  ";
        // line 9
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "act", array(), "any", false, 9) == "edit")) {
            $context['formname'] = "Edit user";
        } else {
            $context['formname'] = "Add new user";
        }
        // line 10
        echo "  ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['macros']) ? $context['macros'] : null), "manageform", array("staff_add", (isset($context['formname']) ? $context['formname'] : null), true, array("Username" => array("id" => "username", "type" => "text", "value" => $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_name", array(), "any", false, 11)), "Password" => array("id" => "pwd1", "type" => "password", "value" => $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_password", array(), "any", false, 12)), "Reenter Password" => array("id" => "pwd2", "type" => "password", "value" => $this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_password", array(), "any", false, 13)), "Type" => array("id" => "type", "type" => "select", "value" => array("Administrator" => array("value" => 1, "selected" => (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "act", array(), "any", false, 16) == "edit") && ($this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_type", array(), "any", false, 16) == 1))), "Moderator" => array("value" => 2, "selected" => (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "act", array(), "any", false, 17) == "edit") && ($this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_type", array(), "any", false, 17) == 2))), "Janitor" => array("value" => 3, "selected" => (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "act", array(), "any", false, 18) == "edit") && ($this->getAttribute((isset($context['user']) ? $context['user'] : null), "user_type", array(), "any", false, 18) == 3)))))), ), "method", false, 10), "html");
        // line 22
        echo "
</form>
  
<br />  
<table class=\"users\" cellspacing=\"1px\">
  <col class=\"col1\" /> <col class=\"col2\" />
  <col class=\"col1\" /> <col class=\"col2\" />
  <col class=\"col1\" />
  <thead>
    <tr>
      <th>";
        // line 32
        echo _gettext("Username");        echo "</th>
      <th>";
        // line 33
        echo _gettext("Date Added");        echo "</th>
      <th>";
        // line 34
        echo _gettext("Last active");        echo "</th>
      <th>";
        // line 35
        echo _gettext("Usergroup");        echo "</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
  ";
        // line 40
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['staffmembers']) ? $context['staffmembers'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['member']) {
            // line 41
            echo "    <tr>
      <td>";
            // line 42
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_name", array(), "any", false, 42), "html");
            echo "</td>
      <td>";
            // line 43
            echo twig_escape_filter($this->env, $this->env->getExtension('DateFormat')->strftime($this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_add_time", array(), "any", false, 43), "%b %d, %Y %H:%M"), "html");
            echo "</td>
      <td>";
            // line 44
            echo twig_escape_filter($this->env, $this->env->getExtension('DateFormat')->strftime($this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_last_active", array(), "any", false, 44), "%b %d, %Y %H:%M"), "html");
            echo "</td>
      <td>";
            // line 45
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_type", array(), "any", false, 45), "html");
            echo "</td>
      <td>[ <a href=\"";
            // line 46
            echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
            echo "app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=edit&amp;id=";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_id", array(), "any", false, 46), "html");
            echo "\">";
            echo _gettext("Edit");            echo "</a> ] [ <a href=\"";
            echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
            echo "app=core&amp;module=staff&amp;section=staff&amp;do=show&amp;act=del&amp;id=";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['member']) ? $context['member'] : null), "user_id", array(), "any", false, 46), "html");
            echo "\">";
            echo _gettext("Delete");            echo "</a> ]</td>
    </tr>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['member'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 49
        echo "  </tbody>
</table>
";
    }

    public function getTemplateName()
    {
        return "manage/staff_show.tpl";
    }
}
