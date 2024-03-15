<?php declare(strict_types=1);

namespace ZichtTest\Bundle\FrameworkExtraBundle\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\FrameworkExtraBundle\EventListener\SearchBotBlockerRequestListener;

final class SearchBotBlockerRequestListenerTest extends TestCase
{
    /** @return array{0: SearchBotBlockerRequestListener, 1: RequestEvent} */
    private function setupSearchBotBlockerRequestListenerAndRequestEvent(string $userAgent): array
    {
        $searchBotsListPatterns = array_filter(
            file(dirname(__DIR__, 2) . '/src/Resources/config/search_bots.list', \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [],
            static fn (string $line): bool => strpos($line, '#') !== 0
        );

        $listener = new SearchBotBlockerRequestListener();
        $listener->setSearchBotsListPatterns($searchBotsListPatterns);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], [], [], [], ['HTTP_USER_AGENT' => $userAgent], null);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        return [$listener, $event];
    }

    /**
     * @dataProvider provideSearchBotUserAgents
     */
    public function testOnKernelRequestBlockSearchBots(string $userAgent): void
    {
        [$listener, $event] = $this->setupSearchBotBlockerRequestListenerAndRequestEvent($userAgent);

        $this->expectException(AccessDeniedHttpException::class);
        $listener->onKernelRequestBlockSearchBots($event);
    }

    public function provideSearchBotUserAgents(): array
    {
        return [
            'AdsBot-Google' => ['AdsBot-Google (+http://www.google.com/adsbot.html)'],
            'AdsBot-Google-Mobile' => ['Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.94 Mobile Safari/537.36 (compatible; AdsBot-Google-Mobile; +http://www.google.com/mobile/adsbot.html)'],
            'Applebot' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)'],
            'bingbot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36'],
            'DataForSeoBot' => ['Mozilla/5.0 (compatible; DataForSeoBot/1.0; +https://dataforseo.com/dataforseo-bot)'],
            'DotBot' => ['Mozilla/5.0 (compatible; DotBot/1.2; +https://opensiteexplorer.org/dotbot; help@moz.com)'],
            'DuckDuckGo-Favicons-Bot' => ['Mozilla/5.0 (compatible; DuckDuckGo-Favicons-Bot/1.0; +http://duckduckgo.com)'],
            'Gabanzabot' => ['Gabanzabot/1.1 (Gabanza Search Engine; https://www.gabanza.com)'],
            'GenomeCrawlerd' => ['Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/networks/ip-networks/deepfield/genome/)'],
            'Googlebot' => ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.94 Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
            'Googlebot-Image' => ['Googlebot-Image/1.0'],
            'IABot' => ['IABot/2.0 (+https://meta.wikimedia.org/wiki/InternetArchiveBot/FAQ_for_sysadmins) (Checking if link from Wikipedia is broken and needs removal)'],
            'magpie-crawler' => ['magpie-crawler/1.1 (robots-txt-checker; +http://www.brandwatch.net)'],
            'MJ12bot' => ['Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)'],
            'Paqlebot' => ['Mozilla/5.0 (compatible; Paqlebot/2.0; +http://www.paqle.dk/about/paqlebot)'],
            'YandexBot' => ['Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)'],
        ];
    }

    /**
     * @dataProvider provideNormalUserAgents
     */
    public function testOnKernelRequestDoNotBlockNormalUserAgents(string $userAgent): void
    {
        [$listener, $event] = $this->setupSearchBotBlockerRequestListenerAndRequestEvent($userAgent);

        $listener->onKernelRequestBlockSearchBots($event);
        // No exception should have been thrown, so the next line should be reached.
        $this->addToAssertionCount(1);
    }

    public function provideNormalUserAgents(): array
    {
        return [
            'Linux Google Chrome' => ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'],
            'Linux Firefox' => ['Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0'],
            'Mac Safari' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Safari/605.1.15'],
            'Windows Edge' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586'],
        ];
    }
}
