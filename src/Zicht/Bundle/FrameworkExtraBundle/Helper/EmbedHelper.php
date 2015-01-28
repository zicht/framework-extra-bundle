<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Helper;

use \Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\Form\FormError;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\Form\Form;
use \Symfony\Component\Form\FormInterface;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use \Zicht\Bundle\FrameworkExtraBundle\Http\JsonResponse;
use \Symfony\Component\Form\FormErrorIterator;

/**
 * Helper class to facilitate embedded forms in ESI with redirection handling.
 */
class EmbedHelper
{
    /**
     * Service container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Whether or not to consider exceptions thrown by the handler as formerrors.
     *
     * @var bool
     */
    protected $isMarkExceptionsAsFormErrors;


    /**
     * Construct the helper with the service container.
     *
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param bool $markExceptionsAsFormErrors
     */
    public function __construct(Container $container, $markExceptionsAsFormErrors = false)
    {
        $this->container = $container;
        $this->isMarkExceptionsAsFormErrors = $markExceptionsAsFormErrors;
    }


    /**
     * Generate an embedded url, adding the embedded parameters to the url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function url($route, $params)
    {
        // use array filter to remove keys with null values
        $params += array_filter($this->getEmbedParams());

        return $this->container->get('router')->generate($route, $params);
    }


    /**
     * Returns the parameters to add to an embedded url from the current request.
     *
     * @return array
     */
    public function getEmbedParams()
    {
        $params = array('return_url' => null, 'success_url' => null, 'do' => null);

        if ($returnUrl = $this->container->get('request')->get('return_url')) {
            $params['return_url'] = $returnUrl;
        }
        if ($returnUrl = $this->container->get('request')->get('success_url')) {
            $params['success_url'] = $returnUrl;
        }
        // eg: do=change
        if ($doAction = $this->container->get('request')->get('do')) {
            $params['do'] = $doAction;
        }

        return $params;
    }


    /**
     * Handles a form and executes a callback to do definite handling.
     *
     * The embed helper utilizes a way to store the form state of a form after submitting, so when including a form
     * in an ESI, the form state is kept until the next display of the form. When using the handleForm with
     * XmlHttpRequests, the form state is not stored, since it is assumed the response will be used to display the
     * data directly.
     *
     * The return value is either a Response object that can be returned as the result of the action, or it is an
     * array which can be used in a template.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \callback $handlerCallback
     * @param string $formTargetRoute
     * @param array $formTargetParams
     * @param array $extraViewVars
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function handleForm(Form $form, Request $request, $handlerCallback, $formTargetRoute,
                        $formTargetParams = array(), $extraViewVars = array())
    {

        $formId = $this->getFormId($form);

        if ($request->hasPreviousSession()) {
            // cannot store errors iterator in session because somewhere there is a closure that can't be serialized
            // therefore convert the errors iterator to array, on get from session convert to iterator
            // see [1]
            $formState = $request->getSession()->get($formId);
            $formState['form_errors'] = is_array($formState['form_errors']) ? $formState['form_errors'] : array();
            $formState['form_errors'] = new FormErrorIterator($form, $formState['form_errors']);
        } else {
            $formState = null;
        }
        $formStatus = '';

        // This only binds the form, so the event listeners are triggered, but no actual submit-handling is done.
        // This is useful for AJAX requests which need to modify the form based on submitted data.
        if ($request->get('_submit_type') === 'bind') {
            $form->submit($request);
        } elseif ($request->getMethod() == 'POST') {
            $form->submit($request);

            $returnUrl     = $request->get('return_url');
            $successUrl    = $request->get('success_url');

            $handlerResult = false;

            // if it is valid, we can use the callback to handle the actual handling
            if ($form->isValid()) {
                try {
                    $handlerResult = call_user_func($handlerCallback, $request, $form, $this->container);
                } catch (\Exception $e) {
                    if (!$this->isMarkExceptionsAsFormErrors) {
                        throw $e;
                    } else {
                        $form->addError($this->convertExceptionToFormError($e));
                    }
                }

                if ($handlerResult) {
                    // any lingering errors may be removed now.
                    unset($formState['has_errors']);
                    unset($formState['data']);
                    unset($formState['form_errors']);
                    $formStatus = 'ok';

                    if ($handlerResult && $handlerResult instanceof Response) {
                        return $handlerResult;
                    } elseif (is_array($handlerResult)) {
                        $extraViewVars = $handlerResult + $extraViewVars;
                    }
                    if ($successUrl) {
                        $returnUrl = $successUrl;
                        if ($request->isXmlHttpRequest()) {
                            return new JsonResponse(array('success_url' => $successUrl));
                        }
                    }
                } else {
                    $formStatus = 'errors';

                    $formState['has_errors']  = true;
                    $formState['data']        = $request->request->get($form->getName());
                    $formState['form_errors'] = $form->getErrors();
                }
            } else {
                $formStatus = 'errors';

                $formState['has_errors']  = true;
                $formState['data']        = $request->request->get($form->getName());
                $formState['form_errors'] = $form->getErrors();
            }
            // redirect to the return url, if available
            if ($returnUrl && !$request->isXmlHttpRequest()) {
                $response = new RedirectResponse($returnUrl);
            }
        } elseif (!empty($formState['has_errors'])) {
            $formStatus = 'errors';

            // see if there were any errors left in the session from a previous post, so we show them
            if (!empty($formState['data']) && is_array($formState['data'])) {
                $form->bind($formState['data']);
                unset($formState['data']);
            }
            if (!empty($formState['form_errors'])) {
                foreach ($formState['form_errors'] as $error) {
                    $form->addError($error);
                }
            }
            // and we only show them once.
            unset($formState['has_errors']);
            unset($formState['form_errors']);
        }
        if ($formState && !$request->isXmlHttpRequest()) {
            // see [1] for explanation
            $formState['form_errors'] = iterator_to_array($formState['form_errors']);
            $request->getSession()->set($formId, $formState);
        } elseif ($request->hasPreviousSession()) {
            $request->getSession()->remove($formId);
        }

        $viewVars = $extraViewVars;

        if (empty($response)) {
            if ($request->get('extension')) {
                $formTargetParams += array(
                    'extension' => $request->get('extension')
                );
            }
            $viewVars['form_status'] = $formStatus;

            $viewVars['form_url'] = $this->url($formTargetRoute, $formTargetParams);
            $viewVars['form']     = $form->createView();
            if ($request->hasPreviousSession()) {
                $viewVars['flash']    = $request->getSession()->getBag('flashes')->get($form->getName());
            } else {
                $viewVars['flash']    = '';
            }

            return $viewVars;
        }

        return $response;
    }


    /**
     * Returns the ID the form's state is stored by in the session
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @return mixed
     */
    public function getFormId(FormInterface $form)
    {
        if (is_object($form->getData())) {
            $ret = preg_replace('/\W/', '_', get_class($form->getData()));
        } else {
            if ($form->getName()) {
                return (string)$form->getName();
            } else {
                return preg_replace('/\W/', '_', get_class($form));
            }
        }
        
        return $ret;
    }


    /**
     * @param $markExceptionsAsFormErrors
     */
    public function setMarkExceptionsAsFormErrors($markExceptionsAsFormErrors)
    {
        $this->isMarkExceptionsAsFormErrors = $markExceptionsAsFormErrors;
    }


    /**
     * Provides a way of customizing error messages based on type of exception, etc.
     *
     * @param \Exception $exception
     * @return FormError
     */
    protected function convertExceptionToFormError($exception)
    {
        return new FormError($exception->getMessage());
    }
}