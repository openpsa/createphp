<?php

namespace Midgard\CreatePHP\Extension\Twig;

use Twig_NodeInterface;

class CreatephpNode extends \Twig_Node
{
    /**
     * Constructor.
     *
     * Available attributes:
     *
     *  * varname: The name of the rdfa entity to expose to the body node
     *
     * @param Twig_NodeInterface $body       The body node
     * @param string             $modelname  The name of the model class to make an rdfa entity
     * @param string             $varname    The name for the rdfa entity to expose
     * @param integer            $lineno     The line number
     * @param string             $tag        The tag name
     */
    public function __construct(\Twig_NodeInterface $body, $modelname, $varname, $lineno = 0, $tag = null)
    {
        $nodes = array('body' => $body);
        if (empty($varname)) {
            $varname = $modelname . '_rdf';
        }

        $attributes = array('varname' => $varname, 'modelname' => $modelname);

        parent::__construct($nodes, $attributes, $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write("// createphp\n")
            ->write('$context[')
            ->repr($this->getAttribute('varname'))
            ->raw('] = ')
        ;

        $this->compileTypeLoad($compiler, $this->getAttribute('modelname'));

        $compiler
            ->raw(";\n")
            ->subcompile($this->getNode('body'))
        ;

        $compiler
            ->write('unset($context[')
            ->repr($this->getAttribute('varname'))
            ->raw("]);\n")
        ;
    }

    protected function compileTypeLoad(\Twig_Compiler $compiler, $modelname)
    {
        $compiler
            ->write('$this->env->getExtension(\'createphp\')->createEntity(')
            ->raw('$context[')
            ->repr($modelname)
            ->raw("]);\n")
        ;
    }
}
