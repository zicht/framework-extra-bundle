<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Node;
use Twig_Compiler;
use Zicht\Bundle\FrameworkExtraBundle\Twig\Extension as ZichtFrameworkExtraExtension;

/**
 * Class AnnotationsNode
 */
class AnnotationsNode extends Twig_Node
{
    /**
     * Compile
     *
     * @param Twig_Compiler $compiler
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $getExtension = sprintf('$this->env->getExtension(\'%s\')', ZichtFrameworkExtraExtension::class);

        $compiler
            ->write('$parent = $context;')
            ->write(sprintf('foreach (%s->getAnnotationRegistry()->getAnnotations() as $annotation) {', $getExtension))
            ->indent()
            ->write('$context[\'name\'] = $annotation[\'name\'];')
            ->write('$context[\'value\'] = $annotation[\'value\'];')
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write('}');
        $compiler->write('$context = $parent;');
    }
}
