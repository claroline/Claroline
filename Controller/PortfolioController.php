<?php

namespace Icap\PortfolioBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
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
     * @Route("/{page}", name="icap_portfolio_list", requirements={"page" = "\d+"}, defaults={"page" = 1})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function listAction(User $loggedUser, $page)
    {
        $query = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findByUserWithWidgets($loggedUser, false);
        $pager = $this->get('claroline.pager.pager_factory')->createPager($query, $page, 10);

        return array(
            'pager' => $pager
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
     * @Route("/{portfolioSlug}", name="icap_portfolio_view")
     */
    public function viewAction($portfolioSlug)
    {
        /** @var User|null $user */
        $user        = $this->getUser();
        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\TitleWidget')->findOneBySlug($portfolioSlug);
        $portfolio   = $titleWidget->getWidgetNode()->getPortfolio();
        $editMode    = false;

        $portfolioWidgets = $this->getPortfolioWidgetTypeRepository()->findAllInArray();

        if (null === $portfolio) {
            throw $this->createNotFoundException("Unknown portfolio.");
        }
        if ($user === $portfolio->getUser()) {
            $editMode = true;
        }

        $widgetsConfig       = $this->getWidgetsManager()->getWidgetsConfig();
        $response            = new Response($this->renderView('IcapPortfolioBundle:Portfolio:view.html.twig', array('title' => $titleWidget->getTitle(), 'portfolio' => $portfolio, 'editMode' => $editMode, 'widgetsConfig' => $widgetsConfig)));
        $portfolioVisibility = $portfolio->getVisibility();

        if ($user !== $portfolio->getUser()) {
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
        }

        return $response;
    }
}
 