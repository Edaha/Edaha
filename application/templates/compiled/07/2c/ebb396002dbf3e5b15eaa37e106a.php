<?php

/* manage/macros.tpl */
class __TwigTemplate_072cebb396002dbf3e5b15eaa37e106a extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

    }

    // line 1
    public function getmanageform($id = null, $name = null, $submit = null, $entries = null)
    {
        $context = array_merge($this->env->getGlobals(), array(
            "id" => $id,
            "name" => $name,
            "submit" => $submit,
            "entries" => $entries,
        ));

        ob_start();
        // line 2
        echo "    <fieldset id=\"";
        echo twig_escape_filter($this->env, (isset($context['id']) ? $context['id'] : null), "html");
        echo "\">
      <legend>";
        // line 3
        echo _gettext((isset($context['name']) ? $context['name'] : null));        echo "</legend>
      ";
        // line 4
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['entries']) ? $context['entries'] : null));
        foreach ($context['_seq'] as $context['name'] => $context['entry']) {
            // line 5
            echo "        <label for=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 5), "html");
            echo "\">";
            echo _gettext((isset($context['name']) ? $context['name'] : null));            echo ":</label>
        ";
            // line 6
            if ((($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "type", array(), "any", false, 6) == "text") || ($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "type", array(), "any", false, 6) == "password"))) {
                // line 7
                echo "          <input type=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "type", array(), "any", false, 7), "html");
                echo "\" id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 7), "html");
                echo "\" name=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 7), "html");
                echo "\" value=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "value", array(), "any", false, 7), "html");
                echo "\" />
        ";
            } elseif (($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "type", array(), "any", false, 8) == "textarea")) {
                // line 9
                echo "          <textarea id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 9), "html");
                echo "\" name=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 9), "html");
                echo "\" rows=\"25\" cols=\"65\">";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "value", array(), "any", false, 9), "html");
                echo "</textarea>
        ";
            } elseif (($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "type", array(), "any", false, 10) == "select")) {
                // line 11
                echo "          <select id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 11), "html");
                echo "\" name=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "id", array(), "any", false, 11), "html");
                echo "\">
            ";
                // line 12
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "value", array(), "any", false, 12));
                foreach ($context['_seq'] as $context['key'] => $context['option']) {
                    // line 13
                    echo "              <option value=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['option']) ? $context['option'] : null), "value", array(), "any", false, 13), "html");
                    echo "\" ";
                    if ($this->getAttribute((isset($context['option']) ? $context['option'] : null), "selected", array(), "any", false, 13)) {
                        echo "selected=selected";
                    }
                    echo ">";
                    echo _gettext((isset($context['key']) ? $context['key'] : null));                    echo "</option>
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['key'], $context['option'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                // line 15
                echo "          </select>
        ";
            }
            // line 17
            echo "        ";
            if ($this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "desc", array(), "any", false, 17)) {
                // line 18
                echo "          ";
                $context['entrydesc'] = $this->getAttribute((isset($context['entry']) ? $context['entry'] : null), "desc", array(), "any", false, 18);
                // line 19
                echo "          <span class=\"desc\">";
                echo _gettext((isset($context['entrydesc']) ? $context['entrydesc'] : null));                echo "</span><br />
        ";
            }
            // line 21
            echo "        <br />
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['name'], $context['entry'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 23
        echo "      ";
        if ((isset($context['submit']) ? $context['submit'] : null)) {
            // line 24
            echo "        <label for=\"submit\">&nbsp;</label>
        <input type=\"submit\" id=\"submit\" value=\"";
            // line 25
            echo _gettext("Submit");            echo "\" />
      ";
        }
        // line 27
        echo "    </fieldset>
";

        return ob_get_clean();
    }

    public function getTemplateName()
    {
        return "manage/macros.tpl";
    }
}
