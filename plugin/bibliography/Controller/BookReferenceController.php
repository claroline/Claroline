<?php

namespace Icap\BibliographyBundle\Controller;

use Icap\BibliographyBundle\Entity\BookReference;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;
use Icap\BibliographyBundle\Form\BookReferenceConfigurationType;
use Icap\BibliographyBundle\Manager\BookReferenceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BookReferenceController extends Controller
{
    private $formFactory;
    private $request;
    private $manager;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "manager"      = @DI\Inject("icap.bookReference.manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        BookReferenceManager $manager
    ) {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
    }

    /**
     * @EXT\Route(
     *     "/configure/form",
     *     name="icap_bibliography_config_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function pluginConfigureFormAction()
    {
        $config = $this->manager->getConfig();
        $form = $this->container->get('form.factory')->create(new BookReferenceConfigurationType(), $config);

        return $this->render(
            'IcapBibliographyBundle:BookReference:options.form.html.twig',
            ['form' => $form->createView(), 'id' => $config->getId()]
        );
    }

    /**
     * @EXT\Route("/update/configuration/{id}", name="icap_bibliography_config_save")
     * @EXT\ParamConverter("config", class="IcapBibliographyBundle:BookReferenceConfiguration")
     * @EXT\Method("POST")
     */
    public function updateConfigurationAction(BookReferenceConfiguration $config, Request $request)
    {
        $postData = $request->request->get('icap_bibliography_configuration');
        $isUpdated = $this->manager->updateConfiguration($config, $postData);

        if ($isUpdated) {
            $msg = $this->get('translator')->trans('config_update_success', [], 'icap_bibliography');
            $this->get('session')->getFlashBag()->set('success', $msg);
        } else {
            $msg = $this->get('translator')->trans('config_update_error', [], 'icap_bibliography');
            $this->get('session')->getFlashBag()->set('error', $msg);
        }

        return $this->redirectToRoute('icap_bibliography_config_form');
    }
}
