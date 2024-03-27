<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Twig;

use PHPUnit\Framework\TestCase;
use Twig\Extension\ExtensionInterface;
use Zicht\Bundle\FrameworkExtraBundle\Helper\AnnotationRegistry;
use Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Zicht\Bundle\FrameworkExtraBundle\Twig\Extension;
use Zicht\Util\Str;

/**
 * @covers \Zicht\Bundle\FrameworkExtraBundle\Twig\Extension
 */
class ExtensionTest extends TestCase
{
    /** @var EmbedHelper */
    protected $embedHelper;

    /** @var AnnotationRegistry */
    protected $annotationRegistry;

    /** @var ExtensionInterface */
    protected $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embedHelper = $this->getMockBuilder(EmbedHelper::class)->disableOriginalConstructor()->getMock();
        $this->annotationRegistry = $this->getMockBuilder(AnnotationRegistry::class)->getMock();

        $this->extension = new Extension($this->embedHelper, $this->annotationRegistry);
    }

    /**
     * @param string $filter
     * @param array $args
     * @param int|string|array $expect
     * @dataProvider filterData
     */
    public function testFilters($filter, $args, $expect)
    {
        $filter = $this->getFilter($filter);
        $this->assertEquals($expect, call_user_func_array($filter->getCallable(), $args));
    }

    /**
     * @param string $filterName
     * @return \Twig\TwigFilter
     */
    private function getFilter($filterName)
    {
        foreach ($this->extension->getFilters() as $filter) {
            if ($filter->getName() === $filterName) {
                return $filter;
            }
        }
        throw new \OutOfBoundsException("{$filterName} not found");
    }

    /**
     * @return array[]
     */
    public function filterData()
    {
        return [
            ['re_replace', ['ab', '/.b/', 'c'], 'c'],
            ['str_uscore', ['abcDef'], Str::uscore('abcDef')],
            ['str_uscore', ['abc def'], Str::uscore('abc def')],
            ['str_dash', ['abcDef'], Str::dash('abcDef')],
            ['str_dash', ['abc def'], Str::dash('abc def')],
            ['relative_date', [new \DateTime('-10 seconds -2 minutes')], '2 minutes ago'],
            ['relative_date', [new \DateTime('-1 hour')], '1 hour ago'],
            ['date_format', [new \DateTime(), 'ymd'], 'ymd'],
            ['date_format', [new \DateTime(), '%Y-%m-%d'], strftime('%Y-%m-%d')],
            ['floor', [1.9], 1],
            ['floor', [1.1], 1],
            ['ceil', [1.1], 2],
            ['ceil', [1.8], 2],
            ['url_to_form_params', [''], []],
            ['url_to_form_params', ['/'], []],
            ['url_to_form_params', ['/?'], []],
            ['url_to_form_params', ['/?a'], ['a' => '']], // < --- this is intended behaviour
            ['url_to_form_params', ['/?a=b'], ['a' => 'b']],
            ['url_to_form_params', ['/?a[x][y]=b'], ['a[x][y]' => 'b']],
            ['url_to_form_params', ['/?a[x][y]=b&c=1'], ['a[x][y]' => 'b', 'c' => '1']],
            ['url_to_form_params', ['/?a[x][y]=b&a[x][z]=c'], ['a[x][y]' => 'b', 'a[x][z]' => 'c']],

            ['url_strip_query', [''], ''],
            ['url_strip_query', ['/'], '/'],
            ['url_strip_query', ['/?'], '/'],
            ['url_strip_query', ['/?a'], '/'],
            ['url_strip_query', ['/?a=b'], '/'],
            ['url_strip_query', ['/?a[x][y]=b'], '/'],
            ['url_strip_query', ['/?a[x][y]=b&c=1'], '/'],
            ['url_strip_query', ['/?a[x][y]=b&a[x][z]=c'], '/'],
            ['url_strip_query', ['http://www.example.org/some/path/with=values?a[x][y]=b&a[x][z]=c'], 'http://www.example.org/some/path/with=values'],
            ['url_strip_query', ['https://www.example.org/some/path/with=values?a[x][y]=b&a[x][z]=c'], 'https://www.example.org/some/path/with=values'],
            ['url_strip_query', ['https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'], 'https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'],
            ['url_strip_query', ['https://www.example.com/some/path/with=values?a[x][y]=b&a[x][z]=c#note_that_the_hash_should_remain'], 'https://www.example.com/some/path/with=values#note_that_the_hash_should_remain'],
        ];
    }
}
