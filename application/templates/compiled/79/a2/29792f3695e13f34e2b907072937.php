<?php

/* index.tpl */
class __TwigTemplate_79a229792f3695e13f34e2b907072937 extends Twig_Template
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

    // line 2
    public function block_css($context, array $blocks = array())
    {
        // line 3
        echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo kxEnv::Get('kx:paths:main:path');
        echo "/public/css/site_burichan.css\" />
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 4
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
        echo "\t<h1>";
        echo kxEnv::Get('kx:site:name');
        echo "</h1>
";
        // line 24
        if (($this->env->getExtension('kxEnv')->kxEnvFilter("site:slogan") != "")) {
            // line 25
            echo "\t<h3>";
            echo kxEnv::Get('kx:site:slogan');
            echo "</h3>
";
        }
        // line 27
        echo "
\t<div class=\"menu\" id=\"topmenu\">
\t\t<ul>
\t\t\t";
        // line 30
        ob_start();
        echo "<li class=\"";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 30) == "")) {
            echo "current ";
        } else {
            echo "tab ";
        }
        echo "first\">";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 30) != "")) {
            echo "<a href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/index.php\">";
        }
        echo _gettext("News");        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 30) != "")) {
            echo "</a>";
        }
        echo "</li>";
        echo implode(array_map('ltrim', explode("\n", ob_get_clean())));
        // line 31
        echo "\t\t\t";
        ob_start();
        echo "<li class=\"";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 31) == "faq")) {
            echo "current";
        } else {
            echo "tab";
        }
        echo "\">";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 31) != "faq")) {
            echo "<a href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/index.php?p=faq\">";
        }
        echo _gettext("FAQ");        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 31) != "faq")) {
            echo "</a>";
        }
        echo "</li>";
        echo implode(array_map('ltrim', explode("\n", ob_get_clean())));
        // line 32
        echo "\t\t\t";
        ob_start();
        echo "<li class=\"";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 32) == "rules")) {
            echo "current";
        } else {
            echo "tab";
        }
        echo "\">";
        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 32) != "rules")) {
            echo "<a href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/index.php?p=rules\">";
        }
        echo _gettext("Rules");        if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 32) != "rules")) {
            echo "</a>";
        }
        echo "</li>";
        echo implode(array_map('ltrim', explode("\n", ob_get_clean())));
        // line 33
        echo "\t\t</ul>
\t\t<br />
\t</div>


";
        // line 38
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['entries']) ? $context['entries'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['item']) {
            echo " 
\t<div class=\"content\">
\t\t<h2><span class=\"newssub\">";
            // line 40
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_subject", array(), "any", false, 40), "html");
            echo " ";
            if (($this->getAttribute((isset($context['_get']) ? $context['_get'] : null), "p", array(), "any", false, 40) == "")) {
                echo " by ";
                if (($this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_email", array(), "any", false, 40) != "")) {
                    echo " <a href=\"mailto:";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_email", array(), "any", false, 40), "html");
                    echo "\">";
                }
                echo " ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['item']) ? $context['item'] : null), "poster", array(), "any", false, 40), "html");
                echo " ";
                if (($this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_email", array(), "any", false, 40) != "")) {
                    echo " </a>";
                }
                echo "  - ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_time", array(), "any", false, 40), "d/m/y @ h:i a T"), "html");
                echo " ";
            }
            echo " </span>
\t\t<span class=\"permalink\"><a href=\"#";
            // line 41
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['item']) ? $context['item'] : null), "id", array(), "any", false, 41), "html");
            echo "\">#</a></span></h2>
\t\t";
            // line 42
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['item']) ? $context['item'] : null), "entry_message", array(), "any", false, 42), "html");
            echo "
\t</div><br />
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 45
        echo "                 ";
    }

    public function getTemplateName()
    {
        return "index.tpl";
    }
}
