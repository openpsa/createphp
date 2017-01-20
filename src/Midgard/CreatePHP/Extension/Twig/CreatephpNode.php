<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Extension\Twig;

use Twig_Node;
use Twig_Compiler;

/**
 * A twig node to render the createphp tag
 *
 * @package Midgard.CreatePHP
 */
class CreatephpNode extends Twig_Node
{
    /**
     * Constructor.
     *
     * Available attributes:
     *
     *  * varname: The name of the rdfa entity to expose to the body node
     *
     * @param Twig_Node $body     The body of the createphp token
     * @param Twig_Node $object   The object to convert to rdf
     * @param string|null        $varname  The name for the rdfa entity to expose or null if no explicit name
     * @param boolean            $autotag  Automatically render start and end part of the node?
     * @param integer            $lineno   The line number
     * @param string             $tag      The tag name
     */
    public function __construct(
        Twig_Node $body,
        Twig_Node $object,
        $varname,
        $autotag,
        $lineno = 0,
        $tag = null
    ) {
        $nodes = array('body' => $body);
        if (empty($varname)) {
            $varname = $this->findVariableName($object) . '_rdf';
        }

        $attributes = array(
            'varname' => $varname,
            'object' => $object,
            'autotag' => $autotag
        );

        parent::__construct($nodes, $attributes, $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write("// createphp\n")
            ->write('$context[')
            ->repr($this->getAttribute('varname'))
            ->raw('] = ')
        ;

        $this->compileTypeLoad($compiler, 'mymodel');

        $compiler
            ->raw(";\n")
        ;

        //output opening and closing elements for this node?
        $autotag = $this->getAttribute('autotag');
        if ($autotag) {
            $compiler->write('echo ');
        }

        /**
         * To set the node into the correct mode, start rendering even if no
         * output is needed. It is up to the user to be intelligent enough to
         * understand what he has to do if he is using noautotag.
         */
        $compiler
            ->write('$context[')
            ->repr($this->getAttribute('varname'))
            ->write(']->renderStart()')
            ->raw(";\n");

        $compiler
            ->subcompile($this->getNode('body'))
        ;

        if ($autotag) {
            $compiler->write('echo ');
        }
        $compiler
            ->write('$context[')
            ->repr($this->getAttribute('varname'))
            ->write(']->renderEnd()')
            ->raw(";\n");

        $compiler
            ->write('unset($context[')
            ->repr($this->getAttribute('varname'))
            ->raw("]);\n")
        ;
    }

    protected function compileTypeLoad(Twig_Compiler $compiler, $modelname)
    {
        $compiler
            ->write('$this->env->getExtension(\'createphp\')->createEntity(')
        ;
        $compiler->subcompile($this->getAttribute('object'));
        $compiler
            ->raw(");\n")
        ;
    }

    /**
     * Get the name for the rdf variable by taking it from the last piece that
     * is a name.
     *
     * For example container.method.content will make the name "content"
     *
     * @param Twig_Node $node
     *
     * @return string|null get the variable name
     */
    protected function findVariableName(Twig_Node $node)
    {
        $name = null;
        if ($node instanceof \Twig_Node_Expression_Name) {
            $name = $node->getAttribute('name');
        } elseif ($node instanceof \Twig_Node_Expression_Constant) {
            $name = $node->getAttribute('value');
        }

        foreach ($node as $child) {
            $ret = $this->findVariableName($child);
            if ($ret) {
                $name = $ret;
            }
        }

        return $name;
    }
}
