<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider;

interface DelegatorDependentProviderInterface
{
    public function setDelegatorRefProvider(DelegatorRefProvider $provider);
}
