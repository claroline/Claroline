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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @EXT\Route("/user/profile")
 */
class ProfileController extends Controller
{
    /** @var UserRepository */
    private $repository;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var ParametersSerializer */
    private $parametersSerializer;

    /**
     * ProfileController constructor.
     *
     * @DI\InjectParams({
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user"),
     *     "profileSerializer"    = @DI\Inject("claroline.serializer.profile"),
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param ObjectManager        $om
     * @param UserSerializer       $userSerializer
     * @param ProfileSerializer    $profileSerializer
     * @param ParametersSerializer $parametersSerializer
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer,
        ProfileSerializer $profileSerializer,
        ParametersSerializer $parametersSerializer
    ) {
        $this->repository = $om->getRepository(User::class);
        $this->userSerializer = $userSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->parametersSerializer = $parametersSerializer;
    }

    /**
     * Displays a user profile from its public URL or ID.
     *
     * @EXT\Route("/{user}", name="claro_user_profile")
     * @EXT\Template("ClarolineCoreBundle:user:profile.html.twig")
     *
     * @param string|int $user
     *
     * @return array
     */
    public function indexAction($user)
    {
        try {
            $profileUser = $this->repository->findOneByIdOrPublicUrl($user);
            $serializedUser = $this->userSerializer->serialize($profileUser, [Options::SERIALIZE_FACET]);

            return [
                'user' => $serializedUser,
                'facets' => $this->profileSerializer->serialize(),
                'parameters' => $this->parametersSerializer->serialize()['profile'],
            ];
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('Page not found');
        }
    }
}
