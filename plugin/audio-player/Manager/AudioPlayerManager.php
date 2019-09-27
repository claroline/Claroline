<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\AudioPlayerBundle\Serializer\Resource\SectionSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.audio_player")
 */
class AudioPlayerManager
{
    /** @var ObjectManager */
    private $om;

    /** @var SectionSerializer */
    private $sectionSerializer;

    private $audioParamsRepo;
    private $sectionRepo;
    private $sectionCommentRepo;

    /**
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "sectionSerializer" = @DI\Inject("Claroline\AudioPlayerBundle\Serializer\Resource\SectionSerializer")
     * })
     *
     * @param ObjectManager     $om
     * @param SectionSerializer $sectionSerializer
     */
    public function __construct(ObjectManager $om, SectionSerializer $sectionSerializer)
    {
        $this->om = $om;
        $this->sectionSerializer = $sectionSerializer;

        $this->audioParamsRepo = $om->getRepository(AudioParams::class);
        $this->sectionRepo = $om->getRepository(Section::class);
        $this->sectionCommentRepo = $om->getRepository(SectionComment::class);
    }

    public function getAudioParams(ResourceNode $resourceNode)
    {
        $audioParams = $this->audioParamsRepo->findOneBy(['resourceNode' => $resourceNode]);

        if (!$audioParams) {
            $audioParams = new AudioParams();
            $audioParams->setResourceNode($resourceNode);
            $this->om->persist($audioParams);
            $this->om->flush();
        }

        return $audioParams;
    }

    public function deserializeSections(ResourceNode $resourceNode, $data)
    {
        $sections = $this->getManagerSections($resourceNode);
        $sectionsArray = [];
        $newSectionsIds = [];

        foreach ($sections as $section) {
            $sectionsArray[$section->getUuid()] = $section;
        }
        if (isset($data['sections'])) {
            foreach ($data['sections'] as $sectionData) {
                $section = null;

                if (isset($sectionsArray[$sectionData['id']])) {
                    $section = $sectionsArray[$sectionData['id']];
                } else {
                    $section = new Section();
                    $section->setResourceNode($resourceNode);
                }
                $section = $this->sectionSerializer->deserialize($sectionData, $section);
                $newSectionsIds[] = $section->getUuid();
                $this->om->persist($section);
            }
        }
        foreach ($sectionsArray as $uuid => $section) {
            if (!in_array($uuid, $newSectionsIds)) {
                $this->om->remove($section);
            }
        }
    }

    public function getManagerSections(ResourceNode $resourceNode)
    {
        return $this->sectionRepo->findBy(['resourceNode' => $resourceNode, 'type' => AudioParams::MANAGER_TYPE]);
    }

    public function getUserSections(ResourceNode $resourceNode, User $user)
    {
        return $this->sectionRepo->findBy([
            'resourceNode' => $resourceNode,
            'type' => AudioParams::USER_TYPE,
            'user' => $user,
        ]);
    }

    public function getSectionUserComment(Section $section, User $user)
    {
        return $this->sectionCommentRepo->findOneBy(['section' => $section, 'user' => $user]);
    }
}
