<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Helper;

use \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use \Symfony\Component\Form\FormBuilder;

class EmbedHelperHandleFormTest extends EmbedHelperTest
{
    protected $form;
    protected $router;


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormReturnsExpectedFormUrl() {
        $value = rand(1, 1000);
        $this->router->expects($this->once())->method('generate')->with('user_login')->will($this->returnValue($value));
        $ret = $this->helper->handleForm($this->form, self::$container->get('request'), function() {}, 'user_login');
        $this->assertEquals($value, $ret['form_url']);
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormReturnsExpectedFormUrlWithEmbeddedParams() {
        $this->request->request->set('return_url', 'return url value');
        $this->request->request->set('success_url', 'success url value');
        $this->router->expects($this->once())->method('generate')
            ->with('user_login', array('return_url' => 'return url value', 'success_url' => 'success url value'))
            ->will($this->returnCallback(function($route, $params) {
                return $route. '?' . http_build_query($params);
            }));
        $ret = $this->helper->handleForm($this->form, $this->request, function() {}, 'user_login');
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
        $this->request->setMethod('POST');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm($this->form, self::$container->get('request'), function() {return false;}, 'user_login');
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($this->form)));
    }

    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillNotStoreFormStateIfXmlHttpRequest() {
        $this->request->setMethod('POST');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm($this->form, self::$container->get('request'), function() {return false;}, 'user_login');

        $this->assertTrue($this->request->isXmlHttpRequest());
        $this->assertNull($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormStateWillContainErrorsIfAddedByCallback() {
        $this->request->setMethod('POST');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $error = new \Symfony\Component\Form\FormError('FooBar');
        $this->form->expects($this->once())->method('addError');
        $this->form->expects($this->once())->method('getErrors')->will($this->returnValue(array($error)));
        $this->form->expects($this->any())->method('getName')->will($this->returnValue('mock'));

        $this->helper->handleForm(
            $this->form,
            self::$container->get('request'),
            function($request, $form) use ($error) {
                $form->addError($error);
                return false;
            },
            'user_login'
        );

        $state = $this->session->get($this->helper->getFormId($this->form));
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
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));

        $response = $this->helper->handleForm(
            $this->form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertEmpty($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillRedirectToReturnUrlIfNotSuccessful() {
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->any())->method('getName')->will($this->returnValue('mock'));
        $response = $this->helper->handleForm(
            $this->form,
            self::$container->get('request'),
            function($request, $form) {
                return false;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillNotRedirectToReturnUrlIfSuccessfulAndXmlHttpRequest() {
        $this->request->setMethod('POST');
        $this->request->query->set('return_url', 'return url');
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->request->set('mock', array('foo' => '321'));
        $response = $this->helper->handleForm(
            $this->form,
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
        $this->request->setMethod('POST');
        $this->request->query->set('success_url', 'success url');
        $this->request->request->set('mock', array('foo' => '321'));
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $response = $this->helper->handleForm(
            $this->form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Zicht\Bundle\FrameworkExtraBundle\Http\JsonResponse', $response);
    }



    /**
     * @covers \Zicht\Bundle\SroBundle\Extension\EmbedHelper::handleForm
     * @return void
     */
    function testHandleFormWillRestoreFormStateWhenDisplayed() {
        $state = array(
            'has_errors' => true,
            'form_errors' => array(
                new \Symfony\Component\Form\FormError("BazQuux")
            ),
            'data' => array(
                'foo' => '123456789'
            )
        );
        $this->session->set($this->helper->getFormId($this->form), $state);
        $this->form->expects($this->once())->method('addError')->with($state['form_errors'][0]);
        $this->form->expects($this->once())->method('bind')->with($state['data']);
        $response = $this->helper->handleForm(
            $this->form,
            self::$container->get('request'),
            function($request, $form) {
                return true;
            },
            'user_login'
        );
    }
}