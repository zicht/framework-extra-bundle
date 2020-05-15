<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig\Compiler;
use Twig\Node\Node;
use Zicht\Bundle\FrameworkExtraBundle\Twig\Extension as ZichtFrameworkExtraExtension;

class AnnotationsNode extends Node
{
    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler)
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
