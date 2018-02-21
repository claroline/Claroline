<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\User;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/user/tracking")
 */
class TrackingController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var UserRepository */
    private $userRepo;
    /** @var ResourceUserEvaluation */
    private $resourceUserEvaluationRepo;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * ProfileController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param PlatformConfigurationHandler $configHandler
     * @param ObjectManager                $om
     * @param SerializerProvider           $serializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $configHandler,
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configHandler = $configHandler;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->resourceUserEvaluationRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceUserEvaluation');
        $this->serializer = $serializer;
    }

    /**
     * Displays a user tracking.
     *
     * @EXT\Route("/{publicUrl}", name="claro_user_tracking")
     * @EXT\Template("ClarolineCoreBundle:User:tracking.html.twig")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param string $publicUrl
     *
     * @return array
     */
    public function indexAction($publicUrl)
    {
        $this->checkAccess();

        try {
            $user = $this->userRepo->findOneByIdOrPublicUrl($publicUrl);
            $evaluations = $this->resourceUserEvaluationRepo->findBy(['user' => $user], ['date' => 'desc']);

            return [
                'user' => $this->serializer->serialize($user),
                'evaluations' => array_map(function (ResourceUserEvaluation $rue) {
                    return $this->serializer->serialize($rue);
                }, $evaluations),
            ];
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('Page not found');
        }
    }

    private function checkAccess()
    {
        // todo check access
    }
}
