<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Interaction
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionRepository")
 * @ORM\Table(name="ujm_interaction")
 */
class Interaction
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string $invite
     *
     * @ORM\Column(name="invite", type="text")
     */
    private $invite;

    /**
     * @var integer $ordre
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @var string $feedBack
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    private $feedBack;

    /**
     * @var boolean $locked_expertise
     *
     * @ORM\Column(name="locked_expertise", type="boolean", nullable=true)
     */
    private $lockedExpertise = false;

    /**
     * @ORM\ManyToMany(targetEntity="UJM\ExoBundle\Entity\Document")
     * @ORM\JoinTable(
     *     name="ujm_document_interaction",
     *     joinColumns={
     *         @ORM\JoinColumn(name="interaction_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="document_id", referencedColumnName="id")}
     * )
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question", cascade={"remove"})
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Hint", mappedBy="interaction", cascade={"remove","persist"})
     */
    private $hints;

     /**
     * Constructs a new instance of Documents, hints
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->hints = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set invite
     *
     * @param string $invite
     */
    public function setInvite($invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get invite
     *
     * @return text
     */
    public function getInvite()
    {
        return $this->invite;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set feedBack
     *
     * @param text $feedBack
     */
    public function setFeedBack($feedBack)
    {
        $this->feedBack = $feedBack;
    }

    /**
     * Get feedBack
     *
     * @return text
     */
    public function getFeedBack()
    {
        return $this->feedBack;
    }

    /**
     * Set locked_expertise
     *
     * @param boolean $lockedExpertise
     */
    public function setLockedExpertise($lockedExpertise)
    {
        $this->lockedExpertise = $lockedExpertise;
    }

    /**
     * Get lockedExpertise
     */
    public function getLockedExpertise()
    {
        return $this->lockedExpertise;
    }

    /**
     * Gets an array of Documents.
     *
     * @return array An array of Documents objects
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add $Document
     *
     * @param UJM\ExoBundle\Entity\Document $Document
     */
    public function addDocument(\UJM\ExoBundle\Entity\Document $document)
    {
        $this->document[] = $document;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    public function getHints()
    {
        return $this->hints;
    }

    public function addHint(Hint $hint)
    {
        $this->hints[] = $hint;
        //le choix est bien lié à l'entité interactionqcm, mais dans l'entité choice il faut
        //aussi lier l'interactionqcm double travail avec les relations bidirectionnelles avec
        //lesquelles il faut bien faire attention à garder les données cohérentes dans un autre
        //script il faudra exécuter $interaction->addHint() qui garde la cohérence entre les
        //deux entités, il ne faudra pas exécuter $hint->setInteraction(), car lui ne garde pas
        // la cohérence
        $hint->setInteraction($this);
    }

    public function removeHint(\UJM\ExoBundle\Entity\Hint $hint)
    {
    }

    public function setHints($hints)
    {
        foreach ($hints as $hint) {
            $this->addHint($hint);
        }
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $this->question = clone $this->question;
            $this->question->setModel(0);

            $newHints = new \Doctrine\Common\Collections\ArrayCollection;
            foreach ($this->hints as $hint) {
                $newHint = clone $hint;
                $newHint->setInteraction($this);
                $newHints->add($newHint);
            }
            $this->hints = $newHints;

        }
    }
}
