<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Doctrine\Common\Annotations\AnnotationRegistry;

use Midgard\CreatePHP\Extension\Twig\CreatephpExtension;
use Midgard\CreatePHP\Extension\Twig\CreatephpNode;

use Midgard\CreatePHP\Metadata\RdfDriverXml;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;

use Test\Midgard\CreatePHP\Model;

class CreatephpExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Midgard\CreatePHP\RdfMapperInterface
     */
    private $mapper;
    /**
     * @var RdfTypeFactory
     */
    private $factory;
    /** @var \Twig_Environment */
    private $twig;

    /**
     * @var RdfDriverXml
     */
    private $driver;

    protected function setUp()
    {
        global $autoload;
        if (!class_exists('Twig_Environment')) {
            $this->markTestSkipped('Twig is not installed.');
        }

        AnnotationRegistry::registerLoader(function($class) use ($autoload) {
            $autoload->loadClass($class);
            return class_exists($class, false);
        });
        AnnotationRegistry::registerFile(__DIR__.'/../../../../../../vendor/doctrine/phpcr-odm/lib/Doctrine/ODM/PHPCR/Mapping/Annotations/DoctrineAnnotations.php');

        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $driver = new \Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver($reader, array('../../'));


        $this->driver = new RdfDriverXml(array(__DIR__ . '/../../Metadata/rdf'));
        $documentManager = \Doctrine\ODM\PHPCR\DocumentManager::create($this->getMock('PHPCR\\SessionInterface'), new \Doctrine\ODM\PHPCR\Configuration());
        $registry = $this->getMock('Doctrine\\Common\\Persistence\\ManagerRegistry');
        $registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($documentManager))
        ;

        $this->mapper = new \Midgard\CreatePHP\Mapper\DoctrinePhpcrOdmMapper($this->driver->getAllNames(), $registry);

        $this->factory = new RdfTypeFactory($this->mapper, $this->driver);

        $this->twig = new \Twig_Environment();
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/templates'));
        $this->twig->addExtension(new CreatephpExtension($this->factory));
    }

    public function testNode()
    {
        $this->twig->addGlobal('mymodel', new Model);

        /*
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
        */
        $xml = $this->renderXml('node.twig');

        $this->assertCompiledCorrectly($xml);

    }

    public function testNodeAs()
    {
        $this->twig->addGlobal('mymodel', new Model);
        /*
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
        */

        $xml = $this->renderXml('node_as.twig');

        $this->assertCompiledCorrectly($xml);
    }

    public function testFunctions()
    {
        $this->twig->addGlobal('mymodel', new Model);

        /*
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
        */

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
