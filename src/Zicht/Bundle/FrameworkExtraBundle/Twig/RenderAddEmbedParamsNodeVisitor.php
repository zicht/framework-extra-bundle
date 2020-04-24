<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig_NodeVisitorInterface;
use Twig_NodeInterface;
use Twig_Environment;

class RenderAddEmbedParamsNodeVisitor implements Twig_NodeVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function enterNode(\Twig_Node $node, Twig_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(\Twig_Node $node, Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Expression_Function) {
            if ($node->getAttribute('name') === 'controller') {
                $args = $node->getNode('arguments');
                if (!$args->hasNode(1)) {
                    $args->setNode(1, new \Twig_Node_Expression_Array([], $node->getTemplateLine()));
                }
                $args->setNode(
                    1,
                    new \Twig_Node_Expression_Function(
                        'embed',
                        new \Twig_Node([$args->getNode(1)]),
                        $node->getTemplateLine()
                    )
                );
            }
        }
        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
