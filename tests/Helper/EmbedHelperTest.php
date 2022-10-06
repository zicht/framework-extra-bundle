<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Zicht\Bundle\FrameworkExtraBundle\Url\UrlCheckerService;
use ZichtTest\Bundle\FrameworkExtraBundle\Tests\AbstractIntegrationTestCase;

class MockType extends Form\AbstractType
{
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('foo', TextType::class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'aForm';
    }
}

class MockData
{
    /** @var string */
    public $foo = '123';
}

class EmbedHelperTest extends AbstractIntegrationTestCase
{
    /** @var EmbedHelper */
    protected $helper;

    /** @var SessionInterface */
    protected $session;

    /** @var RequestStack */
    protected $request;

    /** @var UrlCheckerService&MockObject */
    protected $urlChecker;

    /** @var Form\Form&MockObject */
    protected $form;

    /** @var RouterInterface&MockObject */
    protected $router;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->request = new RequestStack();
        $request = new Request();
        $request->setSession($this->session);
        $this->request->push($request);

        $this->urlChecker = $this->getMockBuilder(UrlCheckerService::class)->disableOriginalConstructor()->setMethods(['getSafeUrl'])->getMock();
        $this->urlChecker->method('getSafeUrl')->willReturnArgument(0);

        $this->form = $this->getMockBuilder(Form\Form::class)->disableOriginalConstructor()->getMock();
        $this->form->method('getName')->willReturn('MyForm');

        $router = $this->getMockBuilder(RouterInterface::class)->disableOriginalConstructor()->getMock();
        $this->router = $router;
        $this->helper = new EmbedHelper($router, $this->session, $this->request, $this->urlChecker);
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::getEmbedParams
     * @return void
     */
    public function testGetEmbedParams()
    {
        $this->request->getCurrentRequest()->query->set('return_url', 'test 123');
        $this->request->getCurrentRequest()->query->set('success_url', 'test 321');
        $this->assertEquals(
            [
                'success_url' => 'test 321',
                'return_url' => 'test 123',
                'do' => null,
            ],
            $this->helper->getEmbedParams()
        );
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::getEmbedParams
     * @return void
     */
    public function testGetEmbedParamsWithoutEmbedParams()
    {
        $this->assertEquals(
            [
                'return_url' => null,
                'success_url' => null,
                'do' => null,
            ],
            $this->helper->getEmbedParams()
        );
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    public function testUrlGeneration()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->with('test', []);
        $property = new \ReflectionProperty($this->helper, 'router');
        $property->setAccessible(true);
        $property->setValue($this->helper, $router);
        $this->helper->url('test', []);
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    public function testUrlGenerationWillInheritEmbedParams()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->with('test', ['return_url' => 'test 123', 'success_url' => 'test 321']);
        $property = new \ReflectionProperty($this->helper, 'router');
        $property->setAccessible(true);
        $property->setValue($this->helper, $router);
        $this->request->getCurrentRequest()->query->set('return_url', 'test 123');
        $this->request->getCurrentRequest()->query->set('success_url', 'test 321');
        $this->helper->url('test', []);
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    public function testUrlGenerationCanOverrideEmbedParams()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->with('test', ['return_url' => 'override 123', 'success_url' => 'override 321']);
        $property = new \ReflectionProperty($this->helper, 'router');
        $property->setAccessible(true);
        $property->setValue($this->helper, $router);
        $this->request->getCurrentRequest()->query->set('return_url', 'test 123');
        $this->request->getCurrentRequest()->query->set('success_url', 'test 321');
        $this->helper->url('test', ['return_url' => 'override 123', 'success_url' => 'override 321']);
    }

    public function testGetFormId()
    {
        $form = $this->form;
        $this->assertIsString($this->helper->getFormId($form));
    }
}
