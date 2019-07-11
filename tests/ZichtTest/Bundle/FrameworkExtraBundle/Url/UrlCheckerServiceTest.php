<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Url;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zicht\Bundle\FrameworkExtraBundle\Url\UrlCheckerService;

class UrlCheckerServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyUrlIsUnsafe()
    {
        $urlChecker = new UrlCheckerService($this->getRequestStack());
        self::assertFalse($urlChecker->isSafeUrl(null));
        self::assertFalse($urlChecker->isSafeUrl(''));
    }

    public function testConfiguredMatch()
    {
        $urlChecker = new UrlCheckerService($this->getRequestStack(), ['#safe#i']);
        self::assertTrue($urlChecker->isSafeUrl('this is safe'));
        self::assertFalse($urlChecker->isSafeUrl('though shallt not pass'));
    }

    /**
     * @param string $host
     * @param string[] $urls
     * @dataProvider safeUrlsProvider
     */
    public function testSafeUrls($host, array $urls)
    {
        $urlChecker = new UrlCheckerService($this->getRequestStack($host));
        foreach ($urls as $url) {
            self::assertTrue($urlChecker->isSafeUrl($url), sprintf('Expecting host:"%s" url:"%s" to be safe', $host, $url));
            self::assertEquals($url, $urlChecker->getSafeUrl($url, 'fallback'));
        }
    }

    /**
     * @param string $host
     * @param string[] $urls
     * @dataProvider unsafeUrlsProvider
     */
    public function testUnsafeUrls($host, array $urls)
    {
        $urlChecker = new UrlCheckerService($this->getRequestStack($host));
        foreach ($urls as $url) {
            self::assertFalse($urlChecker->isSafeUrl($url), sprintf('Expecting host:"%s" url:"%s" to be unsafe', $host, $url));
            self::assertEquals('fallback', $urlChecker->getSafeUrl($url, 'fallback'));
        }
    }

    /**
     * @return array
     */
    public function safeUrlsProvider()
    {
        return [
            [
                'host' => 'zicht.nl',
                'urls' => [
                    // Relative urls
                    '/',
                    '/foo',

                    // Absolute urls
                    '//www.zicht.nl',
                    '//www.zicht.nl/',
                    '//zicht.nl',
                    'http://www.zicht.nl',
                    'http://www.zicht.nl/',
                    'http://zicht.nl',
                    'http://zicht.nl/',
                    'https://www.zicht.nl',
                    'https://www.zicht.nl/',
                    'https://zicht.nl',
                    'https://zicht.nl/',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function unsafeUrlsProvider()
    {
        return [
            [
                'host' => 'zicht.nl',
                'urls' => [
                    '//example.com',
                    '//example.com/',
                    '//example.com/foo',
                    'http://example.com',
                    'http://example.com/',
                    'http://example.com/foo',
                    'https://example.com',
                    'https://example.com/',
                    'https://example.com/foo',

                    // postfix with different domain
                    '//zicht.nl.example.com',
                    '//zicht.nl.example.com/',
                    '//zicht.nl.example.com/foo',
                    'http://zicht.nl.example.com',
                    'http://zicht.nl.example.com/',
                    'http://zicht.nl.example.com/foo',
                    'https://zicht.nl.example.com',
                    'https://zicht.nl.example.com/',
                    'https://zicht.nl.example.com/foo',
                ],
            ],
        ];
    }

    /**
     * @param string $host
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private function getRequestStack($host = 'localhost')
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods(['getHost'])->getMock();
        $requestMock->method('getHost')->willReturn($host);

        $requestStackMock = $this->getMockBuilder(RequestStack::class)->disableOriginalConstructor()->setMethods(['getMasterRequest'])->getMock();
        $requestStackMock->method('getMasterRequest')->willReturn($requestMock);

        return $requestStackMock;
    }
}