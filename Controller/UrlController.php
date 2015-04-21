<?php

namespace HeVinci\UrlBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Form\UrlChangeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack
    ){
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
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
            $formInterface = $form->getData();
            $url = $formInterface->getUrl();
            $baseUrl = $this->request->getSchemeAndHttpHost() . $this->request->getScriptName();
            $baseUrlEscapeQuote = preg_quote($baseUrl);
            $formInterface->setInternalUrl(false);

            if (preg_match("#$baseUrlEscapeQuote#", $url)) {
                $formInterface->setUrl(substr($url, strlen($baseUrl)));
                $formInterface->setInternalUrl(true);
            }
            $em->flush();

            return new JsonResponse();
        }

        return array('form' => $form->createView(), 'node' => $node->getId());
    }
}