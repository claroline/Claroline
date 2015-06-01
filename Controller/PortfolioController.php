<?php

namespace Icap\PortfolioBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\ImportData;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Event\Log\PortfolioViewEvent;
use Icap\PortfolioBundle\Exporter\Exporter;
use Icap\PortfolioBundle\Form\Type\PortfolioImport;
use Icap\PortfolioBundle\Manager\ImportManager;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/portfolio")
 */
class PortfolioController extends Controller
{
    /**
     * @Route("/{page}/{guidedPage}/{portfolioSlug}", name="icap_portfolio_index", requirements={"page" = "\d+", "guidedPage" = "\d+"}, defaults={"page" = 1, "guidedPage" = 1, "portfolioSlug" = null})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function indexAction(User $loggedUser, $page, $guidedPage, $portfolioSlug)
    {
        $this->checkPortfolioToolAccess();

        $ownedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findByUserWithWidgetsAndComments($loggedUser, false);
        $portfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($ownedPortfolioQuery, $page, 10);

        $guidedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findGuidedPortfolios($loggedUser, false);
        $guidedPortfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($guidedPortfolioQuery, $guidedPage, 10);

        $importManager          = $this->getImportManager();
        $availableImportFormats = $importManager->getAvailableFormats();

        $portfolioId = 0;

        if (null !== $portfolioSlug) {
            /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
            $titleWidget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\TitleWidget')->findOneBySlug($portfolioSlug);

            if (null === $titleWidget) {
                throw $this->createNotFoundException();
            }

            $portfolioId = $titleWidget->getPortfolio()->getId();
        }

        return array(
            'portfoliosPager' => $portfoliosPager,
            'guidedPortfoliosPager' => $guidedPortfoliosPager,
            'availableImportFormats' => $availableImportFormats,
            'portfolioId' => $portfolioId
        );
    }

    /**
     * @Route("/add", name="icap_portfolio_add")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function addAction(Request $request, User $loggedUser)
    {
        $this->checkPortfolioToolAccess();

        $portfolio = new Portfolio();
        $portfolio->setUser($loggedUser);

        try {
            if ($this->getPortfolioFormHandler()->handleAdd($portfolio)) {
                if ($request->isXmlHttpRequest()) {
                    $ownedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findByUserWithWidgetsAndComments($loggedUser, false);
                    /** @var \Icap\PortfolioBundle\Entity\Portfolio[] $portfoliosPager */
                    $portfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($ownedPortfolioQuery, 1, 10);

                    $guidedPortfolioQuery = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Portfolio')->findGuidedPortfolios($loggedUser, false);
                    $guidedPortfoliosPager = $this->get('claroline.pager.pager_factory')->createPager($guidedPortfolioQuery, 1, 10);

                    $importManager          = $this->getImportManager();
                    $availableImportFormats = $importManager->getAvailableFormats();

                    $parameters = array(
                        'portfoliosPager' => $portfoliosPager,
                        'guidedPortfoliosPager' => $guidedPortfoliosPager,
                        'availableImportFormats' => $availableImportFormats,
                        'portfolioId' => 0
                    );

                    $html = $this->renderView('IcapPortfolioBundle:Portfolio:list_content.html.twig', $parameters);

