<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Node;
use Twig_Compiler;

class AnnotationsNode extends Twig_Node
{
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('$parent = $context;')
            ->write('foreach ($this->env->getExtension(\'zicht_framework_extra\')->getAnnotationRegistry()->getAnnotations() as $annotation) {')
            ->indent()
                ->write('$context[\'name\'] = $annotation[\'name\'];')
                ->write('$context[\'value\'] = $annotation[\'value\'];')
                ->subcompile($this->getNode('body'))
            ->outdent()
            ->write('}');
        $compiler->write('$context = $parent;');
    }
}