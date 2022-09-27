<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Form;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * Provides a choice type for "parent" (tree) entities.
 */
class ParentChoiceType extends AbstractType
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['class'])
            ->setDefaults(
                [
                    'data_class' => function (Options $o) {
                        return $o->offsetGet('class');
                    },
                    'required' => false
                ]
            );
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $formFactory = $builder->getFormFactory();

        $doctrine = $this->doctrine;

        // TODO implement a subscriber for this
        $createParentChoice = function ($parentId) use ($formFactory, $doctrine, $options) {
            $repo = $doctrine->getRepository($options['class']);
            $choices = [];
            if (!$parentId) {
                $list = $repo->getRootNodes();
                $choices[''] = '';
            } else {
                $parent = $repo->find($parentId);
                $choices[$parentId] = '(' . $parent . ')';
                $list = $repo->getChildren($parent, true);
            }
            foreach ($list as $item) {
                $choices[$item->getId()] = (string)$item;
            }
            return $formFactory->createNamed(
                'parent',
                ChoiceType::class,
                $parentId,
                ['choices' => array_flip($choices), 'mapped' => false, 'auto_initialize' => false, 'choice_translation_domain' => false]
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function ($e) use ($formFactory, $doctrine, $createParentChoice, $options) {
                $data = $e->getData();
                $form = $e->getForm();
                $parentId = $data['parent'];
                if (!$parentId) {
                    return;
                }
                if ($form->has('parent')) {
                    $form->remove('parent');
                }
                $form->add($createParentChoice($parentId));
                $form->setData($doctrine->getRepository($options['class'])->find($parentId));
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $e) use ($formFactory, $doctrine, $createParentChoice) {
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
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $parent = $form->getData();
        $view->vars['parents'] = [];

        if ($parent) {
            if ($parent->getId()) {
                $view->vars['parents'][] = $parent;
            }
            while ($parent = $parent->getParent()) {
                // Circular reference break
                if (in_array($parent, $view->vars['parents'])) {
                    break;
                }

                if ($parent->getId()) {
                    array_unshift($view->vars['parents'], $parent);
                } else {
                    break;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_parent_choice';
    }
}
