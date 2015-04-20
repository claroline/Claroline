<?php

namespace HeVinci\UrlBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Form\UrlChangeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class UrlController extends Controller
{
    private $formFactory;
    private $om;
    private $request;
    private $templating;
    private $manager;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "templating"         = @DI\Inject("templating"),
     *     "manager"            = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        TwigEngine $templating,
        ResourceManager $manager
    ){
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->manager = $manager;
    }

    /**
     * @EXT\Route(
     *     "/change/{node}",
     *     name="hevinci_url_change",
     *     options={"expose"=true}
     * )
     * @EXT\Template("HeVinciUrlBundle:Url:form.html.twig")
     */
    public function changeUrlAction(ResourceNode $node)
    {
        $em = $this->getDoctrine()->getManager();
        $url = $em->getRepository('HeVinciUrlBundle:Url')
            ->findOneBy(array('resourceNode' => $node->getId()));

        if (!$url){
            throw new \Exception("This resource doesn't exist.");
        }

        $form = $this->formFactory->create(new UrlChangeType(), $url);
        $form->handleRequest($this->request);

        if ($form->isValid()){
            $em->flush();

            return new JsonResponse();
        }

        return array('form' => $form->createView(), 'node' => $node->getId());
    }
}