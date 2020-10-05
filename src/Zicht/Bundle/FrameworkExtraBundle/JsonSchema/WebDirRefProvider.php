<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema;

use Swaggest\JsonSchema\RemoteRef\BasicFetcher;
use Swaggest\JsonSchema\RemoteRefProvider;

class WebDirRefProvider extends BasicFetcher implements RemoteRefProvider
{
    /** @var string */
    private $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = $webDir;
    }

    public function getSchemaData($url)
    {
        if (preg_match('#^/bundles/#', $url) && is_file($this->webDir . $url)) {
            if ($data = file_get_contents($this->webDir . $url)) {
                return json_decode($data);
            }
        }

        return parent::getSchemaData($url);
    }
}
