<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\Extension\Twig\CreatephpExtension;
use Midgard\CreatePHP\Extension\Twig\CreatephpNode;

use Test\Midgard\CreatePHP\Model;

class CreatephpExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Midgard\CreatePHP\RdfMapperInterface
     */
    private $mapper;
    /**
     * @var \Midgard\CreatePHP\Metadata\RdfTypeFactory
     */
    private $factory;
    /** @var \Twig_Environment */
    private $twig;

    protected function setUp()
    {
        if (!class_exists('Twig_Environment')) {
            $this->markTestSkipped('Twig is not installed.');
        }

        $this->mapper = $this->getMock('Midgard\CreatePHP\RdfMapperInterface');
        $this->factory = new RdfTypeFactory($this->mapper);

        $this->twig = new \Twig_Environment();
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/templates'));
        $this->twig->addExtension(new CreatephpExtension($this->factory));
    }

    public function testNode()
    {
        $this->twig->addGlobal('mymodel', new Model);

        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnValue('content text'))
        ;
        $this->mapper->expects($this->any())
            ->method('isEditable')
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->any())
            ->method('createSubject')
            ->will($this->returnValue('/the/subject'))
        ;

        $xml = $this->renderXml('node.twig');

        $this->assertCompiledCorrectly($xml);

    }

    public function testNodeAs()
    {
        $this->twig->addGlobal('mymodel', new Model);
        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnValue('content text'))
        ;
        $this->mapper->expects($this->any())
            ->method('isEditable')
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->any())
            ->method('createSubject')
            ->will($this->returnValue('/the/subject'))
        ;

        $xml = $this->renderXml('node_as.twig');

        $this->assertCompiledCorrectly($xml);
    }

    public function testFunctions()
    {
        $this->twig->addGlobal('mymodel', new Model);

        $this->twig->addGlobal('mymodel', new Model);
        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnValue('content text'))
        ;
        $this->mapper->expects($this->any())
            ->method('isEditable')
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->any())
            ->method('createSubject')
            ->will($this->returnValue('/the/subject'))
        ;

        $xml = $this->renderXml('functions.twig');

        $this->assertCompiledCorrectly($xml);
    }

    private function assertCompiledCorrectly(\SimpleXMLElement $xml)
    {
        $this->assertEquals(1, count($xml->div));
        $this->assertEquals('/the/subject', $xml->div['about']);
        // TODO: how to get to namesapce? $this->assertEquals('http://purl.org/dc/terms/', $xml->div['xmlns:dcterms']);
        $this->assertEquals(1, count($xml->div->div));
        $this->assertEquals('content text', $xml->div->div);
        $this->assertEquals('dcterms:title', $xml->div->div['property']);
    }

    /**
     * @param $name
     * @param array $context
     * @return \SimpleXMLElement
     */
    private function renderXml($name, $context = array())
    {
        return new \SimpleXMLElement($this->twig->loadTemplate($name)->render($context));
    }

    /**
     * Only for debugging, dump the compiled template file to console and die
     *
     * @param string $template the name of the template in __DIR__/templates/
     */
    private function dumpCompiledTemplate($template)
    {
        $source = file_get_contents(__DIR__ . '/templates/' . $template);
        var_dump($this->twig->compileSource($source, $template));
        die;
    }
}
