<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig_Node;
use Twig_Compiler;
use Zicht\Bundle\FrameworkExtraBundle\Twig\Extension as ZichtFrameworkExtraExtension;

class AnnotateNode extends Twig_Node
{
    /**
     * @param Twig_Compiler $compiler
     */
    public function compile(Twig_Compiler $compiler)
    {
        $prio = ($this->hasNode('prio')) ? $this->getNode('prio') : 0;

        $getExtension = sprintf('$this->env->getExtension(\'%s\')', ZichtFrameworkExtraExtension::class);

        $compiler->addDebugInfo($this);
        $compiler->write(sprintf('%s->getAnnotationRegistry()', $getExtension));

        if ($this->hasNode('name')) {
            $compiler->raw('->addAnnotation(')->subcompile($this->getNode('name'))->raw(', ');
        } else {
            $compiler->raw('->addAnnotations(');
        }

        $compiler->subcompile($this->getNode('expr'));

        if ($this->hasNode('prio')) {
            $compiler->raw(', ');
            $compiler->subcompile($prio);
        }

        $compiler->write(');' . "\n");
    }
}
