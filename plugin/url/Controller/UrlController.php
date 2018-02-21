<?php

namespace HeVinci\UrlBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use HeVinci\UrlBundle\Form\UrlChangeType;
use HeVinci\UrlBundle\Manager\UrlManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UrlController extends Controller
{
    private $formFactory;
    private $om;
    private $request;
    private $urlManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "urlManager"         = @DI\Inject("hevinci_url.manager.url")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        UrlManager $urlManager
    ) {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->urlManager = $urlManager;
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
        if (!$this->get('security.authorization_checker')->isGranted('edit', $node)) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $url = $em->getRepository('HeVinciUrlBundle:Url')
            ->findOneBy(['resourceNode' => $node->getId()]);

        if (!$url) {
            throw new \Exception("This resource doesn't exist.");
        }

        $form = $this->formFactory->create(new UrlChangeType(), $url);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->urlManager->setUrl($form->getData());
            $em->flush();

            return new JsonResponse();
        }

        return ['form' => $form->createView(), 'node' => $node->getId()];
    }
}
