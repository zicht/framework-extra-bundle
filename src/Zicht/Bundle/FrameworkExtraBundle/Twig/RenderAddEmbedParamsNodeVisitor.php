<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig_NodeVisitorInterface;
use Twig_NodeInterface;
use Twig_Environment;

/**
 * Class RenderAddEmbedParamsNodeVisitor
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Twig
 */
class RenderAddEmbedParamsNodeVisitor implements Twig_NodeVisitorInterface
{
    /**
     * {@inheritdoc}
     */
    public function enterNode(\Twig_Node $node, Twig_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(\Twig_Node $node, Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Expression_Function) {
            if ($node->getAttribute('name') === 'controller') {
                $args = $node->getNode('arguments');
                if (!$args->hasNode(1)) {
                    $args->setNode(1, new \Twig_Node_Expression_Array(array(), $node->getTemplateLine()));
                }
                $args->setNode(
                    1,
                    new \Twig_Node_Expression_Function(
                        'embed',
                        new \Twig_Node(array($args->getNode(1))),
                        $node->getTemplateLine()
                    )
                );
            }
        }
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
