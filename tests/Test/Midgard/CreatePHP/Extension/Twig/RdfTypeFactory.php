<?php

namespace Test\Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\RdfMapperInterface;
use Midgard\CreatePHP\Entity\Controller as Type;

use Test\Midgard\CreatePHP\Model;

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
     * Get the type if this is the expected model class
     */
    public function getTypeByObject($class) {
        if ($class instanceof Model) {
            $type = new Type($this->mapper);
            $type->setVocabulary('dcterms', 'http://purl.org/dc/terms/');
            $prop = new \Midgard\CreatePHP\Entity\Property('title', array());
            $prop->setAttributes(array('property' => 'dcterms:title'));
            $type->title = $prop;
            return $type;
        }

        throw new \Exception('No type found for ' . get_class($class));
    }

}