<?php

/* manage/board.tpl */
class __TwigTemplate_994809e3f61f0403ba9ee99d40c8fe13 extends Twig_Template
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
        echo _gettext("Board Management");    }

    // line 7
    public function block_managecontent($context, array $blocks = array())
    {
        // line 8
        echo "
<form action=\"";
        // line 9
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "app=board&amp;module=board&amp;do=board&amp;action=post\" method=\"post\">
  ";
        // line 10
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['macros']) ? $context['macros'] : null), "manageform", array("boards", "Add Board", true, array("Board Name" => array("id" => "name", "type" => "text", "desc" => "The directory of the board. <b>Only put in the letter(s) of the board directory, no slashes!</b>
", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_name", array(), "any", false, 12)), "Board Description" => array("id" => "description", "type" => "text", "desc" => "The name of the board", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_description", array(), "any", false, 13)), "First Post ID" => array("id" => "start", "type" => "text", "desc" => "The first post of this board will recieve this ID.", "value" => $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_start", array(), "any", false, 14))), ), "method", false, 10), "html");
        // line 16
        echo "
<input type=\"hidden\" id=\"del\" name=\"del\" value=\"";
        // line 17
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "entry_id", array(), "any", false, 17), "html");
        echo "\" />
<input type=\"hidden\" name=\"directory\" id=\"directory\" value=\"\" />
</form>

<br />
";
        // line 22
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['macros']) ? $context['macros'] : null), "manageform", array("boards", "Delete Board", true, array("Boards" => array("id" => "boards", "type" => "select", "value" => array())), ), "method", false, 22), "html");
        // line 29
        echo "
";
    }

    public function getTemplateName()
    {
        return "manage/board.tpl";
    }
}
