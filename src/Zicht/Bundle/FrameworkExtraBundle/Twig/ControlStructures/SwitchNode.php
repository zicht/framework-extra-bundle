<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Represents a 'switch' statement
 */
class SwitchNode extends Node
{
    /**
     * @param Node $cases
     * @param Node $expression
     * @param int $line
     */
    public function __construct(Node $cases, Node $expression, $line)
    {
        parent::__construct(
            [
                'expression' => $expression,
                'cases' => $cases
            ],
            [],
            $line
        );
    }


    /**
     * @param Compiler $compiler
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('switch(')
            ->subcompile($this->getNode('expression'))
            ->raw(") {\n")
            ->indent();

        $total = count($this->getNode('cases'));
        for ($i = 0; $i < $total; $i++) {
            $expr = $this->getNode('cases')->getNode($i)->getAttribute('expression');
            $body = $this->getNode('cases')->getNode($i)->getNode('body');
            if (is_null($expr)) {
                $compiler
                    ->write('default')
                    ->raw(":\n");
            } else {
                foreach ($expr as $subExpr) {
                    $compiler
                        ->write('case ')
                        ->subcompile($subExpr)
                        ->raw(":\n");
                }
            }
            $compiler->indent();
            $compiler->subcompile($body);
            if ($i + 1 >= $total || !$this->getNode('cases')->getNode($i + 1)->getAttribute('fallthrough')) {
                $compiler->write("break;\n");
            }
            $compiler->outdent();
        }

        $compiler->outdent()->write('}');
    }
}
