<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider;

use Swaggest\JsonSchema\RemoteRefProvider;

class WebDirRefProvider implements RemoteRefProvider
{
    /** @var string */
    private $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * @param string $url
     * @return \stdClass|false json_decode of $url resource content
     */
    public function getSchemaData($url)
    {
        if (preg_match('#^/bundles/#', $url) && is_file($this->webDir . $url)) {
            if ($data = file_get_contents($this->webDir . $url)) {
                return json_decode($data);
            }
        }

        return false;
    }
}
