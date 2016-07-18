<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\HttpKernel\Bundle\Bundle;
use Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\Pass;

/**
 * Bundle entry point
 */
class ZichtFrameworkExtraBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container); 
        
        $container->addCompilerPass(new Pass());
    }

}