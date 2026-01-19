<?php

namespace Icap\LessonBundle\Manager;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;
use Twig\Environment;

class PdfManager
{
    private ChapterRepository $chapterRepo;

    public function __construct(
        private readonly Environment $templating,
        ObjectManager $om,
        private readonly BasePdfManager $pdfManager,
        private readonly TempFileManager $tempManager
    ) {
        $this->chapterRepo = $om->getRepository(Chapter::class);
    }

    public function renderLesson(Lesson $lesson, ?bool $toFile = false): string
    {
        $pdfContent = $this->pdfManager->fromHtml(
            $this->templating->render('@IcapLesson/lesson/open.pdf.twig', [
                '_resource' => $lesson,
                'tree' => $this->chapterRepo->getChapterTree($lesson->getRoot(), false),
            ])
        );

        if ($toFile) {
            return $this->toFile($pdfContent);
        }

        return $pdfContent;
    }

    public function renderChapter(Chapter $chapter, ?bool $toFile = false): string
    {
        $pdfContent = $this->pdfManager->fromHtml(
            $this->templating->render('@IcapLesson/lesson/open.pdf.twig', [
                '_resource' => $chapter->getLesson(),
                'tree' => $this->chapterRepo->getChapterTree($chapter),
            ])
        );

        if ($toFile) {
            return $this->toFile($pdfContent);
        }

        return $pdfContent;
    }

    private function toFile(string $pdfContent): string
    {
        $pdfPath = $this->tempManager->generate();
        file_put_contents($pdfPath, $pdfContent);

        return $pdfPath;
    }
}
