<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\Organization\OrganizationRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Repository\CourseRepository;
use Claroline\CursusBundle\Repository\CursusRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.cursus")
 * @DI\Tag("claroline.serializer")
 */
class CursusSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CourseRepository */
    private $courseRepo;
    /** @var CursusRepository */
    private $cursusRepo;
    /** @var OrganizationRepository */
    private $organizationRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * CourseSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"   = @DI\Inject("claroline.api.serializer"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param ObjectManager         $om
     * @param SerializerProvider    $serializer
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;

        $this->courseRepo = $om->getRepository('Claroline\CursusBundle\Entity\Course');
        $this->cursusRepo = $om->getRepository('Claroline\CursusBundle\Entity\Cursus');
        $this->organizationRepo = $om->getRepository('Claroline\CoreBundle\Entity\Organization\Organization');
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/cursus/cursus.json';
    }

    /**
     * @param Cursus $cursus
     * @param array  $options
     *
     * @return array
     */
    public function serialize(Cursus $cursus, array $options = [])
    {
        $serialized = [
            'id' => $cursus->getUuid(),
            'code' => $cursus->getCode(),
            'title' => $cursus->getTitle(),
            'description' => $cursus->getDescription(),
            'parent' => $cursus->getParent() ? $this->serialize($cursus->getParent(), [Options::SERIALIZE_MINIMAL]) : null,
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'course' => $cursus->getCourse() ?
                        $this->serializer->serialize($cursus->getCourse(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'workspace' => $cursus->getWorkspace() ?
                        $this->serializer->serialize($cursus->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'order' => $cursus->getCursusOrder(),
                    'icon' => $cursus->getIcon(),
                    'blocking' => $cursus->isBlocking(),
                    'color' => $cursus->getColor(),
                ],
                'structure' => [
                    'root' => $cursus->getRoot(),
                    'lvl' => $cursus->getLvl(),
                    'lft' => $cursus->getLft(),
                    'rgt' => $cursus->getRgt(),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array  $data
     * @param Cursus $cursus
     *
     * @return Cursus
     */
    public function deserialize($data, Cursus $cursus)
    {
        $this->sipe('id', 'setUuid', $data, $cursus);
        $this->sipe('code', 'setCode', $data, $cursus);
        $this->sipe('title', 'setTitle', $data, $cursus);
        $this->sipe('description', 'setDescription', $data, $cursus);
        $this->sipe('meta.order', 'setCursusOrder', $data, $cursus);
        $this->sipe('meta.blocking', 'setBlocking', $data, $cursus);
        $this->sipe('meta.icon', 'setIcon', $data, $cursus);
        $this->sipe('meta.color', 'setColor', $data, $cursus);

        $parent = isset($data['parent']['id']) ?
            $this->cursusRepo->findOneBy(['uuid' => $data['parent']['id']]) :
            null;
        $cursus->setParent($parent);

        $course = isset($data['meta']['course']['id']) ?
            $this->courseRepo->findOneBy(['uuid' => $data['meta']['course']['id']]) :
            null;
        $cursus->setCourse($course);

        $workspace = isset($data['meta']['workspace']['uuid']) ?
            $this->workspaceRepo->findOneBy(['uuid' => $data['meta']['workspace']['uuid']]) :
            null;
        $cursus->setWorkspace($workspace);

        $organizations = $cursus->getOrganizations()->toArray();

        // If Cursus is associated to no organization, initializes it with organizations administrated by authenticated user
        // or at last resort with default organizations
        if (0 === count($organizations)) {
            $user = $this->tokenStorage->getToken()->getUser();
            $useDefaultOrganizations = false;

            if ('anon.' !== $user) {
                $userOrganizations = $user->getAdministratedOrganizations()->toArray();

                if (0 < count($userOrganizations)) {
                    foreach ($userOrganizations as $organization) {
                        $cursus->addOrganization($organization);
                    }
                } else {
                    $useDefaultOrganizations = true;
                }
            } else {
                $useDefaultOrganizations = true;
            }
            // Initializes Cursus with default organizations if no others organization is found
            if ($useDefaultOrganizations) {
                $defaultOrganizations = $this->organizationRepo->findBy(['default' => true]);

                foreach ($defaultOrganizations as $organization) {
                    $cursus->addOrganization($organization);
                }
            }
        }

        return $cursus;
    }
}
