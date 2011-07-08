<?php

/* manage/menu.tpl */
class __TwigTemplate_92250ef8b9192ebd2942be0bd42669ca extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['menu']) ? $context['menu'] : null));
        foreach ($context['_seq'] as $context['module'] => $context['items']) {
            // line 2
            echo "  ";
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context['items']) ? $context['items'] : null));
            foreach ($context['_seq'] as $context['name'] => $context['item']) {
                // line 3
                echo "      <div class=\"section\">
        <h2>";
                // line 4
                echo twig_escape_filter($this->env, (isset($context['name']) ? $context['name'] : null), "html");
                echo "</h2>
        <ul>
        ";
                // line 6
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable((isset($context['item']) ? $context['item'] : null));
                foreach ($context['_seq'] as $context['_key'] => $context['section']) {
                    // line 7
                    echo "            <li><a href=\"";
                    echo twig_escape_filter($this->env, (isset($context['base_url']) ? $context['base_url'] : null), "html");
                    echo "app=";
                    echo twig_escape_filter($this->env, $this->env->getExtension('core')->getConstant("KX_CURRENT_APP"), "html");
                    echo "&amp;module=";
                    echo twig_escape_filter($this->env, (isset($context['module']) ? $context['module'] : null), "html");
                    echo "&amp;section=";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['section']) ? $context['section'] : null), "section", array(), "any", false, 7), "html");
                    echo "&amp;";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['section']) ? $context['section'] : null), "url", array(), "any", false, 7), "html");
                    echo "\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['section']) ? $context['section'] : null), "title", array(), "any", false, 7), "html");
                    echo "</a></li>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['section'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                // line 9
                echo "        </ul>
      </div>
  ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['name'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['module'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
    }

    public function getTemplateName()
    {
        return "manage/menu.tpl";
    }
}
