<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

class RenderAddEmbedParamsNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        if ($node instanceof FunctionExpression) {
            if ($node->getAttribute('name') === 'controller') {
                $args = $node->getNode('arguments');
                if (!$args->hasNode(1)) {
                    $args->setNode(1, new ArrayExpression([], $node->getTemplateLine()));
                }
                $args->setNode(
                    1,
                    new FunctionExpression(
                        'embed',
                        new Node([$args->getNode(1)]),
                        $node->getTemplateLine()
                    )
                );
            }
        }
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
