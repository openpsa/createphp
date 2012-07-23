<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\RdfMapperInterface;

use Midgard\CreatePHP\Entity\Controller as Type;

class RdfTypeFactory extends \Midgard\CreatePHP\Metadata\RdfTypeFactory
{
    /**
     * @var RdfMapperInterface
     */
    private $mapper;

    /**
     * @param RdfMapperInterface $mapper the mapper to use in this project
     */
    public function __construct(RdfMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get the type responsible for this class
     *
     * @param string $classname name of the model class to get type for
     *
     * @return \Midgard\CreatePHP\Type\TypeInterface
     */
    public function getType($classname) {
        if ('Test\\Midgard\\CreatePHP\\Model' == $classname) {
            $type = new Type($this->mapper);
            $type->setVocabulary('dcterms', 'http://purl.org/dc/terms/');
            $prop = new \Midgard\CreatePHP\Entity\Property(array(), 'title');
            $prop->setAttributes(array('property' => 'dcterms:title'));
            $type->title = $prop;
            return $type;
        }

        throw new \Exception("No type found for $classname");
    }

}