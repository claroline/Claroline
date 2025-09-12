<?php

namespace Icap\LessonBundle\Component\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Manager\PdfManager;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class LessonResource extends ResourceComponent implements DownloadableResourceInterface, FileAdapterInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly PlaceholderManager $placeholderManager,
        private readonly ChapterManager $chapterManager,
        private readonly PdfManager $pdfManager,
    ) {
    }

    public static function getName(): string
    {
        return 'icap_lesson';
    }

    /** @param Lesson $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $internalNotes = $this->authorization->isGranted('VIEW_INTERNAL_NOTES', $resource->getResourceNode());
        $chapters = $this->om->getRepository(Chapter::class)->getChildren($resource->getRoot(), false, null, 'ASC', true);

        return [
            'resource' => $this->serializer->serialize($resource),
            'tree' => $this->chapterManager->serializeChapterTree($resource),
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
            'chapters' => array_map(function (Chapter $chapter) use ($internalNotes) {
                return $this->serializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []);
            }, $chapters),
        ];
    }

    /** @param Lesson $resource */
    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        // generate a PDF for the resource
        $pdfPath = $this->pdfManager->renderLesson($resource, true);
        $fileBag->add($resource->getName().'.pdf', $pdfPath);
    }

    /** @param Lesson $resource */
    public function create(AbstractResource $resource, array $data): void
    {
        $this->om->startFlushSuite();

        $resource->buildRoot();
        if (!empty($data['resource']['chapters'])) {
            foreach ($data['resource']['chapters'] as $chapterData) {
                if (empty($chapterData['title'])) {
                    $chapterData['title'] = $resource->getName();
                }
                $this->chapterManager->createChapter($resource, $chapterData, $resource->getRoot(), [Crud::NO_PERMISSIONS]);
            }
        }

        $this->om->endFlushSuite();
    }

    public function update(AbstractResource $resource, array $data, array $previousData): ?array
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
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
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
        $copy->buildRoot();
        $this->om->flush();

        $this->chapterManager->copyRoot($original->getRoot(), $copy->getRoot());
    }

    /** @param Lesson $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $chapters = $this->om->getRepository(Chapter::class)->getChildren($resource->getRoot());

        return [
            'chapters' => array_map(function (Chapter $chapter) {
                return $this->serializer->serialize($chapter, [SerializerInterface::SERIALIZE_TRANSFER]);
            }, $chapters),
        ];
    }

    /** @param Lesson $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        if (empty($data['chapters'])) {
            return;
        }

        $this->om->startFlushSuite();

        $resource->buildRoot();
        $chapters = [];
        foreach ($data['chapters'] as $chapterData) {
            $parent = $resource->getRoot();
            if (!empty($chapterData['parentSlug']) && !empty($chapters[$chapterData['parentSlug']])) {
                $parent = $chapters[$chapterData['parentSlug']];
            }

            $chapter = $this->chapterManager->createChapter($resource, $chapterData, $parent, [
                Crud::NO_PERMISSIONS, // this has already been checked by the core before forwarding the import
                Crud::NO_VALIDATION,
                Options::REFRESH_UUID,
            ]);

            $chapters[$chapterData['slug']] = $chapter;
        }

        $this->om->endFlushSuite();
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

    public function supportsFile(File $file): int
    {
        if (in_array($file->getMimeType(), ['text/html', 'text/plain'])) {
            return FileAdapterInterface::SUPPORTED;
        }

        return FileAdapterInterface::UNSUPPORTED;
    }

    public function fromFile(File $file): ?array
    {
        return [
            'chapters' => [[
                'contentRaw' => 'text/plain' === $file->getMimeType() ?
                    nl2br($file->getContent()) :
                    $file->getContent(),
            ]],
        ];
    }

    public function requireAdapter(): bool
    {
        return false;
    }
}
