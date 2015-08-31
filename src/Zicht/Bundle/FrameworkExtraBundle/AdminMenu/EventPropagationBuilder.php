<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\AdminMenu;

use \Symfony\Component\EventDispatcher\Event;
use \Sonata\AdminBundle\Admin\Pool;
use \Zicht\Bundle\AdminBundle\Event\AdminEvents;
use \Zicht\Bundle\AdminBundle\Event\MenuEvent;
use \Zicht\Bundle\AdminBundle\Event\PropagationInterface;
use \Zicht\Bundle\PageBundle\Event\PageViewEvent;
use Zicht\Bundle\PageBundle\Url\PageUrlProvider;

/**
 * Propagates a PageView event as an AdminMenu event.
 */
class EventPropagationBuilder implements PropagationInterface
{
    /**
     * @var PageUrlProvider
     */
    private $pageUrlProvider;

    /**
     * Construct with the specified admin pool
     *
     * @param \Sonata\AdminBundle\Admin\Pool $sonata
     */
    public function __construct(PageUrlProvider $pageUrlProvider)
    {
        $this->pageUrlProvider = $pageUrlProvider;
    }


    /**
     * Build the relevant event and forward it.
     *
     * @param \Symfony\Component\EventDispatcher\Event $e
     * @return mixed|void
     */
    public function buildAndForwardEvent(Event $e)
    {
        if (!$e instanceof PageViewEvent) {
            return;
        }
        /** @var \Zicht\Bundle\PageBundle\Event\PageViewEvent $e */

        $e->getDispatcher()->dispatch(
            AdminEvents::MENU_EVENT,
            new MenuEvent(
                $this->pageUrlProvider->url($e->getPage(), array('_locale' => 'zz')),
                'Vertalingen'
            )
        );

        $e->getDispatcher()->dispatch(
            AdminEvents::MENU_EVENT,
            new MenuEvent(
                $this->pageUrlProvider->url($e->getPage()),
                'Pagina herladen'
            )
        );
    }
}