<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider;

use Swaggest\JsonSchema\RemoteRefProvider;
use Twig\Environment;
use Zicht\Util\Str;

class TranslateRefProvider implements RemoteRefProvider, DelegatorDependentProviderInterface
{
    /** @var Environment */
    private $twig;

    /** @var string */
    private $translationDomain;

    /** @var DelegatorRefProvider|null */
    private $provider;

    public function __construct(Environment $twig, string $translationDomain)
    {
        $this->twig = $twig;
        $this->translationDomain = $translationDomain;
        $this->provider = null;
    }

    public function setDelegatorRefProvider(DelegatorRefProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $url
     * @return \stdClass|false json_decode of $url resource content
     */
    public function getSchemaData($url)
    {
        if (preg_match('#^/json-schema-translate/[a-z]+(/.+)#', $url, $matches)) {
            $schema = $this->provider->getSchemaData($matches[1]);
            if ($schema !== false) {
                $this->generateSchemaPaths($schema, [$this->normalizeUrl($url)]);
                return json_decode(
                    $this->twig->render(
                        $this->twig->createTemplate(
                            sprintf('{%% trans_default_domain \'%s\' %%}%s', $this->translationDomain, json_encode($schema)),
                            $url
                        ),
                        ['url' => $url]
                    )
                );
            }
        }

        return false;
    }

    private function generateSchemaPaths(&$schema, array $path)
    {
        foreach ($schema as $key => $value) {
            if (is_string($value) && preg_match('/^{{\s*path\s*\|\s*trans\s*}}$/i', $value)) {
                $schema->{$key} = sprintf('{{ \'%s\'|trans }}', $this->normalizePath(array_merge($path, [$key])));
            }
            if (is_object($value)) {
                $this->generateSchemaPaths($value, array_merge($path, [$key]));
            }
        }
    }

    private function normalizeUrl(string $url): string
    {
        if (preg_match('/([^.]+)/i', basename($url), $matches)) {
            return str_replace('-', '_', $matches[1]);
        }
        return 'json_schema';
    }

    private function normalizePath(array $path): string
    {
        return join(
            '.',
            array_map(
                function (string $value) {
                    return Str::infix($value, '_');
                },
                $path
            )
        );
    }
}
