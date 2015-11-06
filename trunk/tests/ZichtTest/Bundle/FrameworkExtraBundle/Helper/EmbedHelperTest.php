<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Helper;

use Symfony\Component\Form\FormFactory;

use ZichtTest\Bundle\FrameworkExtraBundle\Tests\AbstractIntegrationTestCase;

class MockType extends \Symfony\Component\Form\AbstractType {
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        $builder->add('foo', 'text');
    }

    public function getName()
    {
        return 'aForm';
    }
}

class MockData {
    public $foo = '123';
}

class EmbedHelperTest extends AbstractIntegrationTestCase {
    /**
     * @var \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper
     */
    protected $helper;
    /**
     * @var \Symfony\Component\HttpFoundation\Session
     */
    protected $session;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    function setUp() {
        $this->helper = new \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper(self::$container);
        $this->session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
        $this->request = new \Symfony\Component\HttpFoundation\Request();
        $this->request->setSession($this->session);
        self::$container->set('request', $this->request);

        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')->disableOriginalConstructor()->getMock();
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        self::$container->set('router', $this->router);
    }


    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::getEmbedParams
     * @return void
     */
    function testGetEmbedParams() {
        $this->request->query->set('return_url', 'test 123');
        $this->request->query->set('success_url', 'test 321');
        $this->assertEquals(
            array(
                'success_url' => 'test 321',
                'return_url' => 'test 123',
                'do' => null
            ),
            $this->helper->getEmbedParams()
        );
    }


    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::getEmbedParams
     * @return void
     */
    function testGetEmbedParamsWithoutEmbedParams() {
        $this->assertEquals(
            array(
                'return_url' => null,
                'success_url' => null,
                'do' => null
            ),
            $this->helper->getEmbedParams()
        );
    }



    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    function testUrlGeneration() {
        $router = $this->getMock('RouterInterface', array('generate'));
        $router->expects($this->once())->method('generate')->with('test', array());
        self::$container->set('router', $router);
        $this->helper->url('test', array());
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    function testUrlGenerationWillInheritEmbedParams() {
        $router = $this->getMock('RouterInterface', array('generate'));
        $router->expects($this->once())->method('generate')->with('test', array('return_url' => 'test 123', 'success_url' => 'test 321'));
        self::$container->set('router', $router);
        $this->request->query->set('return_url', 'test 123');
        $this->request->query->set('success_url', 'test 321');
        $this->helper->url('test', array());
    }

    /**
     * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::url
     * @return void
     */
    function testUrlGenerationCanOverrideEmbedParams() {
        $router = $this->getMock('RouterInterface', array('generate'));
        $router->expects($this->once())->method('generate')->with('test', array('return_url' => 'override 123', 'success_url' => 'override 321'));
        self::$container->set('router', $router);
        $this->request->query->set('return_url', 'test 123');
        $this->request->query->set('success_url', 'test 321');
        $this->helper->url('test', array('return_url' => 'override 123', 'success_url' => 'override 321'));
    }


    function testGetFormId() {
        $form = $this->form;
        $this->assertInternalType('string', $this->helper->getFormId($form));
    }
}