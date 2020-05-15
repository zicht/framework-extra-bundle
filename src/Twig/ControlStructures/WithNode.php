<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Represents a 'with' node.
 */
class WithNode extends Node
{
    /**
     * @param array $items
     * @param array $body
     * @param int $options
     * @param null|string $line
     * @param string $tag
     */
    public function __construct($items, $body, $options, $line, $tag)
    {
        parent::__construct(
            ['body' => $body],
            ['items' => $items, 'options' => $options],
            $line,
            $tag
        );
    }


    /**
     * Checks if an option is set.
     *
     * @param string $value
     * @return bool
     */
    public function hasOption($value)
    {
        return
            $this->getAttribute('options')
            && in_array($value, $this->getAttribute('options'));
    }


    /**
     * @param \Twig_Compiler $compiler
     * @return void
     */
    public function compile(Compiler $compiler)
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

        $compiler->write('$values = array_merge(' . "\n")->indent();
        $i = 0;
        foreach ($this->getAttribute('items') as $argument) {
            if ($i++ > 0) {
                $compiler->raw(',' . "\n");
            }
            $this->compileArgument($compiler, $argument);
        }
        $compiler->raw("\n")->outdent()->write(");\n");

        if (!$this->hasOption('always')) {
            $compiler->write(
                'if (count(array_filter($values, function($o) {
                    if ($o instanceof \Countable) {
                        return count($o) > 0;
                    } else {
                        return !empty($o);
                    }
                }))) {'
            );
        }

        if ($this->hasOption('merged')) {
            $compiler->write('$values += $context;' . "\n");
        } else {
            $compiler->write('$values += array(\'_parent\' => $context);');
        }

        $compiler
            ->write('$context = $values;')
            ->subcompile($this->getNode('body'));

        if (!$this->hasOption('always')) {
            $compiler->write('}');
        }
        $compiler->write('$context = array_pop($withStack);' . "\n");
    }


    /**
     * @param Twig_Compiler $compiler
     * @param mixed $argument
     * @return void
     */
    public function compileArgument($compiler, $argument)
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
