<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Interaction;

/**
 * @DI\Service("ujm.exo.api_manager")
 */
class ApiManager
{
    private $om;
    private $interactionRepo;
    private $interactionQcmRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->interactionRepo = $om->getRepository('UJMExoBundle:Interaction');
        $this->interactionQcmRepo = $om->getRepository('UJMExoBundle:InteractionQCM');
    }

    /**
     * @todo add user parameter...
     *
     * @param Exercise $exercise
     * @return array
     */
    public function exportExercise(Exercise $exercise)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->getMetadata($exercise),
            'steps' => $this->getSteps($exercise)
        ];
    }

    /**
     * @todo add duration
     *
     * @param Exercise $exercise
     * @return array
     */
    private function getMetadata(Exercise $exercise)
    {
        $node = $exercise->getResourceNode();
        $creator = $node->getCreator();
        $authorName = sprintf('%s %s', $creator->getFirstName(), $creator->getLastName());

        return [
            'authors' => [$authorName],
            'created' => $node->getCreationDate()->format('Y-m-d H:i:s'),
            'title' => $exercise->getTitle(),
            'description' => $exercise->getDescription(),
            'pick' => $exercise->getNbQuestion(),
            'random' => $exercise->getShuffle(),
            'maxAttempts' => $exercise->getMaxAttempts()
        ];
    }

    /**
     * @todo step id...
     * @todo add optional question description (schema)
     *
     * @param Exercise $exercise
     * @return array
     */
    private function getSteps(Exercise $exercise)
    {
        $interactions = $this->interactionRepo->findByExercise($exercise);

        return array_map(function ($interaction) {
            switch ($type = $interaction->getType()) {
                case 'InteractionQCM':
                    $data = $this->getQCM($interaction);
                    $type = 'application/x.choice+json';
                    break;
                default:
                    throw new \Exception("Export not implemented for {$type} type");
            }

            $step = [
                'id' => 'todo',
                'items' => [array_merge($data, [
                    'id' => $interaction->getId(),
                    'type' => $type,
                    'title' => $interaction->getQuestion()->getTitle(),
                    'hints' => ''
                ])]
            ];

            if ($interaction->getFeedback()) {
                $step['items'][0]['feedback'] = $interaction->getFeedback();
            }

            if (count($hints = $interaction->getHints()->toArray()) > 0) {
                $step['items'][0]['hints'] = array_map(function ($hint) {
                    return [
                        'id' => $hint->getId(),
                        'text' => $hint->getValue(),
                        'penalty' => $hint->getPenalty()
                    ];
                }, $hints);
            }

            return $step;
        }, $interactions);
    }

    /**
     * @todo get real "multiple" value
     * @todo add solutions property
     * @todo check order of choices
     * @todo weight ?
     *
     * @param Interaction $interaction
     * @return array
     */
    private function getQCM(Interaction $interaction)
    {
        $qcm = $this->interactionQcmRepo->findOneBy(['interaction' => $interaction]);

        return [
            'multiple' => false,
            'random' => $qcm->getShuffle(),
            'choices' => array_map(function ($choice) {
                return [
                    'id' => $choice->getId(),
                    'type' => 'text/html',
                    'data' => $choice->getLabel()
                ];
            }, $qcm->getChoices()->toArray()),
        ];
    }
}