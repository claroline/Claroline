<?php

namespace UJM\ExoBundle\Transfer;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\ResourceRichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ItemType\ContentItem;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Transfer\Parser\ExportContentParser;
use UJM\ExoBundle\Transfer\Parser\ImportContentParser;

/**
 * @DI\Service("ujm_exo.importer.exercise")
 * @DI\Tag("claroline.importer")
 */
class ExerciseImporter extends Importer implements ResourceRichTextInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ExerciseImporter constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'ujm_exercise';
    }

    public function validate(array $data)
    {
        $errors = $this->container->get('ujm_exo.validator.exercise')
            ->validate(json_decode(json_encode($data['data']['quiz'])), [Validation::REQUIRE_SOLUTIONS]);

        if (!empty($errors)) {
            throw new ValidationException('Exercise : import data are not valid.', $errors, $data);
        }
    }

    public function import(array $data)
    {
        // Create the exercise entity
        // The rest of the structure will be created at the same time than the rich texts
        // Because this will not be possible to retrieves created entities as all ids are re-generated
        //dump and move the exercise object items

        return new Exercise();
    }

    public function format($data, $exercise)
    {
        $quizData = json_decode(json_encode($data['quiz']));

        // Replaces placeholders in HTML contents
        $this->container->get('ujm_exo.manager.exercise')->parseContents(
            new ImportContentParser(
                $this->getRootPath(),
                $this->container->get('claroline.importer.rich_text_formatter')
            ),
            $quizData
        );

        // Retrieve the new exercise
        //$exercise = $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        // Create entities from import data
        $exercise = $this->container->get('ujm_exo.manager.exercise')->createCopy($quizData, $exercise);

        $fileUtilities = $this->container->get('claroline.utilities.file');
        $om = $this->container->get('claroline.persistence.object_manager');

        //import the objects
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepItem) {
                foreach ($stepItem->getQuestion()->getObjects() as $object) {
                    if ($object->getMimeType() !== 'text/html') {
                        $basename = basename($object->getData());
                        $file = new File($this->getRootPath().DIRECTORY_SEPARATOR.$basename);
                        $file = $fileUtilities->createFile($file);
                        $object->setData($file->getUrl());
                        $om->persist($object);
                    }

                    if ($stepItem->getQuestion()->getInteraction() instanceof ContentItem && 1 !== preg_match('#^text\/[^/]+$#', $stepItem->getQuestion()->getMimeType())) {
                        $contentItem = $stepItem->getQuestion()->getInteraction();
                        $basename = basename($contentItem->getData());
                        $file = new File($this->getRootPath().DIRECTORY_SEPARATOR.$basename);
                        $file = $fileUtilities->createFile($file);
                        $object->setData($file->getUrl());
                        $om->persist($contentItem);
                    }
                }
            }
        }

        $om->flush();
    }

    public function export($workspace, array &$files, $exercise)
    {
        // Export exercise data
        $exerciseData = $this->container->get('ujm_exo.manager.exercise')->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS]);

        // Replaces links in HTML contents
        $contentParser = new ExportContentParser(
            $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir'),
            $this->container->get('claroline.importer.rich_text_formatter')
        );

        $this->container
            ->get('ujm_exo.manager.exercise')
            ->parseContents($contentParser, $exerciseData);

        $files = array_merge($files, $contentParser->getDumpedContents());

        //we also want to dump the objects stored for each step.
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepItem) {
                foreach ($stepItem->getQuestion()->getObjects() as $object) {
                    if ($object->getMimeType() !== 'text/html') {
                        $files[basename($object->getData())] = $this->container
                            ->getParameter('claroline.param.web_dir').DIRECTORY_SEPARATOR.$object->getData();
                    }
                }
            }
        }

        //same process for the content question type
        //we also want to dump the objects stored for each step.
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepItem) {
                $item = $stepItem->getQuestion();
                if ($item->getInteraction() instanceof ContentItem && 1 !== preg_match('#^text\/[^/]+$#', $item->getMimeType())) {
                    $url = $item->getInteraction()->getData();
                    $files[basename($url)] = $this->container
                      ->getParameter('claroline.param.web_dir').DIRECTORY_SEPARATOR.$url;
                }
            }
        }

        return [
            // YML which will receive the quiz structure can not handle stdClasses (he prefers associative arrays)
            // So we do some ugly encoding/decoding to give him what he wants
            'quiz' => json_decode(json_encode($exerciseData), true),
        ];
    }
}
