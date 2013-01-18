<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Extension\Twig;

use Twig_NodeInterface;
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
     * @param Twig_NodeInterface $body       The body node
     * @param string             $modelname  The name of the model class to make an rdfa entity
     * @param string             $varname    The name for the rdfa entity to expose
     * @param boolean            $autotag    Automatically render start and end part of the node?
     * @param integer            $lineno     The line number
     * @param string             $tag        The tag name
     */
    public function __construct(Twig_NodeInterface $body, $modelname, $varname, $autotag, $lineno = 0, $tag = null)
    {
        $nodes = array('body' => $body);
        if (empty($varname)) {
            $varname = $modelname . '_rdf';
        }

        $attributes = array(
            'varname' => $varname,
            'modelname' => $modelname,
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

        $this->compileTypeLoad($compiler, $this->getAttribute('modelname'));

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
            ->raw('$context[')
            ->repr($modelname)
            ->raw("]);\n")
        ;
    }
}
