<?php

namespace Icap\PortfolioBundle\Form\Handler;

use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Event\Log\PortfolioEditEvent;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @DI\Service("icap_portfolio.form_handler.portfolio")
 */
class PortfolioHandler
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var PortfolioManager
     */
    protected $portfolioManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "request"          = @DI\Inject("request_stack"),
     *     "portfolioManager" = @DI\Inject("icap_portfolio.manager.portfolio"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        PortfolioManager $portfolioManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->portfolioManager = $portfolioManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return \Symfony\Component\Form\Form|FormInterface
     */
    public function getAddForm()
    {
        return $this->formFactory->create('icap_portfolio_title_form');
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Portfolio $portfolio
     *
     * @return \Symfony\Component\Form\Form|FormInterface
     */
    public function getRenameForm(Portfolio $portfolio)
    {
        return $this->formFactory->create('icap_portfolio_rename_form', $portfolio);
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Portfolio $portfolio
     *
     * @return \Symfony\Component\Form\Form|FormInterface
     */
    public function getVisibilityForm(Portfolio $portfolio)
    {
        return $this->formFactory->create('icap_portfolio_visibility_form', $portfolio);
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Portfolio $portfolio
     *
     * @return \Symfony\Component\Form\Form|FormInterface
     */
    public function getGuidesForm(Portfolio $portfolio)
    {
        return $this->formFactory->create('icap_portfolio_guides_form', $portfolio);
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleAdd(Portfolio $portfolio)
    {
        $form = $this->getAddForm();
        $form->setData($portfolio);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->portfolioManager->addPortfolio($portfolio);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleRename(Portfolio $portfolio)
    {
        $form = $this->getRenameForm($portfolio);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->portfolioManager->renamePortfolio($portfolio, $form->get('refreshUrl')->getData());
                $event = new PortfolioEditEvent($portfolio);
                $this->eventDispatcher->dispatch('log', $event);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleDelete(Portfolio $portfolio)
    {
        $this->portfolioManager->deletePortfolio($portfolio);
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleVisibility(Portfolio $portfolio)
    {
        $originalPortfolioUsers = $portfolio->getPortfolioUsers();
        $originalPortfolioGroups = $portfolio->getPortfolioGroups();
        $originalPortfolioTeams = $portfolio->getPortfolioTeams();
        $form = $this->getVisibilityForm($portfolio);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->portfolioManager->updateVisibility($portfolio, $originalPortfolioUsers, $originalPortfolioGroups, $originalPortfolioTeams);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleGuides(Portfolio $portfolio)
    {
        $originalPortfolioGuides = $portfolio->getPortfolioGuides();
        $form = $this->getGuidesForm($portfolio);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->portfolioManager->updateGuides($portfolio, $originalPortfolioGuides);

                return true;
            }
        }

        return false;
    }
}
