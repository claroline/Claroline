<?php

namespace Icap\PortfolioBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Event\Log\PortfolioViewEvent;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/portfolio")
 */
class PortfolioController extends Controller
{
    /**
     * @Route("/{page}/{guidedPage}", name="icap_portfolio_list", requirements={"page" = "\d+", "guidedPage" = "\d+"}, defaults={"page" = 1, "guidedPage" = 1})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function listAction(User $loggedUser, $page, $guidedPage)
    {
        $this->checkPortfolioToolAccess();

        $ownedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findByUserWithWidgets($loggedUser, false);
        $portfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($ownedPortfolioQuery, $page, 10);

        $guidedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findGuidedPortfolios($loggedUser, false);
        $guidedPortfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($guidedPortfolioQuery, $guidedPage, 10);

        return array(
            'portfoliosPager'       => $portfoliosPager,
            'guidedPortfoliosPager' => $guidedPortfoliosPager
        );
    }

    /**
     * @Route("/add", name="icap_portfolio_add")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function addAction(User $loggedUser)
    {
        $this->checkPortfolioToolAccess();

        $portfolio = new Portfolio();
        $portfolio->setUser($loggedUser);

        try {
            if ($this->getPortfolioFormHandler()->handleAdd($portfolio)) {
                $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_add_success_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_list'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_add_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_list'));
        }

        return array(
            'form'      => $this->getPortfolioFormHandler()->getAddForm()->createView(),
            'portfolio' => $portfolio
        );
    }

    /**
     * @Route("/rename/{id}", name="icap_portfolio_rename", requirements={"id" = "\d+"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function renameAction(User $loggedUser, TitleWidget $titleWidget)
    {
        $this->checkPortfolioToolAccess();

        try {
            if ($this->getPortfolioFormHandler()->handleRename($titleWidget)) {
                $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_rename_success_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_list'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_rename_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_list'));
        }

        return array(
            'form'      => $this->getPortfolioFormHandler()->getRenameForm($titleWidget)->createView(),
            'portfolio' => $titleWidget
        );
    }

    /**
     * @Route("/delete/{id}", name="icap_portfolio_delete", requirements={"id" = "\d+"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function deleteAction(User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        if ($loggedUser !== $portfolio->getUser()) {
            throw $this->createNotFoundException("Unkown user for this portfolio.");
        }

        try {
            $this->getPortfolioFormHandler()->handleDelete($portfolio);

            $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_delete_success_message', array(), 'icap_portfolio'));
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_delete_error_message', array(), 'icap_portfolio'));
        }

        return $this->redirect($this->generateUrl('icap_portfolio_list'));
    }

    /**
     * @Route("/visibility/{id}", name="icap_portfolio_update_visibility", requirements={"id" = "\d+"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function updateVisibilityAction(User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        try {
            if ($this->getPortfolioFormHandler()->handleVisibility($portfolio)) {
                $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_visibility_update_success_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_list'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_visibility_update_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_list'));
        }

        return array(
            'form'      => $this->getPortfolioFormHandler()->getVisibilityForm($portfolio)->createView(),
            'portfolio' => $portfolio
        );
    }

    /**
     * @Route("/guides/{id}", name="icap_portfolio_update_guides", requirements={"id" = "\d+"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function manageGuidesAction(User $loggedUser, Portfolio $portfolio)
    {
        try {
            if ($this->getPortfolioFormHandler()->handleGuides($portfolio)) {
                $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_guides_update_success_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_list'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_guides_update_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_list'));
        }

        return array(
            'form'      => $this->getPortfolioFormHandler()->getGuidesForm($portfolio)->createView(),
            'portfolio' => $portfolio
        );
    }

    /**
     * @Route("/{portfolioSlug}", name="icap_portfolio_view")
     */
    public function viewAction($portfolioSlug)
    {
        $this->checkPortfolioToolAccess();

        /** @var User|null $user */
        $user        = $this->getUser();
        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\TitleWidget')->findOneBySlug($portfolioSlug);
        $portfolio   = $titleWidget->getPortfolio();

        if (null === $portfolio) {
            throw $this->createNotFoundException("Unknown portfolio.");
        }

        $openingMode = $this->getPortfolioManager()->getOpeningMode($portfolio, $user, $this->get('security.context')->isgranted('ROLE_ADMIN'));

        if (null === $openingMode) {
            $portfolioVisibility = $portfolio->getVisibility();

            if (
                Portfolio::VISIBILITY_NOBODY === $portfolioVisibility
                || (
                    Portfolio::VISIBILITY_USER === $portfolioVisibility && null !== $user && !$portfolio->visibleToUser($user)
                )) {
                $response = new Response($this->renderView('IcapPortfolioBundle:Portfolio:view.error.html.twig', array('errorCode' => 403, 'portfolioSlug' => $portfolioSlug)), 403);
            }
            else if (
                    null === $user
                    && (
                        Portfolio::VISIBILITY_PLATFORM_USER === $portfolioVisibility
                        || Portfolio::VISIBILITY_USER === $portfolioVisibility
                    )) {
                $response = new Response($this->renderView('IcapPortfolioBundle:Portfolio:view.error.html.twig', array('errorCode' => 401, 'portfolioSlug' => $portfolioSlug)), 401);
            }
            else {
                throw new \LogicException("Unknow opening mode for the portfolio.");
            }
        }
        else {
            $event = new PortfolioViewEvent($portfolio);
            $this->get('event_dispatcher')->dispatch('log', $event);

            $resourceTypes = $this->get('claroline.manager.resource_manager')->getAllResourceTypes();

            $widgetsConfig       = $this->getWidgetsManager()->getWidgetsConfig();
            $responseParameters  = array(
                'titleWidget'   => $titleWidget,
                'portfolio'     => $portfolio,
                'openingMode'   => $openingMode,
                'widgetsConfig' => $widgetsConfig,
                'resourceTypes' => $resourceTypes
            );

            if (PortfolioManager::PORTFOLIO_OPENING_MODE_VIEW === $openingMode) {
                $responseParameters['cols'] = $this->getPortfolioDispositionManager()->getColumnsForDisposition($portfolio->getDisposition());
            }

            $response = new Response($this->renderView('IcapPortfolioBundle:Portfolio:view.html.twig', $responseParameters));
        }

        return $response;
    }
}
 