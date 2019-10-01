<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Controller\User;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @EXT\Route("/user/tracking")
 */
class TrackingController extends Controller
{
    /** @var UserRepository */
    private $userRepo;
    /** @var ResourceUserEvaluation */
    private $resourceUserEvaluationRepo;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * TrackingController constructor.
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->userRepo = $om->getRepository(User::class);
        $this->resourceUserEvaluationRepo = $om->getRepository(ResourceUserEvaluation::class);
        $this->serializer = $serializer;
    }

    /**
     * Displays a user tracking.
     *
     * @EXT\Route("/{publicUrl}", name="claro_user_tracking")
     * @EXT\Template("ClarolineCoreBundle:user:tracking.html.twig")
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
