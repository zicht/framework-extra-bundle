<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\FrameworkExtraBundle\Helper;

use Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper;
use Symfony\Component\Form\FormBuilder;

/**
 * @covers \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::handleForm
 */
class EmbedHelperHandleFormTest extends EmbedHelperTest
{
    protected $form;
    protected $router;


    /**
     * @return void
     */
    public function testHandleFormReturnsExpectedFormUrl()
    {
        $value = rand(1, 1000);
        $this->router->expects($this->once())->method('generate')->with('user_login')->will($this->returnValue($value));
        $ret = $this->helper->handleForm(
            $this->form,
            function () {
            },
            'user_login'
        );
        $this->assertEquals($value, $ret['form_url']);
    }

    /**
     * @return void
     */
    public function testHandleFormReturnsExpectedFormUrlWithEmbeddedParams()
    {
        $this->request->getCurrentRequest()->request->set('return_url', 'return url value');
        $this->request->getCurrentRequest()->request->set('success_url', 'success url value');
        $this->router->expects($this->once())->method('generate')
            ->with('user_login', array('return_url' => 'return url value', 'success_url' => 'success url value'))
            ->will(
                $this->returnCallback(
                    function ($route, $params) {
                        return $route . '?' . http_build_query($params);
                    }
                )
            );
        $ret = $this->helper->handleForm(
            $this->form,
            function () {
            },
            'user_login'
        );
        $q = parse_url($ret['form_url'], PHP_URL_QUERY);
        parse_str($q, $params);
        $this->assertEquals($params['success_url'], 'success url value');
        $this->assertEquals($params['return_url'], 'return url value');
    }

    /**
     * @return void
     */
    public function testHandleFormWillStoreFormStateIfNotXmlHttpRequest()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm(
            $this->form,
            function () {
                return false;
            },
            'user_login'
        );
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($this->form)));
    }

    /**
     * @return void
     */
    public function testHandleFormWillNotStoreFormStateIfXmlHttpRequest()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->helper->handleForm($this->form, function () {
            return false;
        }, 'user_login');

        $this->assertTrue($this->request->getCurrentRequest()->isXmlHttpRequest());
        $this->assertNull($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @return void
     */
    public function testHandleFormStateWillContainErrorsIfAddedByCallback()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $error = new \Symfony\Component\Form\FormError('FooBar');
        $this->form->expects($this->once())->method('addError');
        $this->form->expects($this->once())->method('getErrors')->will($this->returnValue(array($error)));
        $this->form->expects($this->any())->method('getName')->will($this->returnValue('mock'));

        $this->helper->handleForm(
            $this->form,
            function ($form) use ($error) {
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
     * @return void
     */
    public function testHandleFormWillRedirectToReturnUrlIfSuccessful()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->query->set('return_url', 'return url');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));

        $response = $this->helper->handleForm(
            $this->form,
            function ($form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertEmpty($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @return void
     */
    public function testHandleFormWillRedirectToReturnUrlIfNotSuccessful()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->request->set('return_url', 'return url');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->form->expects($this->any())->method('getName')->will($this->returnValue('mock'));
        $response = $this->helper->handleForm(
            $this->form,
            function ($form) {
                return false;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('return url', $response->headers->get('Location'));
        $this->assertNotEmpty($this->session->get($this->helper->getFormId($this->form)));
    }


    /**
     * @return void
     */
    public function testHandleFormWillNotRedirectToReturnUrlIfSuccessfulAndXmlHttpRequest()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->query->set('return_url', 'return url');
        $this->request->getCurrentRequest()->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $response = $this->helper->handleForm(
            $this->form,
            function ($form) {
                return true;
            },
            'user_login'
        );

        $this->assertNotInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }


    /**
     * @return void
     */
    public function testHandleFormWillYieldJsonResponseWithSuccessUrlIfSuccessfulAndXmlHttpRequest()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->query->set('success_url', 'success url');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));
        $this->request->getCurrentRequest()->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $response = $this->helper->handleForm(
            $this->form,
            function ($form) {
                return true;
            },
            'user_login'
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionHandlingWillThrowExceptionIfNotMarkedAsError()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));

        $this->helper->setMarkExceptionsAsFormErrors(false);
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->form->expects($this->never())->method('addError');
        $this->helper->handleForm($this->form, function () {
            throw new \Exception("foo");
        }, '');
    }

    /**
     */
    public function testExceptionHandlingWillNotThrowExceptionIfNotMarkedAsError()
    {
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->request->getCurrentRequest()->request->set('mock', array('foo' => '321'));

        $this->helper->setMarkExceptionsAsFormErrors(true);
        $this->request->getCurrentRequest()->setMethod('POST');
        $this->form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $errors = array();
        $this->form->expects($this->once())->method('addError')->will($this->returnCallback(function ($e) use (&$errors) {
            $errors[] = $e;
        }));
        $this->helper->handleForm($this->form, function () {
            throw new \Exception("foo");
        }, '');

        $this->assertInstanceOf('Symfony\Component\Form\FormError', $errors[0]);
    }
}
