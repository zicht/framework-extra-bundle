<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class TFD_Node_Switch extends Twig_Node {
    public function __construct(Twig_NodeInterface $cases, Twig_NodeInterface $expression, $line) {
        parent::__construct(
            array(
                'expression' => $expression,
                'cases' => $cases
            ),
            array(),
            $line
        );
    }


    public function compile(Twig_Compiler $compiler) {
        $compiler->addDebugInfo($this);

        $compiler
                ->write('switch(')
                ->subcompile($this->getNode('expression'))
                ->raw(") {\n")
                ->indent();

        $total = count($this->getNode('cases'));
        for($i = 0; $i < $total; $i ++ ) {
            $expr = $this->getNode('cases')->getNode($i)->getAttribute('expression');
            $body = $this->getNode('cases')->getNode($i)->getNode('body');
            if(is_null($expr)) {
                $compiler
                        ->write('default')
                        ->raw(":\n");
            } else {
                foreach($expr as $subExpr) {
                    $compiler
                            ->write('case ')
                            ->subcompile($subExpr)
                            ->raw(":\n")
                    ;
                }
            }
            $compiler->indent();
            $compiler->subcompile($body);
            if($i +1 >= $total || !$this->getNode('cases')->getNode($i+1)->getAttribute('fallthrough')) {
                $compiler->write("break;\n");
            }
            $compiler->outdent();
        }

        $compiler->outdent()->write('}');
   }
}