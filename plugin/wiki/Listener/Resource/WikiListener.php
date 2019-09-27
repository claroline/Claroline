<?php

namespace Icap\WikiBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Icap\WikiBundle\Serializer\WikiSerializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WikiListener
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var WikiManager */
    private $wikiManager;

    /** @var SectionManager */
    private $sectionManager;

    /** @var ResourceEvaluationManager */
    private $evaluationManager;

    /**
     * WikiListener constructor.
     *
     * @param TokenStorageInterface     $tokenStorage
     * @param ObjectManager             $objectManager
     * @param SerializerProvider        $serializer
     * @param WikiManager               $wikiManager
     * @param SectionManager            $sectionManager
     * @param ResourceEvaluationManager $evaluationManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        WikiSerializer $serializer,
        WikiManager $wikiManager,
        SectionManager $sectionManager,
        ResourceEvaluationManager $evaluationManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $objectManager;
        $this->serializer = $serializer;
        $this->wikiManager = $wikiManager;
        $this->sectionManager = $sectionManager;
        $this->evaluationManager = $evaluationManager;
        $this->authorization = $authorization;
    }

    /**
     * Loads a Wiki resource.
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        /** @var Wiki $wiki */
        $wiki = $event->getResource();
        $sectionTree = $this->sectionManager->getSerializedSectionTree(
            $wiki,
            $this->tokenStorage->getToken()->getUser() instanceof User ? $this->tokenStorage->getToken()->getUser() : null,
            $this->checkPermission('EDIT', $resourceNode)
        );

        $event->setData([
            'wiki' => $this->serializer->serialize($wiki),
            'sections' => $sectionTree,
        ]);

        $event->stopPropagation();
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $this->om->flush();

        $event->stopPropagation();
    }

    /**
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Wiki $wiki */
        $wiki = $event->getResource();
        $newWiki = $this->wikiManager->copyWiki($wiki, $event->getCopy(), $this->tokenStorage->getToken()->getUser());

        $event->setCopy($newWiki);
        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
        $wiki = $exportEvent->getObject();

        $data = [
          'root' => $this->sectionManager->getSerializedSectionTree($wiki, null, true),
        ];

        $exportEvent->overwrite('_data', $data);
    }

    public function onImport(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $wiki = $event->getObject();

        $rootSection = $data['_data']['root'];
        $wiki->buildRoot();
        $root = $wiki->getRoot();

        if (isset($rootSection['children'])) {
            $children = $rootSection['children'];

            foreach ($children as $child) {
                $section = $this->importSection($child, $wiki);
                $section->setWiki($wiki);
                $section->setParent($root);

                $this->om->getRepository(Section::class)->persistAsLastChildOf($section, $root);
            }
        }
    }

    private function importSection(array $data = [], Wiki $wiki)
    {
        $section = new Section();
        $contrib = new Contribution();
        $contrib->setTitle($data['activeContribution']['title']);
        $contrib->setText($data['activeContribution']['text']);
        $contrib->setSection($section);
        $section->setActiveContribution($contrib);
        $this->om->persist($contrib);

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $childSec = $this->importSection($child, $wiki);
                $childSec->setParent($section);
                $this->om->getRepository(Section::class)->persistAsLastChildOf($childSec, $section);
            }
        }

        $section->setWiki($wiki);

        return $section;
    }

    /**
     * @param GenericDataEvent $event
     */
    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->evaluationManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-icap_wiki-section_create', 'resource-icap_wiki-section_update'],
            $startDate
        );
        $nbLogs = count($logs);

        if ($nbLogs > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->evaluationManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $status = AbstractResourceEvaluation::STATUS_UNKNOWN;
            $nbAttempts = 0;
            $nbOpenings = 0;

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;

                        if (AbstractResourceEvaluation::STATUS_UNKNOWN === $status) {
                            $status = AbstractResourceEvaluation::STATUS_OPENED;
                        }
                        break;
                    case 'resource-icap_wiki-section_create':
                    case 'resource-icap_wiki-section_update':
                        ++$nbAttempts;
                        $status = AbstractResourceEvaluation::STATUS_PARTICIPATED;
                        break;
                }
            }
            $tracking->setStatus($status);
            $tracking->setNbAttempts($nbAttempts);
            $tracking->setNbOpenings($nbOpenings);
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