                    return new Response($html);
                }
                else {
                    $this->getSessionFlashbag()
                        ->add('success', $this->getTranslator()
                            ->trans('portfolio_add_success_message', array(), 'icap_portfolio'));

                    return $this->redirect($this->generateUrl('icap_portfolio_index'));
                }
            }
        } catch (\Exception $exception) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse('Erreur', 500);
            }
            else {
                $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_add_error_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_index'));
            }
        }

        return array(
            'form' => $this->getPortfolioFormHandler()->getAddForm()->createView(),
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

                return $this->redirect($this->generateUrl('icap_portfolio_index'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_rename_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_index'));
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

        return $this->redirect($this->generateUrl('icap_portfolio_index'));
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

                return $this->redirect($this->generateUrl('icap_portfolio_index'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_visibility_update_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_index'));
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

                return $this->redirect($this->generateUrl('icap_portfolio_index'));
            }
        } catch (\Exception $exception) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_guides_update_error_message', array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_index'));
        }

        return array(
            'form'      => $this->getPortfolioFormHandler()->getGuidesForm($portfolio)->createView(),
            'portfolio' => $portfolio
        );
    }

    /**
     * @Route("/export/{portfolioSlug}.{format}", name="icap_portfolio_export")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function exportAction($portfolioSlug, $format)
    {
        $this->checkPortfolioToolAccess();

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->getDoctrine()->getRepository('IcapPortfolioBundle:Widget\TitleWidget')->findOneBySlug($portfolioSlug);
        $portfolio   = $titleWidget->getPortfolio();

        if (null === $portfolio) {
            throw $this->createNotFoundException("Unknown portfolio.");
        }

        $portfolioExporter = new Exporter($this->get('templating'));

        $response = new Response($portfolioExporter->export($portfolio, $format));
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/import/{format}", name="icap_portfolio_import")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function importFormAction(Request $request, User $loggedUser, $format = null)
    {
        $this->checkPortfolioToolAccess();

        $importManager = $this->getImportManager();
        $importData    = new ImportData();
        $importData->setFormat($format);

        /** @var  $form */
        $form = $form = $this->createForm(
            new PortfolioImport($importManager->getAvailableFormats())
        );
        $form->setData($importData);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                try {
                    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                    $file = $importData->getContent();

                    $portfolio = $importManager->simulateImport(file_get_contents($file->getPathName()), $loggedUser, $importData->getFormat());
                    $previewId = uniqid();
                    $temporaryImportFilePath = sprintf("%s-%s-%s.%s", strtolower($loggedUser->getUsername()), date("Y_m_d\TH_i_s\Z"), $previewId, $importData->getFormat()) . '.import';
                    $file->move(sys_get_temp_dir(), $temporaryImportFilePath);

                    return $this->redirect($this->generateUrl('icap_portfolio_import_preview', ['format' => $importData->getFormat(), 'previewId' => $previewId]));
                } catch (\Exception $exception) {
                    $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_import_error_message', array(), 'icap_portfolio'));

                    return $this->redirect($this->generateUrl('icap_portfolio_import', ['format' => $format]));
                }
            }
        }

        return [
            'form'      => $form->createView(),
            'portfolio' => $importData
        ];
    }

    /**
     * @Route("/import/{format}/{previewId}", name="icap_portfolio_import_preview")
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @Template()
     */
    public function importPreviewAction(Request $request, User $loggedUser, $format, $previewId)
    {
        $this->checkPortfolioToolAccess();

        $temporaryImportFilePathToSearch = sprintf("%s-*-%s.%s.import", strtolower($loggedUser->getUsername()), $previewId, $format);

        $finder = new Finder();
        $files = $finder->files()->in(sys_get_temp_dir())->depth('0')->name($temporaryImportFilePathToSearch);
        $filesCount = $files->count();
        $errorMessage = null;

        if (0 === $filesCount) {
            $errorMessage = 'portfolio_import_error_import_file_not_found_message';
        }
        else if (1 < $filesCount) {
            $errorMessage = 'portfolio_import_error_import_too_many_file_message';
        }

        if (null !== $errorMessage) {
            $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans($errorMessage, array(), 'icap_portfolio'));

            return $this->redirect($this->generateUrl('icap_portfolio_import', ['format' => $format]));
        }

        if ($request->isMethod('POST')) {
            try {
                foreach ($files as $file) {
                    $importManager = $this->getImportManager();
                    $importManager->setEntityManager($this->getEntityManager());

                    $portfolio = $importManager->doImport($file->getContents(), $loggedUser, $format);
                }
                $this->getSessionFlashbag()->add('success', $this->getTranslator()->trans('portfolio_import_success_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_index'));
            } catch(\Exception $exception){
                $this->getSessionFlashbag()->add('error', $this->getTranslator()->trans('portfolio_import_error_message', array(), 'icap_portfolio'));

                return $this->redirect($this->generateUrl('icap_portfolio_import', ['format' => $format]));
            }
        }

        foreach ($files as $file) {
            $portfolio = $this->getImportManager()->simulateImport($file->getContents(), $loggedUser, $format);
        }

        return [
            'previewId' => $previewId,
            'portfolio' => $portfolio
        ];
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

        $openingMode = $this->getPortfolioManager()->getOpeningMode($portfolio, $user, $this->get('security.authorization_checker')->isgranted('ROLE_ADMIN'));

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

            $responseParameters  = array(
                'titleWidget'   => $titleWidget,
                'portfolio'     => $portfolio,
                'openingMode'   => $openingMode
            );

            if (PortfolioManager::PORTFOLIO_OPENING_MODE_VIEW === $openingMode) {
                $responseParameters['widgets'] = $this->getWidgetsManager()->getByPortfolioForGridster($portfolio, true);
            }
            else {
                $responseParameters['widgetsConfig'] = $this->getWidgetsManager()->getWidgetsConfig();
                $responseParameters['resourceTypes'] = $this->get('claroline.manager.resource_manager')->getAllResourceTypes();
            }

            $response = new Response($this->renderView('IcapPortfolioBundle:Portfolio:view.html.twig', $responseParameters));
        }

        return $response;
    }
}
 