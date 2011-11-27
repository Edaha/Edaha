<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a debug node.
 *
 * @package    twig
 * @subpackage Twig-extensions
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class Twig_Extensions_Node_kxEnv extends Twig_Node
{
    public function __construct(Twig_Node_Expression $expr = null, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
      $compiler->addDebugInfo($this);

      $val = $this->getNode('expr')->getAttribute('value');
      if($val=='DUMP_CONFIG') { // For debugging
		$compiler->write("echo kxEnv::dumpConfig();\n");
	  } else {
		$compiler->write("echo kxEnv::Get('kx:$val');\n");
      }
    }

}
