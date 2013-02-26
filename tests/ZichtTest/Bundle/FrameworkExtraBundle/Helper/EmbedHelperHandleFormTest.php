<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Helper;

use \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use \Symfony\Component\Form\FormBuilder;

class EmbedHelperHandleFormTest extends EmbedHelperTest {
    public function setUp() {
        parent::setUp();

        self::$container->set('form.factory', $this->getMock('Symfony\\Component\\Form\\FormFactory', array(), array(), '', false));
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormReturnsExpectedFormUrl() {
        $form = self::$container->get('form.factory')->create(new MockType());
        $ret = $this->helper->handleForm($form, self::$container->get('request'), function() {}, 'user_login');
        $this->assertEquals(self::$container->get('router')->generate('user_login'), $ret['form_url']);
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormReturnsExpectedFormUrlWithEmbeddedParams() {
        $form = self::$container->get('form.factory')->create(new MockType());
        $this->request->request->set('return_url', 'return url value');
        $this->request->request->set('success_url', 'success url value');
        $ret = $this->helper->handleForm($form, self::$container->get('request'), function() {}, 'user_login');

        $q = parse_url($ret['form_url'], PHP_URL_QUERY);
        parse_str($q, $params);
        $this->assertEquals($params['success_url'], 'success url value');
        $this->assertEquals($params['return_url'], 'return url value');
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillStoreFormStateIfNotXmlHttpRequest() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm($form, self::$container->get('request'), function() {return false;}, 'user_login');
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($form)));
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillNotStoreFormStateIfXmlHttpRequest() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm($form, self::$container->get('request'), function() {return false;}, 'user_login');

        $this->assertTrue($this->request->isXmlHttpRequest());
        $this->assertNull($this->session->get($this->helper->getFormId($form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormStateWillContainErrorsIfAddedByCallback() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                $form->addError(new \Symfony\Component\Form\FormError('FooBar'));
                return false;
            },
            'user_login'
        );

        $state = $this->session->get($this->helper->getFormId($form));
        $this->assertTrue($state['has_errors']);
        $this->assertInstanceOf('\Symfony\Component\Form\FormError', $state['form_errors'][0]);
        $this->assertEquals('FooBar', $state['form_errors'][0]->getMessageTemplate());
        $this->assertEquals('321', $state['data']['foo']);
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillRedirectToReturnUrlIfSuccessful() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->request->set('mock', array('foo' => '321'));
        $response = $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertEmpty($this->session->get($this->helper->getFormId($form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillRedirectToReturnUrlIfNotSuccessful() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->request->set('mock', array('foo' => '321'));
        $response = $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                return false;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillNotRedirectToReturnUrlIfSuccessfulAndXmlHttpRequest() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('mock', array('foo' => '321'));
        $response = $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );

        $this->assertNotInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillYieldJsonResponseWithSuccessUrlIfSuccessfulAndXmlHttpRequest() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $this->request->setMethod('POST');
        $this->request->query->set('success_url', 'success url');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $response = $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Zicht\Bundle\SroBundle\Http\JsonResponse', $response);
    }



    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillRestoreFormStateWhenDisplayed() {
        $form = self::$container->get('form.factory')->create(new MockType(), new MockData(), array('csrf_protection' => false));
        $state = array(
            'has_errors' => true,
            'form_errors' => array(
                new \Symfony\Component\Form\FormError("BazQuux")
            ),
            'data' => array(
                'foo' => '123456789'
            )
        );
        $this->session->set($this->helper->getFormId($form), $state);
        $response = $this->helper->handleForm(
            $form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
        $errors = $form->getErrors();
        $this->assertEquals('BazQuux', $errors[0]->getMessageTemplate());
        $this->assertEquals('123456789', $form->getData()->foo);
    }
}