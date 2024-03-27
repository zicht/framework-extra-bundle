<?php

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

class RenderAddEmbedParamsNodeVisitor implements NodeVisitorInterface
{
    private string $kernelProjectDir;

    public function __construct(string $kernelProjectDir)
    {
        $this->kernelProjectDir = $kernelProjectDir;
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        if (!($node instanceof FunctionExpression) || $node->getAttribute('name') !== 'controller') {
            return $node;
        }
        $source = $node->getSourceContext();
        $path = $source ? $source->getPath() : null;
        if (!$path || (strpos($path, $this->kernelProjectDir . '/vendor/') === 0 && strpos($path, $this->kernelProjectDir . '/vendor/zicht/') === false)) {
            // If this is a template from a vendor other than "zicht", don't add the embed params.
            return $node;
        }

        $args = $node->getNode('arguments');
        $attributesName = 'attributes';
        $attributes = new ArrayExpression([], $node->getTemplateLine());
        if ($args->hasNode(1)) {
            $attributesName = 1;
            $attributes = $args->getNode(1);
        } elseif ($args->hasNode('attributes')) {
            $attributes = $args->getNode('attributes');
        }
        $args->setNode(
            $attributesName,
            new FunctionExpression(
                'embed',
                new Node([$attributes]),
                $node->getTemplateLine()
            )
        );

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
