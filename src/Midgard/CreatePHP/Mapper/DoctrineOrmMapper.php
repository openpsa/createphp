<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author David Buchmann <david@liip.ch>
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and LGPL (LGPL-LICENSE.txt) licenses.
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Mapper;

use Midgard\CreatePHP\Entity\CollectionInterface;
use Midgard\CreatePHP\Entity\EntityInterface;
use \RuntimeException;

/**
 * Mapper to handle Doctrine ORM.
 */
class DoctrineOrmMapper extends BaseDoctrineRdfMapper
{
    /**
     * Escaping characters map.
     *
     * @var array
     */
    protected $escapeCharacters = array(
        "\n" => '%0A',
        '%' => '%25',
        '"' => '%22',
        '\'' => '%27',
        '=' => '%3D',
        '<' => '%3C',
        '>' => '%3E',
        '|' => '%7C',
    );

    /**
     * {@inheritDoc}
     *
     * For the ORM, we need the class name to know in which table to look. The
     * ORM can have multi field ids. Build the subject from all id's as per the
     * metadata information as key=value pairs, concatenated with '|'.
     */
    public function createSubject($object)
    {
        $meta = $this->om->getClassMetaData(get_class($object));
        $ids = $meta->getIdentifierValues($object);

        $key = array();
        foreach ($ids as $name => $id) {
            $name = $this->escape($name);
            $id = $this->escape($id);
            $key[] = "$name=$id";
        }

        $idstring = implode('|', $key);

        return $this->canonicalName(get_class($object)) . "|$idstring";
    }

     /**
     * {@inheritDoc}
     */
    public function getBySubject($subject)
    {
        if (empty($subject)) {
            throw new RuntimeException('Subject may not be empty');
        }

        $ids = explode('|', $subject);
        if (count($ids) < 2) {
            throw new RuntimeException("Invalid subject: $subject");
        }
        $class = ltrim($ids[0], '/'); // if we get the / from the url, this breaks the class loader in a funny way.
        $repository = $this->om->getRepository($class);

        array_shift($ids);
        $identifiers = array();
        foreach ($ids as $identifier) {
            list($name, $id) = explode('=', $identifier);
            $name = $this->unescape($name);
            $id = $this->unescape($id);
            $identifiers[$name] = $id;
        }
        $object = $repository->find($identifiers);

        if (empty($object)) {
            throw new RuntimeException("Not found: $subject");
        }

        return $object;
    }

    /**
     * Escape a string to be used in an ID. Escapes all characters used in
     * building the ID string and all potentially harmful when embedded in
     * HTML.
     *
     * @param string $string Original string
     *
     * @return string The string with characters replaced
     */
    protected function escape($string)
    {
        return str_replace(array_keys($this->escapeCharacters), array_values($this->escapeCharacters), $string);
    }

    /**
     * Restore the original string from the escaped one. Reverse operation of
     * self::escape()
     *
     * @param string $string Escaped string
     *
     * @return string Original string
     */
    protected function unescape($string)
    {
        return str_replace(array_values($this->escapeCharacters), array_keys($this->escapeCharacters), $string);
    }

    /**
     * Reorder the children of the collection node according to the expected order
     *
     * @param EntityInterface $entity
     * @param CollectionInterface $node
     * @param $expectedOrder array of subjects
     */
    public function orderChildren(EntityInterface $entity, CollectionInterface $node, $expectedOrder)
    {
        // Currently ordering children is not supported by the ORM. This could be implemented using
        // the sortable doctrine extension, e.g. https://github.com/l3pp4rd/DoctrineExtensions
    }

}
