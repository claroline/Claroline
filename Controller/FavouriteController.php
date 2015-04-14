<?php

namespace HeVinci\FavouriteBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use HeVinci\FavouriteBundle\Entity\Favourite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @EXT\Security("has_role('ROLE_USER')")
 */
class FavouriteController extends Controller
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct (ResourceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @EXT\Route(
     *     "/node/{id}",
     *     name="hevinci_favourite_index",
     *     requirements={"id" = "\d+"}
     * )
     *
     * @EXT\Template()
     */
    public function indexAction(ResourceNode $node)
    {
        $resource = $this->manager->getResourceFromNode($node);

        $user = $this->get('security.context')->getToken()->getUser();
        $isFavourite = $this->get('claroline.persistence.object_manager')->getRepository('HeVinciFavouriteBundle:Favourite')
            ->findOneBy(array('user' => $user, 'resourceNode' => $node));

        return array(
            'isFavourite' => $isFavourite ? 1 : 0,
            '_resource' => $resource
        );
    }

    /**
     * @EXT\Route(
     *     "/add/{node}",
     *     name="hevinci_add_favourite",
     *     requirements={"node" = "\d+"}
     * )
     *
     * @EXT\Template("HeVinciFavouriteBundle:Favourite:index.html.twig")
     */
    public function addFavouriteAction(ResourceNode $node)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $favourite = $em->getRepository('HeVinciFavouriteBundle:Favourite')
            ->findOneBy(array('user' => $user, 'resourceNode' => $node->getId()));

        if ($favourite) {
            throw new \Exception('This favourite already exists !');
        }

        $favourite = new Favourite();
        $favourite->setResourceNode($node);
        $favourite->setUser($user);
        $em->persist($favourite);
        $em->flush();

        return array(
            'isFavourite' => 1,
            '_resource' => $this->manager->getResourceFromNode($node)
        );
    }

    /**
     * @EXT\Route(
     *     "/delete/{node}",
     *     name="hevinci_delete_favourite",
     *     requirements={"node" = "\d+"},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     * @EXT\Template("HeVinciFavouriteBundle:Favourite:index.html.twig")
     */
    public function deleteFavouriteAction(ResourceNode $node)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $favourite = $em->getRepository('HeVinciFavouriteBundle:Favourite')
            ->findOneBy(array('user' => $user, 'resourceNode' => $node->getId()));

        if (!$favourite) {
            throw new \Exception("This favourite doesn't exist !");
        }

        $em->remove($favourite);
        $em->flush();

        return array(
            'isFavourite' => 0,
            '_resource' => $this->manager->getResourceFromNode($node)
        );
    }
}