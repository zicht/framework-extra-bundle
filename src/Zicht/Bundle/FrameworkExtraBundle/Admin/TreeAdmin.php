<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Admin;

use \Sonata\AdminBundle\Admin\Admin;
use \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
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
     * Override the default query builder to utilize correct sorting
     *
     * @param string $context
     * @return ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        if ($context === 'list') {
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getModelManager()->getEntityManager($this->getClass());

            /** @var $cmd \Doctrine\Common\Persistence\Mapping\ClassMetadata */
            $cmd = $em->getMetadataFactory()->getMetadataFor($this->getClass());

            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select('n')
                ->from($this->getClass(), 'n')
            ;
            if ($cmd->hasField('root')) {
                $queryBuilder->orderBy('n.root, n.lft');
            } else {
                $queryBuilder->orderBy('n.lft');
            }

            return new ProxyQuery($queryBuilder);
        }
        return parent::createQuery($context);
    }


    /**
     * @{inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('admin.tab.general')
                ->add('parent', 'zicht_parent_choice', array('required' => false, 'class' => $this->getClass()))
                ->add('title', null, array('required' => true))
            ->end()
            ->end() //needs to be done twice, since tab has a 'with' in it
        ;
    }


    /**
     * @{inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add(
                'root',
                'doctrine_orm_callback',
                array(
                    'label' => 'Sectie',
                    'callback' => function($qb, $alias, $f, $v) {
                        if ($v['value']) {
                            $qb
                                ->andWhere($alias . '.root=:root')
                                ->setParameter(':root', $v['value'])
                            ;
                        }
                    },
                    'field_type' => 'entity',
                    'field_options' => array(
                        'query_builder' => function($repo) {
                            return $repo->createQueryBuilder('t')->andWhere('t.parent IS NULL');
                        },
                        'class' => $this->getClass()
                    )
                )
            );
    }


    /**
     * @{inheritDoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('title', null, array('template' => 'ZichtAdminBundle:CRUD:tree_title.html.twig'))
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'move'   => array(
                            'template' => 'ZichtAdminBundle:CRUD:actions/move.html.twig',
                        ),
                        'show'   => array(),
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
