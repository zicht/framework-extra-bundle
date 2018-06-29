<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Helper;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Helper class to facilitate embedded forms in ESI with redirection handling.
 */
class EmbedHelper
{
    /**
     * Whether or not to consider exceptions thrown by the handler as formerrors.
     *
     * @var bool
     */
    protected $isMarkExceptionsAsFormErrors;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Session
     */
    protected $router;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * EmbedHelper constructor.
     *
     * @param RouterInterface $router
     * @param Session $session
     * @param RequestStack $requestStack
     * @param bool $markExceptionsAsFormErrors
     */
    public function __construct(RouterInterface $router, SessionInterface $session, RequestStack $requestStack, $markExceptionsAsFormErrors = false)
    {
        $this->router = $router;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->isMarkExceptionsAsFormErrors = $markExceptionsAsFormErrors;
    }

    /**
     * Get the top most (root) element of the form view
     *
     * @param FormView $formView
     * @return mixed
     */
    public static function getFormRoot($formView)
    {
        $parent = $formView;
        while (isset($parent->parent)) {
            $parent = $parent->parent;
        }
        return $parent;
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

        return $this->router->generate($route, $params);
    }


    /**
     * Returns the parameters to add to an embedded url from the current request.
     *
     * @return array
     */
    public function getEmbedParams()
    {
        return [
            'return_url' => $this->getParamFromRequest('return_url'),
            'success_url' => $this->getParamFromRequest('success_url'),
            // eg: do=change
            'do' => $this->getParamFromRequest('do')
        ];
    }

    /**
     * search for a param value in the request stack, start in current
     * and bubble up till master request when not found.
     *
     * @param string $name
     * @return mixed|null
     */
    protected function getParamFromRequest($name)
    {
        if (null !== $value = $this->requestStack->getCurrentRequest()->get($name)) {
            return $value;
        }

        if ((null !== $request = $this->requestStack->getParentRequest()) && null !== $value = $request->get($name)) {
            return $value;
        }

        if ((null !== $request = $this->requestStack->getMasterRequest()) && null !== $value = $request->get($name)) {
            return $value;
        }

        return null;
    }

    /**
     * @param Form $form
     * @param int $formId
     * @return FormErrorIterator|null
     */
    protected function getFormState(Form $form, $formId)
    {
        $request = $this->requestStack->getCurrentRequest();
        $state = null;

        if ($request->hasPreviousSession()) {
            // cannot store errors iterator in session because somewhere there is a closure that can't be serialized
            // therefore convert the errors iterator to array, on get from session convert to iterator
            // see [1]
            $state  = $request->getSession()->get($formId);
            $state['form_errors'] = is_array($state ['form_errors']) ? $state ['form_errors'] : [];
            $state['form_errors'] = new FormErrorIterator($form, $state['form_errors']);
        }

        return $state;
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
     * @param Form $form
     * @param \callback $handlerCallback
     * @param string $formTargetRoute
     * @param array $formTargetParams
     * @param array $extraViewVars
     * @param null|\callable $formIdHandler
     * @return array|Response
     * @throws \Exception
     */
    public function handleForm(Form $form, callable $handlerCallback, $formTargetRoute, $formTargetParams = [], $extraViewVars = [], $formIdHandler = null) {

        $formId = is_callable($formIdHandler) ? $formIdHandler($form) : $this->getFormId($form);
        $request = $this->requestStack->getCurrentRequest();
        $formState = $this->getFormState($form, $formId);
        $formStatus = '';

        // This only binds the form, so the event listeners are triggered, but no actual submit-handling is done.
        // This is useful for AJAX requests which need to modify the form based on submitted data.
        if ($request->get('_submit_type') === 'bind') {
            $form->handleRequest($request);
        } elseif ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $returnUrl = $request->get('return_url');
            $successUrl = $request->get('success_url');
            $handlerResult = false;

            // if it is valid, we can use the callback to handle the actual handling
            if ($form->isValid()) {
                try {
                    $handlerResult = call_user_func($handlerCallback, $form);
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
                    } else {
                        // we set a convenience flash message if there was no success url, because
                        // we will probably return to the return url re-displaying the form.
                        $this->setFlashMessage($form, 'confirmed', $request->getSession());
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
                $form->submit($formState['data']);
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
            if (!empty($formState['form_errors'])) {
                // 1. You cannot serialize or un-serialize PDO instances
                // 2. We do not want to store cause and origin in the session since these can become quite large
                foreach ($formState['form_errors'] as $key => $error) {
                    $refObject = new \ReflectionObject($error);
                    $refCauseProperty = $refObject->getProperty('cause');
                    $refCauseProperty->setAccessible(true);
                    $refCauseProperty->setValue($error, null);
                    $refOriginProperty = $refObject->getProperty('origin');
                    $refOriginProperty->setAccessible(true);
                    $refOriginProperty->setValue($error, null);
                }
            }

            // see [1] for explanation
            if (!isset($formState['form_errors'])) {
                $formState['form_errors'] = [];
            } elseif ($formState['form_errors'] instanceof \Traversable) {
                $formState['form_errors'] = iterator_to_array($formState['form_errors']);
            }

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

            $prefix = '';
            if ($root = self::getFormRoot($viewVars['form'])) {
                $prefix = sprintf('form_messages.%s.', strtolower($root->vars['name']));
            }

            $viewVars['messages'] = [];
            if ($request->hasPreviousSession() && ($messages = $this->session->getFlashBag()->get($formId))) {
                foreach ($messages as $value) {
                    $viewVars['messages'][] = $prefix . $value;
                }
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
     * @param bool $markExceptionsAsFormErrors
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

    /**
     * A generic way to set flash messages.
     *
     * When using this make sure you set the following parameter in your parameters.yml to avoid stacking of messages
     * when they are not shown or rendered in templates
     *
     *     session.flashbag.class: Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag
     *
     * Messages will be pushed to a viewVars called 'messages' see $this->handleForm
     *
     * @param Form $form
     * @param string $message
     */
    public function setFlashMessage(Form $form, $message, SessionInterface $session = null)
    {
        if (null === $session) {
            trigger_error(
                "Please do not rely on the container's instance of the session, but fetch it from the Request",
                E_USER_DEPRECATED
            );
            $session = $this->session;
        }
        $session->getFlashBag()->set($this->getFormId($form), $message);
    }
}
