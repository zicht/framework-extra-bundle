<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Form;

use \Doctrine\Bundle\DoctrineBundle\Registry;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\FormEvent;
use \Symfony\Component\Form\FormEvents;
use \Symfony\Component\Form\FormView;
use \Symfony\Component\Form\FormInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Symfony\Component\OptionsResolver\Options;

/**
 * Provides a choice type for "parent" (tree) entities.
 */
class ParentChoiceType extends AbstractType
{
    /**
     * Constructs the type.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('class'))
            ->setDefaults(
                array(
                    'data_class' => function (Options $o) {
                        return $o->get('class');
                    },
                    'required' => false
                )
            );
    }


    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $ff = $builder->getFormFactory();

        $doctrine = $this->doctrine;

        // TODO implement a subscriber for this
        $createParentChoice = function ($parentId) use($ff, $doctrine, $options)
        {
            $repo = $doctrine->getRepository($options['class']);
            $choices = array();
            if (!$parentId) {
                $list = $repo->getRootNodes();
                $choices[''] = '';
            } else {
                $parent = $repo->find($parentId);
                $choices[$parentId] = '(' . $parent . ')';
                $list = $repo->getChildren($parent, true);
            }
            foreach ($list as $item) {
                $choices[$item->getId()]= (string)$item;
            }
            return $ff->createNamed(
                'parent',
                'choice',
                $parentId,
                array('choices' => $choices, 'property_path' => false)
            );
        };
        $builder->addEventListener(
            FormEvents::PRE_BIND,
            function ($e) use ($ff, $doctrine, $createParentChoice, $options) {
                $data     = $e->getData();
                $form     = $e->getForm();
                $parentId = $data['parent'];
                if ($form->has('parent')) {
                    $form->remove('parent');
                }
                $form->add($createParentChoice($parentId));
                $form->setData($doctrine->getRepository($options['class'])->find($parentId));
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $e) use ($ff, $doctrine, $createParentChoice) {
                $parentId = null;
                if (null !== $e->getData()) {
                    $selectedItem = $e->getData();
                    $parentId = $selectedItem->getId();
                }
                $e->getForm()->remove('parent');
                $e->getForm()->add($createParentChoice($parentId));
            }
        );
    }


    /**
     * @{inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $parent = $form->getData();
        $view->vars['parents']= array();

        if ($parent) {
            if ($parent->getId()) {
                $view->vars['parents'][]= $parent;
            }
            while ($parent = $parent->getParent()) {
                if ($parent->getId()) {
                    array_unshift($view->vars['parents'], $parent);
                } else {
                    break;
                }
            }
        }
    }


    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'zicht_parent_choice';
    }
}