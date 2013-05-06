<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class TFD_Node_Unset extends Twig_Node {
    public function compile(Twig_Compiler $compiler) {
        $compiler
                ->addDebugInfo($this)
                ->write('unset(')
                ->subcompile($this->getAttribute('expr'))
                ->raw(");\n")
        ;
    }
}