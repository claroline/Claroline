<?php

namespace Icap\LessonBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LessonListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /* @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ChapterManager */
    private $chapterManager;

    /** @var ChapterRepository */
    private $chapterRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        ChapterManager $chapterManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->chapterManager = $chapterManager;

        $this->chapterRepository = $this->om->getRepository(Chapter::class);
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();
        $root = $this->chapterRepository->findOneBy(['lesson' => $lesson, 'level' => 0, 'parent' => null]);

        $internalNotes = $this->authorization->isGranted('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        $event->setData([
            'lesson' => $this->serializer->serialize($lesson),
            'tree' => $this->chapterManager->serializeChapterTree($lesson),
            'root' => $root ? $this->serializer->serialize($root, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []) : null,
        ]);

        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();

        /** @var Lesson $newLesson */
        $newLesson = $event->getCopy();
        $newRoot = new Chapter();
        $newRoot->setLesson($newLesson);
        $newLesson->setRoot($newRoot);

        $this->om->persist($newRoot);
        $this->om->persist($newLesson);
        $this->om->flush();

        $this->chapterManager->copyRoot($lesson->getRoot(), $newLesson->getRoot());

        $event->setCopy($newLesson);
        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();

        $event->setData([
            'root' => $this->chapterManager->serializeChapterTree($lesson),
        ]);
    }

    public function onImport(ImportResourceEvent $event)
    {
        $data = $event->getData();
        /** @var Lesson $lesson */
        $lesson = $event->getResource();

        // TODO : use CRUD to import chapters

        $rootChapter = $data['root'];
        $lesson->buildRoot();
        $root = $lesson->getRoot();

        if (isset($rootChapter['children'])) {
            $children = $rootChapter['children'];

            foreach ($children as $child) {
                $chapter = $this->importChapter($lesson, $child);
                $chapter->setLesson($lesson);
                $chapter->setParent($root);
                $this->om->persist($chapter);
            }
        }
    }

    private function importChapter(Lesson $lesson, array $data = [])
    {
        $chapter = new Chapter();
        $chapter->setTitle($data['title']);
        $chapter->setText($data['text']);

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $childChap = $this->importChapter($lesson, $child);
                $childChap->setParent($chapter);
            }
        }

        $chapter->setLesson($lesson);
        $this->om->persist($chapter);

        return $chapter;
    }
}
