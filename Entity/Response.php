<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Response.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ResponseRepository")
 * @ORM\Table(name="ujm_response")
 */
class Response
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;

    /**
     * @var float
     *
     * @ORM\Column(name="mark", type="float")
     */
    private $mark;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_tries", type="integer")
     */
    private $nbTries;

    /**
     * @var text
     *
     * @ORM\Column(name="response", type="text", nullable=true)
     */
    private $response;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Paper")
     */
    private $paper;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Interaction")
     */
    private $interaction;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ip.
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set mark.
     *
     * @param float $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * Get mark.
     *
     * @return float
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Set nbTries.
     *
     * @param int $nbTries
     */
    public function setNbTries($nbTries)
    {
        $this->nbTries = $nbTries;
    }

    /**
     * Get nbTries.
     *
     * @return int
     */
    public function getNbTries()
    {
        return $this->nbTries;
    }

    /**
     * Set response.
     *
     * @param text $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Get response.
     *
     * @return text
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setPaper(\UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->paper = $paper;
    }

    public function getPaper()
    {
        return $this->paper;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }
}
