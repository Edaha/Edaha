<?php

/* manage/faq.tpl */
class __TwigTemplate_0d53bd904695500007b165351ac726aa extends Twig_Template
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
        echo _gettext("FAQ Management");    }

    // line 7
    public function block_managecontent($context, array $blocks = array())
    {
        // line 8
        echo "
<form action=\"";
        // line 9
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "app=core&amp;module=site&amp;section=front&amp;do=faq&amp;action=post\" method=\"post\">
  ";
        // line 10
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['macros']) ? $context['macros'] : null), "manageform", array("faq_post", "Add FAQ Entry", true, array("Heading" => array("id" => "subject", "type" => "text", "desc" => "Can not be left blank", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_subject", array(), "any", false, 11)), "Post" => array("id" => "message", "type" => "textarea", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_message", array(), "any", false, 12)), "Order" => array("id" => "order", "type" => "text", "desc" => "Can be left blank, however it will appear at the top of the list", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_order", array(), "any", false, 13))), ), "method", false, 10), "html");
        // line 15
        echo "
<input type=\"hidden\" id=\"edit\" name=\"edit\" value=\"";
        // line 16
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_id", array(), "any", false, 16), "html");
        echo "\" />
<input type=\"hidden\" id=\"type\" name=\"type\" value=\"1\" />
</form>
  
<br />  
<table class=\"users\" cellspacing=\"1px\">
  <col class=\"col1\" /> <col class=\"col2\" />
  <col class=\"col1\" /> <col class=\"col2\" />
  <thead>
    <tr>
      <th>";
        // line 26
        echo _gettext("Order");        echo "</th>
      <th>";
        // line 27
        echo _gettext("Heading");        echo "</th>
      <th>";
        // line 28
        echo _gettext("Message");        echo "</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
  ";
        // line 33
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['entries']) ? $context['entries'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['faq']) {
            // line 34
            echo "    <tr>
      <td>";
            // line 35
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['faq']) ? $context['faq'] : null), "entry_order", array(), "any", false, 35), "html");
            echo "</td>
      <td>";
            // line 36
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['faq']) ? $context['faq'] : null), "entry_subject", array(), "any", false, 36), "html");
            echo "</td>
      <td>";
            // line 37
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['faq']) ? $context['faq'] : null), "entry_message", array(), "any", false, 37), "html");
            echo "</td>
      <td>[ <a href=\"";
            // line 38
            echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
            echo "app=core&amp;module=site&amp;section=front&amp;do=faq&amp;action=edit&amp;id=";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['faq']) ? $context['faq'] : null), "entry_id", array(), "any", false, 38), "html");
            echo "\">";
            echo _gettext("Edit");            echo "</a> ] [ <a href=\"";
            echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
            echo "app=core&amp;module=site&amp;section=front&amp;do=faq&amp;action=del&amp;id=";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['faq']) ? $context['faq'] : null), "entry_id", array(), "any", false, 38), "html");
            echo "\">";
            echo _gettext("Delete");            echo "</a> ]</td>
    </tr>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['faq'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 41
        echo "  </tbody>
</table>
";
    }

    public function getTemplateName()
    {
        return "manage/faq.tpl";
    }
}
