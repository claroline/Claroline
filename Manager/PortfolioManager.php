<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Pager\PagerFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioUser;
use Icap\PortfolioBundle\Entity\PortfolioEvaluator;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\WidgetNode;
use Icap\PortfolioBundle\Event\Log\PortfolioAddEvaluatorEvent;
use Icap\PortfolioBundle\Event\Log\PortfolioRemoveEvaluatorEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.portfolio")
 */
class PortfolioManager
{
    const PORTFOLIO_OPENING_MODE_VIEW     = 'view';
    const PORTFOLIO_OPENING_MODE_EVALUATE = 'evaluate';
    const PORTFOLIO_OPENING_MODE_EDIT     = 'edit';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var  \Icap\PortfolioBundle\Manager\WidgetsManager
     */
    protected $widgetsManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "widgetsManager"  = @DI\Inject("icap_portfolio.manager.widgets"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     * })
     */
    public function __construct(EntityManager $entityManager, FormFactory $formFactory, WidgetsManager $widgetsManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager   = $entityManager;
        $this->formFactory     = $formFactory;
        $this->widgetsManager  = $widgetsManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Portfolio   $portfolio
     * @param TitleWidget $titleWidget
     *
     * @throws \InvalidArgumentException
     */
    public function addPortfolio(Portfolio $portfolio, TitleWidget $titleWidget)
    {
        $titleWidget->setPortfolio($portfolio);

        $this->entityManager->persist($titleWidget);

        $this->persistPortfolio($portfolio);
    }

    /**
     * @param TitleWidget $titleWidget
     * @param bool      $refreshUrl
     */
    public function renamePortfolio(TitleWidget $titleWidget, $refreshUrl = false)
    {
        if ($refreshUrl) {
            $titleWidget->setSlug(null);
        }

        $this->entityManager->persist($titleWidget);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio                               $portfolio
     * @param Collection|PortfolioUser[]              $originalPortfolioUsers
     * @param \Doctrine\Common\Collections\Collection $originalPortfolioGroups
     */
    public function updateVisibility(Portfolio $portfolio, Collection $originalPortfolioUsers, Collection $originalPortfolioGroups)
    {
        $portfolioUsers = $portfolio->getPortfolioUsers();

        foreach ($portfolioUsers as $portfolioUser) {
            if ($originalPortfolioUsers->contains($portfolioUser)) {
                $originalPortfolioUsers->removeElement($portfolioUser);
            }
        }

        foreach ($originalPortfolioUsers as $originalPortfolioUser) {
            $this->entityManager->remove($originalPortfolioUser);
        }

        $portfolioGroups = $portfolio->getPortfolioGroups();

        foreach ($portfolioGroups as $portfolioGroup) {
            if ($originalPortfolioGroups->contains($portfolioGroup)) {
                $originalPortfolioGroups->removeElement($portfolioGroup);
            }
        }

        foreach ($originalPortfolioGroups as $originalPortfolioGroup) {
            $this->entityManager->remove($originalPortfolioGroup);
        }

        $this->persistPortfolio($portfolio);
    }

    /**
     * @param Portfolio                               $portfolio
     * @param Collection|PortfolioEvaluator[]         $originalPortfolioEvaluators
     */
    public function updateEvaluators(Portfolio $portfolio, Collection $originalPortfolioEvaluators)
    {
        $portfolioEvaluators                = $portfolio->getPortfolioEvaluators();
        /** @var PortfolioEvaluator[] $addedPortfolioEvaluatorsToNotify */
        $addedPortfolioEvaluatorsToNotify   = array();
        /** @var PortfolioEvaluator[] $removedPortfolioEvaluatorsToNotify */
        $removedPortfolioEvaluatorsToNotify = array();

        foreach ($portfolioEvaluators as $portfolioEvaluator) {
            if ($originalPortfolioEvaluators->contains($portfolioEvaluator)) {
                $originalPortfolioEvaluators->removeElement($portfolioEvaluator);
            }
            else {
                $addedPortfolioEvaluatorsToNotify[] = $portfolioEvaluator;
            }
        }

        foreach ($originalPortfolioEvaluators as $originalPortfolioUser) {
            $this->entityManager->remove($originalPortfolioUser);
            $removedPortfolioEvaluatorsToNotify[] = $originalPortfolioUser;
        }

        $this->persistPortfolio($portfolio);

        foreach ($addedPortfolioEvaluatorsToNotify as $addedPortfolioEvaluator) {
            $portfolioAddEvaluatorEvent = new PortfolioAddEvaluatorEvent($portfolio, $addedPortfolioEvaluator);
            $this->dispatch($portfolioAddEvaluatorEvent);
        }
        foreach ($removedPortfolioEvaluatorsToNotify as $removedPortfolioEvaluator) {
            $portfolioAddEvaluatorEvent = new PortfolioRemoveEvaluatorEvent($portfolio, $removedPortfolioEvaluator);
            $this->dispatch($portfolioAddEvaluatorEvent);
        }
    }

    /**
     * @param LogGenericEvent $event
     */
    protected function dispatch(LogGenericEvent $event)
    {
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param Portfolio $portfolio
     */
    public function deletePortfolio(Portfolio $portfolio)
    {
        $this->entityManager->remove($portfolio);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     */
    private function persistPortfolio(Portfolio $portfolio)
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return array
     */
    public function getPortfolioData(Portfolio $portfolio)
    {
        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[] $widgets */
        $widgets = $portfolio->getWidgets();

        $data = array(
            'id'          => $portfolio->getId(),
            'disposition' => $portfolio->getDisposition()
        );

        foreach ($widgets as $widget) {
            $widgetType = $widget->getWidgetType();

            $widgetViews = array(
                'type'   => $widgetType,
                'column' => $widget->getColumn(),
                'row'    => $widget->getRow(),
                'views'  => array(
                    'view' => $this->widgetsManager->getView($widget, $widgetType)
                )
            );

            $widgetDatas       = $widgetViews + $widget->getData();
            $data['widgets'][] = $widgetDatas;
        }

        return $data;
    }

    /**
     * @param object $data
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm($data)
    {
        return $this->formFactory->create('icap_portfolio_portfolio_form', $data);
    }

    /**
     * @param Portfolio $portfolio
     * @param array     $parameters
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function handle(Portfolio $portfolio, array $parameters)
    {
        $data           = array();
        $oldDisposition = $portfolio->getDisposition();

        $form = $this->getForm($portfolio);
        $form->submit($parameters);

        if ($form->isValid()) {
            $newDisposition = $portfolio->getDisposition();

            $this->entityManager->persist($portfolio);
            $this->entityManager->flush();

            if ($oldDisposition !== $newDisposition) {
                $this->dispositionUpdated($portfolio);
            }

            $data = array(
                'id'          => $portfolio->getId(),
                'disposition' => $portfolio->getDisposition(),
            );

            $data = $this->getPortfolioData($portfolio);

            return $data;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Portfolio $portfolio
     */
    public function dispositionUpdated(Portfolio $portfolio)
    {
        $widgets   = $portfolio->getWidgets();
        $maxColumn = $portfolio->getDisposition() + 1;

        foreach ($widgets as $widget) {
            if ($maxColumn < $widget->getColumn()) {
                $widget->setColumn($maxColumn);
            }
            $this->entityManager->persist($widget);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     * @param User|null $user
     * @param bool      $isAdmin
     *
     * @return string|null
     */
    public function getOpeningMode(Portfolio $portfolio, $user, $isAdmin = false)
    {
        $openingMode = null;

        if (null !== $user) {
            if ($user === $portfolio->getUser() || $isAdmin) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_EDIT;
            }
            elseif ($portfolio->hasEvaluator($user)) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_EVALUATE;
            }
            elseif ($portfolio->visibleToUser($user)) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_VIEW;
            }
        }
        elseif (Portfolio::VISIBILITY_EVERYBODY === $portfolio->getVisibility()) {
            $openingMode = self::PORTFOLIO_OPENING_MODE_VIEW;
        }

        return $openingMode;
    }
}