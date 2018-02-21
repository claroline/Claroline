<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Item\HintSerializer;

/**
 * @DI\Service("ujm_exo.manager.hint")
 */
class HintManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var HintSerializer
     */
    private $serializer;

    /**
     * HintManager constructor.
     *
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("ujm_exo.serializer.hint")
     * })
     *
     * @param ObjectManager  $objectManager
     * @param HintSerializer $serializer
     */
    public function __construct(
        ObjectManager $objectManager,
        HintSerializer $serializer)
    {
        $this->om = $objectManager;
        $this->serializer = $serializer;
    }

    /**
     * Serializes an Hint.
     *
     * @param Hint  $hint
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize(Hint $hint, array $options = [])
    {
        return $this->serializer->serialize($hint, $options);
    }

    /**
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');

        return $repo->hasHint($paper, $hint);
    }

    /**
     * Returns the contents of a hint and records a log asserting that the hint
     * has been consulted for a given paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return string
     */
    public function viewHint(Paper $paper, Hint $hint)
    {
        $log = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findOneBy(['paper' => $paper, 'hint' => $hint]);

        if (!$log) {
            $log = new LinkHintPaper($hint, $paper);
            $this->om->persist($log);
            $this->om->flush();
        }

        return $hint->getValue();
    }
}
