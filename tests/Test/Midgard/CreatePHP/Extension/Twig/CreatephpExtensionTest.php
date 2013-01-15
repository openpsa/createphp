<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\Extension\Twig\CreatephpExtension;
use Midgard\CreatePHP\Extension\Twig\CreatephpNode;

use Test\Midgard\CreatePHP\Model;
use Midgard\CreatePHP\Metadata\RdfDriverXml;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;

use DOMDocument;
use SimpleXMLElement;

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

        $xmlDriver = new RdfDriverXml(array(__DIR__.'/../../Metadata/rdf'));
        $this->factory = new RdfTypeFactory($this->mapper, $xmlDriver);

        $this->twig = new \Twig_Environment();
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/templates'));
        $this->twig->addExtension(new CreatephpExtension($this->factory));
    }

    /**
     * Test the basic Twig templates of CreatePHP. The following
     * templates need to render the same html: node.twig, node_as.twig
     * and functions.twig:
     *
     * <test>
     *     <div xmlns:sioc="http://rdfs.org/sioc/ns#"
     *          xmlns:dcterms="http://purl.org/dc/terms/"
     *          typeof="sioc:Post"
     *          about="/the/subject">
     *         <h2 property="dcterms:title">content text</h2>
     *         <div property="sioc:content">content text</div>
     *     </div>
     * </test>
     *
     * @dataProvider templateNameDataProvider
     */
    public function testBasicTemplates($templateName)
    {
        $this->prepareTest();
        $xml = $this->renderXml($templateName);
        $this->assertCompiledCorrectly($xml);
    }

    public function templateNameDataProvider()
    {
        return array(
            array('node.twig'),
            array('node_as.twig'),
            array('functions.twig')
        );
    }

    private function prepareTest(){
        $model = new Model;
        $this->twig->addGlobal('mymodel', $model);

        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnValue('content text'))
        ;
        $this->mapper->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue(array()))
        ;
        $this->mapper->expects($this->any())
            ->method('isEditable')
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->any())
            ->method('createSubject')
            ->will($this->returnValue('/the/subject'))
        ;
        $this->mapper->expects($this->any())
            ->method('canonicalName')
            ->with(get_class($model))
            ->will($this->returnValue(get_class($model)))
        ;
    }

    private function assertCompiledCorrectly(\SimpleXMLElement $xml)
    {
        $this->assertEquals(1, count($xml->div));
        $this->assertEquals('/the/subject', $xml->div['about']);
        $namespaces = $xml->getDocNamespaces(true);
        $this->assertEquals('http://purl.org/dc/terms/', $namespaces['dcterms']);
        $this->assertEquals(1, count($xml->div->div));
        $this->assertEquals('dcterms:title', $xml->div->h2['property']);
        $this->assertEquals('content text', (string) $xml->div->h2);
        $this->assertEquals('sioc:content', $xml->div->div['property']);
        $this->assertEquals('content text', (string) $xml->div->div);

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

    /**
     * Only for debugging, write an xml object into a file
     *
     * @param $filename
     * @param SimpleXMLElement $xml
     */
    private function writeXmlToFile($filename, SimpleXMLElement $xml)
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML($xml->asXML());
        $dom->formatOutput = TRUE;
        $dom->save($filename);
    }
}
