<?php

namespace UJM\ExoBundle\Listener\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UJM\ExoBundle\Entity\Question\Question;
use UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection;

/**
 * Manages Life cycle of the Question.
 *
 * @DI\Service("ujm_exo.listener.entity_question")
 * @DI\Tag("doctrine.entity_listener")
 */
class QuestionListener
{
    /**
     * @var QuestionDefinitionsCollection
     */
    private $questionDefinitions;

    /**
     * QuestionListener constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->questionDefinitions = $container->get('ujm_exo.collection.question_definitions');
    }

    /**
     * Loads the entity that holds the question type data when a Question is loaded.
     *
     * @param Question           $question
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Question $question, LifecycleEventArgs $event)
    {
        $definition = $this->questionDefinitions->get($question->getMimeType());
        $repository = $event
            ->getEntityManager()
            ->getRepository($definition->getEntityClass());

        /** @var \UJM\ExoBundle\Entity\QuestionType\AbstractQuestion $typeEntity */
        $typeEntity = $repository->findOneBy([
            'question' => $question,
        ]);

        if (!empty($typeEntity)) {
            $question->setInteraction($typeEntity);
        }
    }

    /**
     * Persists the entity that holds the question type data when a Question is persisted.
     *
     * @param Question           $question
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Question $question, LifecycleEventArgs $event)
    {
        $interaction = $question->getInteraction();
        if (null !== $interaction) {
            $event->getEntityManager()->persist($interaction);
        }
    }

    /**
     * Deletes the entity that holds the question type data when a Question is deleted.
     *
     * @param Question           $question
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Question $question, LifecycleEventArgs $event)
    {
        $interaction = $question->getInteraction();
        if (null !== $interaction) {
            $event->getEntityManager()->remove($interaction);
        }
    }
}
