<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Twig\ControlStructures;

use Twig_Compiler;

/**
 * A 'strict' node wraps the contents within a 'true' or 'false' setting for the strict_variables option.
 *
 * When set to false, the contents' variable references that don't exist will NOT throw errors.
 * When set to true, the contents' variable references that don't exist WILL throw errors.
 *
 * After the node ends, the original value the block started with (whether it was true or false) is restored.
 */
class StrictNode extends \Twig_Node
{
    /**
     * Compile
     *
     * @param Twig_Compiler $compiler
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$restoreStrict = $this->env->isStrictVariables();' . PHP_EOL)
            ->write('$setStrict = (bool)')
            ->subcompile($this->nodes['expr'])
            ->write(';');

        $compiler
            ->write('if ($setStrict) { ' . PHP_EOL)
            ->write('    $this->env->enableStrictVariables();' . PHP_EOL)
            ->write('} else {' . PHP_EOL)
            ->write('    $this->env->disableStrictVariables();' . PHP_EOL)
            ->write('}');

        $compiler->subcompile($this->nodes['body']);

        $compiler
            ->write('if ($restoreStrict) { ' . PHP_EOL)
            ->write('    $this->env->enableStrictVariables();' . PHP_EOL)
            ->write('} else {' . PHP_EOL)
            ->write('    $this->env->disableStrictVariables();' . PHP_EOL)
            ->write('}');
    }
}
