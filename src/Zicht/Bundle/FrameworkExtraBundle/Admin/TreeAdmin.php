<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Admin;

use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\AdminBundle\Datagrid\ListMapper;
use \Sonata\AdminBundle\Form\FormMapper;
use \Sonata\AdminBundle\Route\RouteCollection;
use \Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * Provides a base class for easily providing admins for tree structures.
 */
class TreeAdmin extends Admin
{
    /**
     * @{inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('parent', 'zicht_parent_choice', array('required' => false, 'class' => $this->getClass()))
                ->add('title', null, array('required' => true))
            ->end()
        ;
    }


    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('parent');
    }


    /**
     * @{inheritDoc}
     */
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);

        if (!$this->hasRequest()) {
            $this->datagridValues = array(
                '_sort_order' => 'ASC',
                '_sort_by'    => 'root,lft',
                '_per_page'   => 200
            );
        }
    }


    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('title', null, array('template' => 'ZichtFrameworkExtraBundle:CRUD:tree_title.html.twig'))
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'move'   => array(
                            'template' => 'ZichtFrameworkExtraBundle:CRUD:actions/move.html.twig',
                        ),
                        'view'   => array(),
                        'edit'   => array(),
                        'delete' => array(),
                    )
                )
            );
    }


    /**
     * @{inheritDoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('moveUp', $this->getRouterIdParameter() . '/move-up');
        $collection->add('moveDown', $this->getRouterIdParameter() . '/move-down');
    }
}