<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as BaseCRUDController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Provides some basic utility functionality for admin controllers, to be supplied as an construction parameter
 */
class CRUDController extends BaseCRUDController
{
    public function showAction($id = null)
    {
        $obj = $this->admin->getObject($id);
        if ($this->container->has('zicht_url.provider') && $this->get('zicht_url.provider')->supports($obj)) {
            return $this->redirect($this->get('zicht_url.provider')->url($obj));
        }
        return parent::showAction($id);
    }


    public function editAction($id = null)
    {
        if ($this->get('request')->get('__bind_only')) {
            return $this->bindAndRender('edit');
        }
        return parent::editAction($id);
    }


    public function createAction()
    {
        $refl = new \ReflectionClass($this->admin->getClass());
        if ($refl->isAbstract()) {
            $delegates = array();
            foreach ($this->getDoctrine()->getManager()->getClassMetadata($this->admin->getClass())->subClasses as $subClass) {
                if ($admin = $this->get('sonata.admin.pool')->getAdminByClass($subClass)) {
                    if ($admin->isGranted('CREATE')) {
                        $delegates[]= $admin;
                    }
                }
            }

            if (count($delegates)) {
                return $this->render('ZichtFrameworkExtraBundle:CRUD:create-subclass.html.twig', array('admins' => $delegates));
            }
        }


        if ($this->get('request')->get('__bind_only')) {
            return $this->bindAndRender('create');
        }
        return parent::createAction();
    }


    public function moveUpAction($id)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveUp($result);
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }


    public function moveDownAction($id)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveDown($result);
        return $this->redirect($this->getRequest()->headers->get('referer'));
    }


    protected function bindAndRender($action) {
        // the key used to lookup the template
        $templateKey = 'edit';

        if ($action == 'edit') {
            $id = $this->get('request')->get($this->admin->getIdParameter());

            $object = $this->admin->getObject($id);

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            if (false === $this->admin->isGranted('EDIT', $object)) {
                throw new AccessDeniedException();
            }
        } else {
            $object = $this->admin->getNewInstance();

            $this->admin->setSubject($object);

            /** @var $form \Symfony\Component\Form\Form */
            $form = $this->admin->getForm();
            $form->setData($object);
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => $action,
            'form'   => $view,
            'object' => $object,
        ));
    }
}