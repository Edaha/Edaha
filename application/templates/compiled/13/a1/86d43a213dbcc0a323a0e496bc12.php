<?php

/* global_wrapper.tpl */
class __TwigTemplate_13a186d43a213dbcc0a323a0e496bc12 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'css' => array($this, 'block_css'),
            'extrahead' => array($this, 'block_extrahead'),
            'extrajs' => array($this, 'block_extrajs'),
            'content' => array($this, 'block_content'),
        );
    }

    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html";
        // line 2
        echo twig_escape_filter($this->env, (isset($context['htmloptions']) ? $context['htmloptions'] : null), "html");
        echo " xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
<title>";
        // line 4
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
<link rel=\"shortcut icon\" href=\"";
        // line 5
        echo kxEnv::Get('kx:paths:main:path');
        echo "/favicon.ico\" />
";
        // line 6
        $this->displayBlock('css', $context, $blocks);
        // line 18
        $this->displayBlock('extrahead', $context, $blocks);
        // line 19
        if (((isset($context['locale']) ? $context['locale'] : null) != "en")) {
            // line 20
            echo "\t<link rel=\"gettext\" type=\"application/x-po\" href=\"";
            echo kxEnv::Get('kx:paths:main:path');
            echo "/inc/lang/";
            echo twig_escape_filter($this->env, (isset($context['locale']) ? $context['locale'] : null), "html");
            echo "/LC_MESSAGES/kusaba.po\" />
";
        }
        // line 22
        echo "<script type=\"text/javascript\" src=\"";
        echo kxEnv::Get('kx:paths:main:path');
        echo "/lib/javascript/gettext.js\"></script>
<script type=\"text/javascript\" src=\"";
        // line 23
        echo kxEnv::Get('kx:paths:main:path');
        echo "/lib/javascript/jquery-1.4.2.min.js\"></script>
<script type=\"text/javascript\">
\tkusaba = ";
        // line 25
        echo "{}";
        echo ";
\tkusaba.cgipath = '";
        // line 26
        echo kxEnv::Get('kx:paths:cgi:path');
        echo "';
\tkusaba.webpath = '";
        // line 27
        echo kxEnv::Get('kx:paths:main:path');
        echo "';
  ";
        // line 28
        $this->displayBlock('extrajs', $context, $blocks);
        // line 29
        echo "</script>
<script type=\"text/javascript\" src=\"";
        // line 30
        echo kxEnv::Get('kx:paths:main:path');
        echo "/lib/javascript/kusaba.js\"></script>
</head>
<body>
";
        // line 33
        $this->displayBlock('content', $context, $blocks);
        // line 34
        echo "</body>
</html>";
    }

    // line 4
    public function block_title($context, array $blocks = array())
    {
        echo kxEnv::Get('kx:site:name');
    }

    // line 6
    public function block_css($context, array $blocks = array())
    {
        // line 7
        if (((isset($context['locale']) ? $context['locale'] : null) == "ja")) {
            // line 8
            echo "\t";
            echo "
\t<style type=\"text/css\">
\t\t*{
\t\t\tfont-family: IPAMonaPGothic, Mona, 'MS PGothic', YOzFontAA97 !important;
\t\t\tfont-size: 1em;
\t\t}
\t</style>
\t";
            // line 15
            echo "
";
        }
    }

    // line 18
    public function block_extrahead($context, array $blocks = array())
    {
    }

    // line 28
    public function block_extrajs($context, array $blocks = array())
    {
    }

    // line 33
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "global_wrapper.tpl";
    }
}
