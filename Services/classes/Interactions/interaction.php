<?php

/**
 * abstract class
 *
 */

namespace UJM\ExoBundle\Services\classes\Interactions;

use Doctrine\Bundle\DoctrineBundle\Registry;


abstract class interaction {
    
    protected $doctrine;
    
    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     *
     */
    public function __construct(
        Registry $doctrine
    )
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * For an interaction know if it's linked with response and if it's shared
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     *
     * @return array
     */
    public function getActionInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $response = $this->doctrine->getManager()
                         ->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $interaction->getId()));
        if (count($response) > 0) {
            $questionWithResponse[$interaction->getId()] = 1;
        } else {
            $questionWithResponse[$interaction->getId()] = 0;
        }

        $share = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('question' => $interaction->getQuestion()->getId()));
        if (count($share) > 0) {
            $alreadyShared[$interaction->getQuestion()->getId()] = 1;
        } else {
            $alreadyShared[$interaction->getQuestion()->getId()] = 0;
        }

        $actions[0] = $questionWithResponse;
        $actions[1] = $alreadyShared;

        return $actions;
    }
    
    /**
     * abstract method
     * To process the user's response for a paper(or a test)
     *
     * @access public
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $paperID id Paper or 0 if it's just a question test and not a paper
     *
     * @return array
     */
    abstract public function response(\Symfony\Component\HttpFoundation\Request $request, $paperID = 0);
    
     /**
     * To calculate the score for a question
     *
     * @access public
     *
     * @return string userScore/scoreMax
     */
    abstract public function mark();
    
    /**
     * Get score max possible for a question
     *
     * @access public
     *
     *
     * @return float
     */
    abstract public function matchingMaxScore();
}
