<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

class RenderAddEmbedParamsNodeVisitor implements NodeVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ExpressionFunction) {
            if ($node->getAttribute('name') === 'controller') {
                $args = $node->getNode('arguments');
                if (!$args->hasNode(1)) {
                    $args->setNode(1, new ArrayExpression([], $node->getTemplateLine()));
                }
                $args->setNode(
                    1,
                    new ExpressionFunction(
                        'embed',
                        new Node([$args->getNode(1)]),
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
