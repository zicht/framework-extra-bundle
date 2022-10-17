<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Url;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zicht\Bundle\FrameworkExtraBundle\Url\UrlCheckerService;

class UrlCheckerServiceTest extends TestCase
{
    public function testEmptyUrlIsUnsafe()
    {
        $urlChecker = new UrlCheckerService($this->getRequestStackMock());
        self::assertFalse($urlChecker->isSafeUrl(null));
        self::assertFalse($urlChecker->isSafeUrl(''));
    }

    public function testConfiguredMatch()
    {
        $urlChecker = new UrlCheckerService($this->getRequestStackMock(), ['#safe#i']);
        self::assertTrue($urlChecker->isSafeUrl('this is safe'));
        self::assertFalse($urlChecker->isSafeUrl('though shallt not pass'));
    }

    /**
     * @param string[] $hosts
     * @param string[] $urls
     * @dataProvider safeUrlsProvider
     */
    public function testSafeUrls(array $hosts, array $urls)
    {
        foreach ($hosts as $host) {
            $urlChecker = new UrlCheckerService($this->getRequestStackMock($host));
            foreach ($urls as $url) {
                self::assertTrue($urlChecker->isSafeUrl($url), sprintf('Expecting host:"%s" url:"%s" to be safe', $host, $url));
                self::assertEquals($url, $urlChecker->getSafeUrl($url, 'fallback'));
            }
        }
    }

    /**
     * @param string[] $hosts
     * @param string[] $urls
     * @dataProvider unsafeUrlsProvider
     */
    public function testUnsafeUrls(array $hosts, array $urls)
    {
        foreach ($hosts as $host) {
            $urlChecker = new UrlCheckerService($this->getRequestStackMock($host));
            foreach ($urls as $url) {
                self::assertFalse($urlChecker->isSafeUrl($url), sprintf('Expecting host:"%s" url:"%s" to be unsafe', $host, $url));
                self::assertEquals('fallback', $urlChecker->getSafeUrl($url, 'fallback'));
            }
        }
    }

    /**
     * @return array
     */
    public function safeUrlsProvider()
    {
        return [
            [
                'hosts' => [
                    'nl',
                    'zicht.nl',
                    'www.zicht.nl',
                    'foo.zicht.nl',
                    'a-foo.zicht.nl',
                ],
                'urls' => [
                    '/',
                    '/foo',
                    '/foo/',
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
                    'https://foo.zicht.nl/abc',
                    'https://a-foo.zicht.nl/abc',
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
                'hosts' => [
                    'nl',
                    'zicht.nl',
                    'www.zicht.nl',
                    'foo.zicht.nl',
                ],
                'urls' => [
                    // incorrect urls
                    '//',
                    '//foo',
                    '//foo/',
                    'foo',
                    'foo/',

                    // external urls
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

                    // urls containing javascript
                    'javascript:window.location=46759602//*/window.location=46759602/*',
                ],
            ],
        ];
    }

    /**
     * @param string $host
     * @return RequestStack&MockObject
     */
    private function getRequestStackMock($host = 'localhost')
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods(['getHost'])->getMock();
        $requestMock->method('getHost')->willReturn($host);

        $requestStackMock = $this->getMockBuilder(RequestStack::class)->disableOriginalConstructor()->setMethods(['getMasterRequest'])->getMock();
        $requestStackMock->method('getMasterRequest')->willReturn($requestMock);

        return $requestStackMock;
    }
}
