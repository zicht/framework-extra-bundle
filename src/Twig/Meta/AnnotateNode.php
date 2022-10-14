<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\Meta;

use Twig\Compiler;
use Twig\Node\Node;
use Zicht\Bundle\FrameworkExtraBundle\Twig\Extension as ZichtFrameworkExtraExtension;

class AnnotateNode extends Node
{
    public function compile(Compiler $compiler)
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
