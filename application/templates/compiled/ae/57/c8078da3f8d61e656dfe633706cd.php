<?php

/* manage/wrapper.tpl */
class __TwigTemplate_ae57c8078da3f8d61e656dfe633706cd extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'css' => array($this, 'block_css'),
            'heading' => array($this, 'block_heading'),
            'managecontent' => array($this, 'block_managecontent'),
            'content' => array($this, 'block_content'),
        );
    }

    public function getParent(array $context)
    {
        if (null === $this->parent) {
            $this->parent = $this->env->loadTemplate("global_wrapper.tpl");
        }

        return $this->parent;
    }

    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_title($context, array $blocks = array())
    {
        echo _gettext("Edaha Management");    }

    // line 3
    public function block_css($context, array $blocks = array())
    {
        // line 4
        echo "  <link href=\"";
        echo kxEnv::Get('kx:paths:boards:path');
        echo "/public/css/manage.css\" rel=\"stylesheet\" type=\"text/css\" />
  ";
        // line 5
        echo twig_escape_filter($this->env, $this->renderParentBlock("css", $context, $blocks), "html");
        echo "
";
    }

    // line 35
    public function block_heading($context, array $blocks = array())
    {
    }

    // line 42
    public function block_managecontent($context, array $blocks = array())
    {
    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
        // line 8
        echo "    <div class=\"header\">
      <div class=\"herp\">
        ";
        // line 10
        echo _gettext("Edaha Management");        // line 11
        echo "      </div>

       <br style=\"clear: both;\" />
      <div class=\"login\">
        ";
        // line 15
        echo _gettext("Logged in as");        echo "<span class='strong'>";
        echo twig_escape_filter($this->env, (isset($context['name']) ? $context['name'] : null), "html");
        echo "</span> [<a href=\"";
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "&amp;module=login&amp;do=logout\">";
        echo _gettext("Log Out");        echo "</a>]
      </div>

      <div class=\"tabs\">
        <ul>
          <li class=\"";
        // line 20
        if ((!(isset($context['current_app']) ? $context['current_app'] : null))) {
            echo "selected";
        }
        echo "\"><a href=\"";
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "\">";
        echo _gettext("Main");        echo "</a></li>
          <li class=\"";
        // line 21
        if (((isset($context['current_app']) ? $context['current_app'] : null) == "core")) {
            echo "selected";
        }
        echo "\"><a href=\"";
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "app=core&amp;module=site&section=front&do=news\">";
        echo _gettext("Site Management");        echo "</a></li>
          <li class=\"";
        // line 22
        if (((isset($context['current_app']) ? $context['current_app'] : null) == "board")) {
            echo "selected";
        }
        echo "\"><a href=\"";
        echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
        echo "app=board&amp;module=board&section=board\">";
        echo _gettext("Board Management");        echo "</a></li>
          <li class=\"";
        // line 23
        if (((isset($context['current_app']) ? $context['current_app'] : null) == "apps")) {
            echo "selected";
        }
        echo "\"><a href=\"#\">";
        echo _gettext("Addons");        echo "</a></li>
        </ul>
      </div>
    </div>
    
    <div class=\"main\">

      <div class=\"menu\">
 \t\t\t\t";
        // line 31
        $this->env->loadTemplate("manage/menu.tpl")->display($context);
        // line 32
        echo "      </div>
      
      <div class=\"content\">
        <h1>";
        // line 35
        $this->displayBlock('heading', $context, $blocks);
        echo "</h1>
        ";
        // line 36
        if (((isset($context['notice_type']) ? $context['notice_type'] : null) && (isset($context['notice']) ? $context['notice'] : null))) {
            // line 37
            echo "        <div class=\"";
            echo twig_escape_filter($this->env, (isset($context['notice_type']) ? $context['notice_type'] : null), "html");
            echo "\">
          ";
            // line 38
            echo twig_escape_filter($this->env, (isset($context['notice']) ? $context['notice'] : null), "html");
            echo "
        </div>
        ";
        }
        // line 41
        echo "        
        ";
        // line 42
        $this->displayBlock('managecontent', $context, $blocks);
        // line 43
        echo "      </div>

      <br style=\"clear: both;\" />
    </div>
";
    }

    public function getTemplateName()
    {
        return "manage/wrapper.tpl";
    }
}
