<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig;

use Twig_Compiler;
use Symfony\Bundle\TwigBundle\Node\RenderNode as BaseRenderNode;

/**
 * This decorator makes sure that if a {% render ... %} is used in Twig, the embed parameters (success_url, return_url)
 * are added to the request.
 *
 * TwigExtension::embedParams() is used for this.
 */
class DecoratedRenderNode extends \Twig_Node
{
    public function __construct(BaseRenderNode $wrappedNode)
    {
        parent::__construct();
        $this->wrapped = $wrappedNode;
    }


    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("echo \$this->env->getExtension('actions')->renderAction(")
            ->subcompile($this->wrapped->getNode('expr'))
            ->raw(', ')
            ->raw("\$this->env->getExtension('zicht_framework_extra')->embed(")
            ->subcompile($this->wrapped->getNode('attributes'))
            ->raw(')')
            ->raw(', ')
            ->subcompile($this->wrapped->getNode('options'))
            ->raw(");\n")
        ;
    }
}