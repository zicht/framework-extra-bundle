<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Url;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zicht\Bundle\ConcSroBundle\Exception\UrlFlowException;

/**
 * Provides a checker to determine if a url is safe
 *
 * By default the service will consider url's safe when they are either:
 * a) relative urls, or
 * b) absolute urls within the same domain, i.e. zicht.nl and www.zicht.nl are in the same domain.
 *
 * Alternatively the user can specify their own array with regular expressions to
 * determine whether a url is safe.
 *
 * Example:
 * ```php
 * // Redirect to success_url when the user is already logged in
 * if ($this->isUserLoggedIn()) {
 *    throw new UrlFlowException(
 *       'User is already logged in',
 *       $this->get(UrlCheckerService::class)->getSafeUrl($request->get('success_url'), '/fallback-url')
 *    );
 * }
 */
class UrlCheckerService
{
    /** @var string[] */
    protected $safeUrlMatches;

    /** @var Request|null */
    private $masterRequest;

    /**
     * @param RequestStack $requestStack
     * @param string[] $safeUrlMatches
     */
    public function __construct(RequestStack $requestStack, array $safeUrlMatches = [])
    {
        $this->masterRequest = $requestStack->getMasterRequest();
        $this->safeUrlMatches = $safeUrlMatches;

        if (empty($this->safeUrlMatches)) {
            // When no matches are configured we will accept relative urls
            // Do *not* tweak this matcher without running the unit tests
            $this->safeUrlMatches [] = '#^/([^/]|$)#';

            // When no matches are configured we will accept absolute urls to the same domain
            // Do *not* tweak this matcher without running the unit tests
            $hostParts = array_slice(explode('.', $this->masterRequest->getHost()), -2);
            $this->safeUrlMatches [] = sprintf('#^((https?://)|(/{0,2}))?([a-z0-9.]+[.])?%s(/|$)#i', join('[.]', $hostParts));
        }
    }

    /**
     * @param string|null $url
     * @return bool
     */
    public function isSafeUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        foreach ($this->safeUrlMatches as $safeUrlMatch) {
            if (preg_match($safeUrlMatch, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $url
     * @param string|null $default
     * @return string|null
     */
    public function getSafeUrl($url, $default = null)
    {
        return $this->isSafeUrl($url) ? $url : $default;
    }
}
