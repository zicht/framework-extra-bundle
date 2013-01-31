<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig_NodeVisitorInterface;
use Twig_NodeInterface;
use Twig_Environment;
 
class RenderAddEmbedParamsNodeVisitor implements Twig_NodeVisitorInterface
{
    /**
     * Called before child nodes are visited.
     *
     * @param \Twig_NodeInterface $node The node to visit
     * @param \Twig_Environment   $env  The Twig environment instance
     *
     * @return \Twig_NodeInterface The modified node
     */
    function enterNode(Twig_NodeInterface $node, Twig_Environment $env) {
        return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @param \Twig_NodeInterface $node The node to visit
     * @param \Twig_Environment   $env  The Twig environment instance
     *
     * @return \Twig_NodeInterface The modified node
     */
    function leaveNode(Twig_NodeInterface $node, Twig_Environment $env) {
        if ($node instanceof \Symfony\Bundle\TwigBundle\Node\RenderNode) {
            return new DecoratedRenderNode($node);
        }
        return $node;
    }

    /**
     * Returns the priority for this visitor.
     *
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return integer The priority level
     */
    function getPriority() {
        return 0;
    }
}