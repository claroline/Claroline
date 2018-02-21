<?php

namespace FormaLibre\ReservationBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\ReservationBundle\Entity\ResourceType;
use FormaLibre\ReservationBundle\Serializer\ResourceTypeSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_reservation_tool')")
 */
class ReservationAdminController extends Controller
{
    private $om;
    private $resourceTypeSerializer;

    private $resourceTypeRepo;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceTypeSerializer" = @DI\Inject("claroline.serializer.reservation.resource_type")
     * })
     *
     * @param ObjectManager          $om
     * @param ResourceTypeSerializer $resourceTypeSerializer
     */
    public function __construct(
        ObjectManager $om,
        ResourceTypeSerializer $resourceTypeSerializer
    ) {
        $this->om = $om;
        $this->resourceTypeSerializer = $resourceTypeSerializer;

        $this->resourceTypeRepo = $this->om->getRepository('FormaLibreReservationBundle:ResourceType');
    }

    /**
     * @EXT\Route(
     *      "/admin",
     *      name="formalibre_reservation_admin_index"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Template("FormaLibreReservationBundle:Admin:index.html.twig")
     *
     * @param User $user
     */
    public function indexAction(User $user = null)
    {
        return [
            'isAdmin' => !is_null($user) ? $user->hasRole('ROLE_ADMIN') : false,
            'resourceTypes' => array_map(function (ResourceType $type) {
                return $this->resourceTypeSerializer->serialize($type);
            }, $this->resourceTypeRepo->findBy([], ['name' => 'ASC'])),
        ];
    }
}
