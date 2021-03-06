<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Symfony\Bundle\TwigBundle\Node\RenderNode as BaseRenderNode;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * This decorator makes sure that if a {% render ... %} is used in Twig, the embed parameters (success_url, return_url)
 * are added to the request.
 *
 * TwigExtension::embedParams() is used for this.
 */
class DecoratedRenderNode extends Node
{
    /**
     * @param BaseRenderNode $wrappedNode
     */
    public function __construct(BaseRenderNode $wrappedNode)
    {
        parent::__construct();
        $this->wrapped = $wrappedNode;
    }

    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler)
    {
        $getExtension = sprintf('$this->env->getExtension(\'%s\')', ZichtFrameworkExtraExtension::class);
        $compiler
            ->addDebugInfo($this)
            ->write('echo $this->env->getExtension(\'actions\')->renderAction(')
            ->subcompile($this->wrapped->getNode('expr'))
            ->raw(', ')
            ->raw(sprintf('%s->embed(', $getExtension))
            ->subcompile($this->wrapped->getNode('attributes'))
            ->raw(')')
            ->raw(', ')
            ->subcompile($this->wrapped->getNode('options'))
            ->raw(");\n");
    }
}
