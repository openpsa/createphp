<?php
/**
 * @copyright CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GbR, http://www.contentcontrol-berlin.de
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package Midgard.CreatePHP
 */

namespace Midgard\CreatePHP\Entity;

use Midgard\CreatePHP\Entity\EntityInterface;
use Midgard\CreatePHP\Type\NodeDefinitionInterface;

/**
 * Base interface for the DOM-like nodes.
 *
 * Provides functionality for managing relevant aspects of the node, specifically, managing
 * attributes, parent/children relations and rendering. The latter is split into three
 * different functions for maximum flexibility. So you can call render() to output the
 * complete node HTML, or you can call render_start() for the opening tag, render_content()
 * for the node's content (or children) and render_end() for the closing tag.
 *
 * @package Midgard.CreatePHP
 */
interface NodeInterface extends NodeDefinitionInterface
{
    /**
     * Renders everything including wrapper html tag and properties
     *
     * If you want more control over the generated HTML, call renderStart,
     * renderContent and renderEnd separately
     *
     * @param string $tag_name
     *
     * @return string the rendered html
     */
    public function render($tag_name = false);

    /**
     * Renders introduction part for this node
     *
     * @param string $tag_name set to a string to overwrite what html tag should be written
     *
     * @return string the rendered html
     */
    function renderStart($tag_name = false);

    /**
     * Render the content of this node, including its children if applicable
     *
     * @return string the rendered html
     */
    function renderContent();

    /**
     * Render tail part for this node
     *
     * @return string the rendered html
     */
    public function renderEnd();

    /**
     * Render just the attributes. This is not needed if you use
     * renderStart()
     *
     * @return string the rendered attributes
     */
    function renderAttributes();

    /**
     * Has to return the same as self::render()
     *
     * @return string
     */
    public function __toString();
}
