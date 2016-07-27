<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Node;
use Twig_Compiler;

/**
 * Class AnnotationsNode
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Twig\Meta
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
