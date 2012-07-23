<?php

namespace Midgard\CreatePHP\Extension\Twig;

use Midgard\CreatePHP\NodeInterface;
use Midgard\CreatePHP\Entity\EntityInterface;
use Midgard\CreatePHP\Metadata\RdfTypeFactory;

/**
 * Twig Extension to integrate createphp into Twig
 *
 * @author David Buchmann <david@liip.ch>
 */
class CreatephpExtension extends \Twig_Extension
{
    protected $typeFactory;
    protected $environment;

    public function __construct(RdfTypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
            // {% createphp model %}
            new CreatephpTokenParser($this->typeFactory),
        );
    }

    public function getFunctions()
    {
        return array(
            'createphp_attributes'  => new \Twig_Function_Method($this, 'renderAttributes', array('is_safe' => array('html'))),
            'createphp_content'     => new \Twig_Function_Method($this, 'renderContent', array('is_safe' => array('html'))),

        );
    }

    /**
     * Renders the attributes of the passed node
     *
     * Example usage in Twig templates:
     *
     *     <span {{ createphp_attributes(entity) }}>
     *
     * @param NodeInterface $node The node (entity, property or collection) for which to render the attributes
     *
     * @return string The html markup
     */
    public function renderAttributes(NodeInterface $node)
    {
        return $node->renderAttributes();
    }

    /**
     * Renders the content of the passed node.
     *
     * Example usage:
     *
     *      <div {{ createphp_attributes(entity) }}>
     *          <span {{ createphp_attributes(entity.title) }}>
     *              {{ createphp_content(entity.title}}
     *          </span>
     *      </div>
     *
     * @param NodeInterface $node the node for which to render the content
     *
     * @return string The html markup
     */
    public function renderContent(NodeInterface $node)
    {
        return $node->renderContent();
    }

    public function createEntity($model)
    {
        if (! is_object($model)) {
            throw new \Twig_Error_Runtime('The model to create the entity from must be a class');
        }

        $classname = get_class($model);
        // TODO: how to handle this doctrine specific problem?
        if ($model instanceof \Doctrine\Common\Persistence\Proxy) {
            $classname = \Doctrine\Common\Util\ClassUtils::getRealClass($classname);
        }

        $type = $this->typeFactory->getType($classname);
        if (! $type instanceof \Midgard\CreatePHP\Type\TypeInterface) {
            throw new \Twig_Error_Runtime('Could not find metadata for '.get_class($model));
        }

        return $type->createWithObject($model);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'createphp';
    }

}
