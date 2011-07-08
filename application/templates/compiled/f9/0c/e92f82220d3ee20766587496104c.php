<?php

/* manage/login.tpl */
class __TwigTemplate_f90ce92f82220d3ee20766587496104c extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'extrajs' => array($this, 'block_extrajs'),
            'css' => array($this, 'block_css'),
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
    public function block_title($context, array $blocks = array())
    {
        echo "Manage - Log In";
    }

    // line 4
    public function block_extrajs($context, array $blocks = array())
    {
        // line 5
        echo "  ";
        echo "
    \$(document).ready(function() {
      if ( top != self ) {
          top.location.href = window.location.href;
      }
      document.managelogin.username.focus();
    });
  ";
        // line 12
        echo "
";
    }

    // line 14
    public function block_css($context, array $blocks = array())
    {
        // line 15
        echo "  ";
        echo twig_escape_filter($this->env, $this->renderParentBlock("css", $context, $blocks), "html");
        echo "
  <link rel=\"stylesheet\" type=\"text/css\" media='screen' href=\"";
        // line 16
        echo kxEnv::Get('kx:paths:boards:path');
        echo "/public/css/manage.css\">
";
    }

    // line 18
    public function block_content($context, array $blocks = array())
    {
        // line 19
        echo "<form name='managelogin' action='";
        echo kxEnv::Get('kx:paths:script:path');
        echo "/manage.php?app=core&amp;module=login&amp;do=login-validate' method='post'>
<input type='hidden' name='qstring' id='qstring' value='";
        // line 20
        echo twig_escape_filter($this->env, (isset($context['query_string']) ? $context['query_string'] : null), "html");
        echo "' />
<div id='login'>";
        // line 21
        if ((isset($context['message']) ? $context['message'] : null)) {
            echo " <div id='login_error'>";
            echo twig_escape_filter($this->env, (isset($context['message']) ? $context['message'] : null), "html");
            echo "</div>";
        }
        echo "\t<div id='login_controls'>
\t\t<label for='username'>Username</label>
\t\t<input type='text' size='20' id='username' name='username' value=''>

\t\t
\t\t<label for='password'>Password</label>
\t\t<input type='password' size='20' id='password' name='password' value=''>\t</div>
\t<div id='login_submit'>
\t\t<input type='submit' class='button' value=\"Log In\" />
\t</div>
</div>
</form>\t
";
    }

    public function getTemplateName()
    {
        return "manage/login.tpl";
    }
}
