<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allows arbitrary markup to be rendered in a form
 *
 * @example
 *      Add the FrameworkExtra form_theme.html.twig to the twig config:
 *      twig:
 *           form:
 *              resources:
 *                  ....
 *                  - 'ZichtFrameworkExtraBundle::form_theme.html.twig'
 *
 *      Use in your form builder like this:
 *
 *           $formMapper->add('teaserExplanation', 'zicht_markup_type', ['inherit_data' => true, 'markup' => 'admin.help.teaser_info'])
 *
 * @package Zicht\Bundle\MokveldSiteBundle\Form\Type
 */
class MarkupType extends AbstractType
{
    /**
     * @{inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['markup' => false, 'inherit_data' => true]);
    }

    /**
     * Finish view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $view->vars['markup'] = $options['markup'];
    }


    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'zicht_markup_type';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_markup_type';
    }
}
