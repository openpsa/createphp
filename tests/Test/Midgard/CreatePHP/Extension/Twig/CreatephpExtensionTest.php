<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\Extension\Twig\CreatephpExtension;
use Midgard\CreatePHP\Extension\Twig\CreatephpNode;

use Test\Midgard\CreatePHP\Container;
use Test\Midgard\CreatePHP\Model;
use Test\Midgard\CreatePHP\Collection;
use Midgard\CreatePHP\Metadata\RdfDriverXml;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;
use Midgard\CreatePHP\Entity\PropertyInterface;

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

        $xmlDriver = new RdfDriverXml(array(__DIR__.'/../../Metadata/rdf-twig'));
        $this->factory = new RdfTypeFactory($this->mapper, $xmlDriver);

        $this->twig = new \Twig_Environment();
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/templates'));
        $this->twig->addExtension(new CreatephpExtension($this->factory));
    }

    /**
     * Test the basic Twig templates of CreatePHP (no collections). The
     * following templates need to render the same html: node.twig,
     * node_as.twig and functions.twig:
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
        $this->prepareBasicTest();
        $xml = $this->renderXml($templateName);
        $this->assertCompiledCorrectly($xml);
    }

    public function templateNameDataProvider()
    {
        return array(
            array('node.twig'),
            array('node_as.twig'),
            array('container.twig'),
            array('container_as.twig'),
            array('functions.twig'),
        );
    }

    public function testCollectionsTemplate()
    {
        $collection = new Collection;
        $this->twig->addGlobal('mycollection', $collection);

        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnCallback(array($this, 'getPropertyValueCallback')))
        ;
        $this->mapper->expects($this->any())
            ->method('isEditable')
            ->will($this->returnValue(true))
        ;
        $this->mapper->expects($this->any())
            ->method('createSubject')
            ->will($this->returnCallback(array($this, 'createSubjectCallback')))
        ;
        $this->mapper->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue($collection->getChildren()))
        ;
        $this->mapper->expects($this->any())
            ->method('canonicalName')
            ->will($this->returnCallback(array($this, 'canonicalNameCallback')))
        ;

        $xml = $this->renderXml('collection.twig');

        //assert no duplicate enclosing div is present
        $this->assertEquals(0, count($xml->div));

        //assert the enclosing div is correct
        $this->assertEquals('/the/subject/collection', $xml['about']);
        $this->assertEquals('my-class', $xml['class']);
        $this->assertEquals('sioc:Forum', $xml['typeof']);
        $namespaces = $xml->getDocNamespaces(true);
        $this->assertEquals('http://purl.org/dc/terms/', $namespaces['dcterms']);
        $this->assertEquals('http://rdfs.org/sioc/ns#', $namespaces['sioc']);

        //assert the collection content is correct
        $this->assertEquals(1, count($xml->h1));
        $this->assertEquals('the collection title', $xml->h1);
        $this->assertEquals('dcterms:title', $xml->h1['property']);

        //assert the listing of children is correct
        $this->assertEquals(1, count($xml->ul));
        $this->assertEquals(0, count($xml->ul['about']));
        $this->assertEquals(2, count($xml->ul->li));
        $this->assertEquals('dcterms:hasPart', $xml->ul['rel']);
        $this->assertEquals('dcterms:partOf', $xml->ul['rev']);

        //assert the 1st children is correct
        $this->assertEquals('sioc:Post', $xml->ul->li['typeof']);
        $this->assertEquals('/the/subject/model/1', $xml->ul->li['about']);
        $this->assertEquals('title 1', (string) $xml->ul->li->div[0]);
        $this->assertEquals('dcterms:title', $xml->ul->li->div[0]['property']);
        $this->assertEquals('content 1', (string) $xml->ul->li->div[1]);
        $this->assertEquals('sioc:content', $xml->ul->li->div[1]['property']);

        //assert the 2nd children is correct
        $this->assertEquals('sioc:Post', $xml->ul->li[1]['typeof']);
        $this->assertEquals('/the/subject/model/2', $xml->ul->li[1]['about']);
        $this->assertEquals('title 2', (string) $xml->ul->li[1]->div[0]);
        $this->assertEquals('dcterms:title', $xml->ul->li[1]->div[0]['property']);
        $this->assertEquals('content 2', (string) $xml->ul->li[1]->div[1]);
        $this->assertEquals('sioc:content', $xml->ul->li[1]->div[1]['property']);
    }

    public function canonicalNameCallback($className) {

        if ($className === 'Test\Midgard\CreatePHP\Collection') {
            return 'Test\Midgard\CreatePHP\Collection';
        } else {
            return 'Test\Midgard\CreatePHP\Model';
        }
    }

    public function getPropertyValueCallback($object, PropertyInterface $property)
    {
        $name = $property->getIdentifier();
        if (method_exists($object, 'getObject')) {
            $object = $object->getObject();
        }
        $method = 'get' . ucfirst($name);
        if (method_exists($object, $method)) {
            return $object->$method();
        }
        throw new \Exception('Invalid call to getPropertyValue');
    }

    public function createSubjectCallback($object)
    {
        if (method_exists($object, 'getObject')) {
            $object = $object->getObject();
        }
        return $object->getSubject();
    }

    private function prepareBasicTest(){
        $model = new Model;
        $this->twig->addGlobal('mymodel', $model);
        $container = new Container($model);
        $this->twig->addGlobal('mycontainer', $container);

        $this->mapper->expects($this->any())
            ->method('getPropertyValue')
            ->will($this->returnCallback(array($this, 'getPropertyValueCallback')))
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
        $this->assertEquals('the model title', (string) $xml->div->h2);
        $this->assertEquals('sioc:content', $xml->div->div['property']);
        $this->assertEquals('the model content', (string) $xml->div->div);

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
