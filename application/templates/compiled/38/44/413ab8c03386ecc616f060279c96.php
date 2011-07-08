<?php

/* error.tpl */
class __TwigTemplate_3844413ab8c03386ecc616f060279c96 extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'css' => array($this, 'block_css'),
            'title' => array($this, 'block_title'),
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

    // line 3
    public function block_css($context, array $blocks = array())
    {
        // line 4
        echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo kxEnv::Get('kx:paths:main:path');
        echo "/public/css/menu_global.css\" />
  ";
        // line 5
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['styles']) ? $context['styles'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['style']) {
            // line 6
            echo "    <link rel=\"";
            if (((isset($context['style']) ? $context['style'] : null) != $this->env->getExtension('kxEnv')->kxEnvFilter("css:menudefault"))) {
                echo "alternate ";
            }
            echo "stylesheet\" type=\"text/css\" href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/public/css/site_";
            echo twig_escape_filter($this->env, (isset($context['style']) ? $context['style'] : null), "html");
            echo ".css\" title=\"";
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, (isset($context['style']) ? $context['style'] : null)), "html");
            echo "\" />
    <link rel=\"";
            // line 7
            if (((isset($context['style']) ? $context['style'] : null) != $this->env->getExtension('kxEnv')->kxEnvFilter("css:menudefault"))) {
                echo "alternate ";
            }
            echo "stylesheet\" type=\"text/css\" href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/public/css/sitemenu_";
            echo twig_escape_filter($this->env, (isset($context['style']) ? $context['style'] : null), "html");
            echo ".css\" title=\"";
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, (isset($context['style']) ? $context['style'] : null)), "html");
            echo "\" />
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['style'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 9
        echo "
  ";
        // line 10
        echo "
    <style type=\"text/css\">
    body {
      width: 100% !important;
    }
    </style>
  ";
        // line 16
        echo "
  ";
        // line 17
        echo twig_escape_filter($this->env, $this->renderParentBlock("css", $context, $blocks), "html");
        echo "
";
    }

    // line 20
    public function block_title($context, array $blocks = array())
    {
        echo kxEnv::Get('kx:site:name');
    }

    // line 22
    public function block_content($context, array $blocks = array())
    {
        // line 23
        echo "  <h1 style=\"font-size: 3em;\">";
        echo _gettext("Error");        echo "</h1>
  <br />
  <h2 style=\"font-size: 2em;font-weight: bold;text-align: center;\">
    ";
        // line 26
        echo twig_escape_filter($this->env, (isset($context['errormsg']) ? $context['errormsg'] : null), "html");
        echo "
  </h2>
    ";
        // line 28
        echo twig_escape_filter($this->env, (isset($context['errormsgext']) ? $context['errormsgext'] : null), "html");
        echo "
  <div style=\"text-align: center;width: 100%;position: absolute;bottom: 10px;\">
    <br />
    <div class=\"footer\" style=\"clear: both;\">
      ";
        // line 33
        echo "      <div class=\"legal\">\t- <a href=\"http://www.kusabax.org/\" target=\"_top\">Edaha 1.0</a> -
    </div>
  </div>
";
    }

    public function getTemplateName()
    {
        return "error.tpl";
    }
}
