<?php

namespace HeVinci\FavouriteBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use HeVinci\FavouriteBundle\Entity\Favourite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FavouriteController extends Controller
{
    /**
     * @EXT\Route(
     *     "/{isFavourite}/node/{id}",
     *     name="hevinci_favourite_index"
     * )
     * @EXT\Template("HeVinciFavouriteBundle:Favourite:index.html.twig")
     */
    public function indexAction($isFavourite, ResourceNode $node)
    {
        $manager = $this->get('claroline.manager.resource_manager');
        $resource = $manager->getResourceFromNode($node);

        return array(
            'isFavourite' => $isFavourite,
            '_resource' => $resource
        );
    }

    /**
     * @EXT\Route(
     *     "/add/{node}",
     *     name="hevinci_add_favourite",
     *     requirements={"node" = "\d+"},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Method("GET")
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

        return new Response();
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

        return new Response();
    }
}