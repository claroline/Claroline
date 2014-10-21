<?php

/**
 * To import a question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\SecurityContextInterface;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Interaction;
use UJM\ExoBundle\Entity\Question;

abstract class qtiImport
{
    protected $doctrine;
    protected $securityContext;
    protected $container;
    protected $user;
    protected $qtiRepos;
    protected $qtiCat;
    protected $interaction;
    protected $question;
    protected $document;
    protected $assessmentItem;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     *
     */
    public function __construct(Registry $doctrine, SecurityContextInterface $securityContext, $container)
    {
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
        $this->container       = $container;
        $this->user            = $this->securityContext->getToken()->getUser();
    }

    /**
     * Create the question objet
     *
     * @access protected
     *
     */
    protected function createQuestion()
    {
        $this->question = new Question();
        $this->question->setTitle($this->getTitle());
        $this->question->setDateCreate(new \Datetime());
        $this->question->setUser($this->user);
        $this->question->setCategory($this->qtiCat);
        $this->getDescription();
        $this->doctrine->getManager()->persist($this->question);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Create the interaction objet
     *
     * @access protected
     *
     */
    protected function createInteraction()
    {
        $feedback = $this->getFeedback();
        $this->interaction = new Interaction;
        $this->interaction->setInvite($this->getPrompt());
        if ($feedback != null) {
            $this->interaction->setFeedBack($feedback);
        }
        $this->interaction->setQuestion($this->question);
    }

    /**
     * If not exist create for the user the category QTI to import the question
     *
     * @access private
     *
     */
    private function createQTICategory()
    {
        $this->qtiCat = new Category();
        $this->qtiCat->setValue("QTI");
        $this->qtiCat->setUser($this->user);
        $this->doctrine->getManager()->persist($this->qtiCat);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Get the category QTI of the user
     *
     * @access protected
     *
     */
    protected function getQTICategory()
    {
        $this->qtiCat = $this->doctrine
                             ->getManager()
                             ->getRepository('UJMExoBundle:Category')
                             ->findOneBy(array('value' => 'QTI',
                                               'user' => $this->user->getId()));
        if ($this->qtiCat == null) {
            $this->createQTICategory();
        }
    }

    /**
     * Get the title of question to import
     *
     * @access private
     *
     */
    private function getTitle()
    {
        if ($this->assessmentItem->hasAttribute("title")) {
           $title = $this->assessmentItem->getAttribute("title");
        } else {
            $title = $this->container->get('translator')->trans('qti_import_title');
        }

        return $title;
    }

    /**
     * init assessmentItem
     *
     * @access protected
     *
     */
    protected function initAssessmentItem()
    {
        $root = $this->document->getElementsByTagName('assessmentItem');
        $this->assessmentItem = $root->item(0);
    }

    /**
     * Get the feedback of question to import
     *
     * @access private
     *
     */
    private function getFeedback()
    {
        $feedback = null;
        $md = $this->assessmentItem->getElementsByTagName("modalFeedback")->item(0);
        if (isset($md)) {
           $feedback = $md->nodeValue;
        }

        return $feedback;
    }

    /**
     * Get the description of question to import
     *
     * @access private
     *
     */
    private function getDescription()
    {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        foreach($ib->childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE) {
                $this->question->setDescription($child->textContent);
            }
        }
    }

    /**
     * abstract method to import a question
     *
     * @access public
     * @param qtiRepository $qtiRepos
     */
    abstract public function import(qtiRepository $qtiRepos, \DOMDocument $document);

    /**
     * abstract method to get the prompt
     *
     * @access protected
     */
    abstract protected function getPrompt();

}
