<?php

namespace Icap\LessonBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LessonListener extends ResourceComponent
{
    private ChapterRepository $chapterRepository;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ChapterManager $chapterManager
    ) {
        $this->chapterRepository = $this->om->getRepository(Chapter::class);
    }

    public static function getName(): string
    {
        return 'icap_lesson';
    }

    /** @var Lesson $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $root = $this->chapterRepository->findOneBy(['lesson' => $resource, 'level' => 0, 'parent' => null]);

        $internalNotes = $this->authorization->isGranted('VIEW_INTERNAL_NOTES', $resource->getResourceNode());

        return [
            'resource' => $this->serializer->serialize($resource),
            'tree' => $this->chapterManager->serializeChapterTree($resource),
            'root' => $root ? $this->serializer->serialize($root, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []) : null,
        ];
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        $chapters = [];
        if (!empty($data['chapters'])) {
            $this->om->startFlushSuite();

            foreach ($data['chapters'] as $chapterData) {
                $chapters[] = $this->crud->createOrUpdate(Chapter::class, $chapterData);
            }

            // TODO : remove deleted chapters

            $this->om->endFlushSuite();
        }

        return [
            'resource' => $this->serializer->serialize($resource),
            'chapters' => array_map(function (Chapter $chapter) {
                return $this->serializer->serialize($chapter);
            }, $chapters),
        ];
    }

    /**
     * @param Lesson $original
     * @param Lesson $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $newRoot = new Chapter();
        $newRoot->setLesson($copy);
        $copy->setRoot($newRoot);

        $this->om->persist($newRoot);
        $this->om->persist($copy);
        $this->om->flush();

        $this->chapterManager->copyRoot($original->getRoot(), $copy->getRoot());

    }

    /** @var Lesson $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        return [
            'root' => $this->chapterManager->serializeChapterTree($resource),
        ];
    }

    /** @var Lesson $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        if (empty($data['root'])) {
            return;
        }

        $rootChapter = $data['root'];
        $resource->buildRoot();
        $root = $resource->getRoot();

        if (isset($rootChapter['children'])) {
            $children = $rootChapter['children'];

            foreach ($children as $child) {
                $chapter = $this->importChapter($resource, $child);
                $chapter->setLesson($resource);
                $chapter->setParent($root);
                $this->om->persist($chapter);
            }
        }
    }

    private function importChapter(Lesson $lesson, array $data = []): Chapter
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
