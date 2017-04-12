<?php

namespace UJM\ExoBundle\Transfer;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Transfer\Parser\ExportContentParser;
use UJM\ExoBundle\Transfer\Parser\ImportContentParser;

/**
 * @DI\Service("ujm_exo.importer.exercise")
 * @DI\Tag("claroline.importer")
 */
class ExerciseImporter extends Importer implements RichTextInterface
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
            throw new ValidationException('Exercise : import data are not valid.', $errors);
        }
    }

    public function import(array $data)
    {
        // Create the exercise entity
        // The rest of the structure will be created at the same time than the rich texts
        // Because this will not be possible to retrieves created entities as all ids are re-generated
        $exercise = new Exercise();
        $exercise->setUuid($data['data']['id']);

        return $exercise;
    }

    public function format($data)
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
        $exercise = $this->container->get('claroline.persistence.object_manager')
            ->getRepository('UJMExoBundle:Exercise')
            ->findOneBy([
                'uuid' => $data['id'],
            ]);

        // Create entities from import data
        $this->container->get('ujm_exo.manager.exercise')->createCopy($quizData, $exercise);
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

        return [
            // The id will be used to retrieve the imported entity to replace the HTML contents
            'id' => Uuid::uuid4()->toString(),
            // YML which will receive the quiz structure can not handle stdClasses (he prefers associative arrays)
            // So we do some ugly encoding/decoding to give him what he wants
            'quiz' => json_decode(json_encode($exerciseData), true),
        ];
    }
}
