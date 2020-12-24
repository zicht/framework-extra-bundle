<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider;

use Swaggest\JsonSchema\RemoteRefProvider;

class DelegatorRefProvider implements RemoteRefProvider
{
    /** @var RemoteRefProvider[] */
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;

        foreach ($this->providers as $provider) {
            if ($provider instanceof DelegatorDependentProviderInterface) {
                $provider->setDelegatorRefProvider($this);
            }
        }
    }

    /**
     * @param string $url
     * @return \stdClass|false json_decode of $url resource content
     */
    public function getSchemaData($url)
    {
        foreach ($this->providers as $provider) {
            $data = $provider->getSchemaData($url);
            if ($data !== false) {
                return $data;
            }
        }

        return false;
    }
}
