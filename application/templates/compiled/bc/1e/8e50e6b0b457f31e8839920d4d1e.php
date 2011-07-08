<?php

/* manage/index.tpl */
class __TwigTemplate_bc1e8e50e6b0b457f31e8839920d4d1e extends Twig_Template
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
        echo "Statistics";
    }

    // line 5
    public function block_managecontent($context, array $blocks = array())
    {
        // line 6
        echo "<table class=\"stats\">

  <col class=\"col1\" /><col class=\"col2\" /><col class=\"col1\" /><col class=\"col2\" />
  <thead>
    <tr>
      <th colspan=\"4\">Statistics</th>
    </tr>
  </thead>
  <tbody>
    <tr>

      <td>Installation Date: </td>
      <td class=\"strong\">";
        // line 18
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['stats']) ? $context['stats'] : null), "installation_date", array(), "any", false, 18), "html");
        echo "</td>
      <td>Database Type: </td>
      <td class=\"strong\">";
        // line 20
        echo twig_escape_filter($this->env, (isset($context['dbtype']) ? $context['dbtype'] : null), "html");
        echo "</td>
    </tr>
    <tr>
      <td>Edaha Version: </td>

      <td class=\"strong\">";
        // line 25
        echo twig_escape_filter($this->env, (isset($context['currentversion']) ? $context['currentversion'] : null), "html");
        echo " ";
        if ((isset($context['newversion']) ? $context['newversion'] : null)) {
            echo "<a href=\"#\" class=\"warning\" title=\"{trans \"A new version of Edaha is available\"}\">[ ! ]</a>";
        }
        echo "</td>
      <td>Database Version: </td>
      <td class=\"strong\">";
        // line 27
        echo twig_escape_filter($this->env, (isset($context['dbversion']) ? $context['dbversion'] : null), "html");
        echo "</td>
    </tr>
    <tr>
      <td>Number of Boards: </td>

      <td class=\"strong\">";
        // line 32
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['stats']) ? $context['stats'] : null), "numboards", array(), "any", false, 32), "html");
        echo "</td>
      <td>Database Size: </td>
      <td class=\"strong\">";
        // line 34
        echo twig_escape_filter($this->env, (isset($context['dbsize']) ? $context['dbsize'] : null), "html");
        echo "</td>
    </tr>
    <tr>
      <td>Total Posts: </td>

      <td class=\"strong\">";
        // line 39
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['stats']) ? $context['stats'] : null), "totalposts", array(), "any", false, 39), "html");
        echo "</td>
      <td>PHP Version: </td>
      <td class=\"strong\">";
        // line 41
        echo twig_escape_filter($this->env, $this->env->getExtension('core')->getConstant("PHP_VERSION"), "html");
        echo "</td>
    </tr>
  </tbody>
</table>
";
    }

    public function getTemplateName()
    {
        return "manage/index.tpl";
    }
}
