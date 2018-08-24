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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Repository\CourseRepository;
use Claroline\CursusBundle\Repository\CursusRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.cursus")
 * @DI\Tag("claroline.serializer")
 */
class CursusSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var CourseSerializer */
    private $courseSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /** @var CourseRepository */
    private $courseRepo;
    /** @var CursusRepository */
    private $cursusRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * CourseSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "courseSerializer"    = @DI\Inject("claroline.serializer.cursus.course"),
     *     "workspaceSerializer" = @DI\Inject("claroline.serializer.workspace")
     * })
     *
     * @param ObjectManager       $om
     * @param CourseSerializer    $courseSerializer
     * @param WorkspaceSerializer $workspaceSerializer
     */
    public function __construct(
        ObjectManager $om,
        CourseSerializer $courseSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->om = $om;
        $this->courseSerializer = $courseSerializer;
        $this->workspaceSerializer = $workspaceSerializer;

        $this->courseRepo = $om->getRepository('Claroline\CursusBundle\Entity\Course');
        $this->cursusRepo = $om->getRepository('Claroline\CursusBundle\Entity\Cursus');
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
                        $this->courseSerializer->serialize($cursus->getCourse(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'workspace' => $cursus->getWorkspace() ?
                        $this->workspaceSerializer->serialize($cursus->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'order' => $cursus->getCursusOrder(),
                    'icon' => $cursus->getIcon(),
                    'blocking' => $cursus->isBlocking(),
                    'details' => $cursus->getDetails(),
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
        $this->sipe('meta.details', 'setDetails', $data, $cursus);

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

        return $cursus;
    }
}
