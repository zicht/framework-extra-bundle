<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Node;
use Twig_Compiler;

class AnnotateNode extends Twig_Node
{
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler->write('$this->env->getExtension(\'zicht_framework_extra\')->getAnnotationRegistry()');
        if ($this->hasNode('name')) {
            $compiler->raw('->addAnnotation(')->subcompile($this->getNode('name'))->raw(', ');
        } else {
            $compiler->raw('->addAnnotations(');
        }
        $compiler->subcompile($this->getNode('expr'));
        $compiler->write(');' . "\n");
    }
}