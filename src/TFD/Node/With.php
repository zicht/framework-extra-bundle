<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

class TFD_Node_With extends Twig_Node
{
    function __construct($items, $body, $options, $line, $tag)
    {
        parent::__construct(
            array('body' => $body),
            array('items' => $items, 'options' => $options),
            $line,
            $tag
        );
    }


    function hasOption($value)
    {
        return
                $this->getAttribute('options')
                && in_array($value, $this->getAttribute('options'));
    }


    public function compile(Twig_Compiler $compiler)
    {
        $compiler
                ->addDebugInfo($this)
                ->write('if(!isset($withStack)) {' . "\n")
                ->indent()
                ->write('$withStack = array();' . "\n")
                ->outdent()
                ->write('}' . "\n")
                ->write("\n")
                ->write('array_push($withStack, $context);' . "\n");

        if ($this->hasOption('sandboxed')) {
            $compiler->write('$values = array();' . "\n");
        } elseif ($this->hasOption('merged')) {
            $compiler->write('$values = $context;' . "\n");
        } else {
            $compiler->write('$values = array(\'_parent\' => $context);');
        }

        $compiler
                ->write('$values = array_merge(' . "\n")
                ->indent()
                ->write('$values');
        foreach ($this->getAttribute('items') as $argument) {
            $compiler->raw(',' . "\n");
            $this->compileArgument($compiler, $argument);
        }

        $compiler
                ->raw("\n")
                ->outdent()
                ->write(");\n");
        $compiler
                ->write('$context = $values;')
                ->subcompile($this->getNode('body'))
                ->write('$context = array_pop($withStack);' . "\n");
    }


    function compileArgument($compiler, $argument)
    {
        if (empty($argument['name'])) {
            $compiler
                    ->write('(array) ')
                    ->subcompile($argument['value']);
        } else {
            $compiler
                    ->write('array(')
                    ->repr($argument['name'])
                    ->raw(' => ')
                    ->subcompile($argument['value'])
                    ->raw(')');
        }
    }


}