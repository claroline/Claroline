<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->initializeResourceEvaluationProgression();
    }

    /**
     * Initializes progression of path evaluation.
     */
    private function initializeResourceEvaluationProgression()
    {
        $this->log('Initializing progression of path evaluations...');

        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');
        $paths = $om->getRepository('Innova\PathBundle\Entity\Path\Path')->findAll();

        $om->startFlushSuite();
        $i = 0;

        foreach ($paths as $path) {
            $node = $path->getResourceNode();
            $userEvals = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation')
                ->findBy(['resourceNode' => $node]);

            foreach ($userEvals as $userEval) {
                $userEvalScore = $userEval->getScore();
                $userEvalScoreMax = $userEval->getScoreMax();

                if (is_null($userEval->getProgression()) && !is_null($userEvalScore) && !empty($userEvalScoreMax)) {
                    $progression = intval(($userEvalScore / $userEvalScoreMax) * 100);
                    $userEval->setProgression($progression);
                    $om->persist($userEval);
                    ++$i;

                    if (0 === $i % 250) {
                        $om->forceFlush();
                    }
                }
                $evals = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceEvaluation')
                    ->findBy(['resourceUserEvaluation' => $userEval]);

                foreach ($evals as $eval) {
                    $evalScore = $eval->getScore();
                    $evalScoreMax = $eval->getScoreMax();

                    if (is_null($eval->getProgression()) && !is_null($evalScore) && !empty($evalScoreMax)) {
                        $progression = intval(($evalScore / $evalScoreMax) * 100);
                        $eval->setProgression($progression);
                        $om->persist($eval);
                        ++$i;

                        if (0 === $i % 250) {
                            $om->forceFlush();
                        }
                    }
                }
            }
        }

        $om->endFlushSuite();
    }
}
