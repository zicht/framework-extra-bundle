<?php declare(strict_types=1);

namespace Zicht\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\ActivateSearchBotBlockerPass;

/**
 * Request listener that (if enabled) blocks search bots from accessing the site.
 * @see ActivateSearchBotBlockerPass
 */
class SearchBotBlockerRequestListener
{
    /** @var non-empty-string[] List of regular expressions to match against the User-Agent header */
    private array $searchBotsListPatterns = ['(bot|spider|crawler|slurp|mediapartners)'];

    /** @param non-empty-string[] $searchBotsListPatterns */
    public function setSearchBotsListPatterns(array $searchBotsListPatterns): void
    {
        $this->searchBotsListPatterns = $searchBotsListPatterns;
    }

    /** @throws AccessDeniedHttpException */
    public function onKernelRequestBlockSearchBots(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        foreach ($this->searchBotsListPatterns as $pattern) {
            if (0 < preg_match('/' . $pattern . '/i', $userAgent)) {
                throw new AccessDeniedHttpException();
            }
        }
    }
}
