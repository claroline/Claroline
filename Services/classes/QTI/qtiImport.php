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
     * Create the objets Interaction and Question
     *
     * @access protected
     *
     */
    protected function genericInteraction()
    {
        $this->question = new Question();
        $this->question->setTitle($this->getTitle());
        $this->question->setDateCreate(new \Datetime());
        $this->question->setUser($this->user);
        $this->question->setCategory($this->qtiCat);
        $this->doctrine->getManager()->persist($this->question);
        $this->doctrine->getManager()->flush();

        $this->interaction = new Interaction();
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
        $title = '';
        $elements = $this->document->getElementsByTagName('assessmentItem');
        $element = $elements->item(0);
        if ($element->hasAttribute("title")) {
           $title = $element->getAttribute("title");
        }

        return $title;
    }

    /**
     * Get the description of question to import
     *
     * @access private
     *
     */
    private function getDescription()
    {
    }

    /**
     * abstract method to import a question
     *
     * @access public
     * @param qtiRepository $qtiRepos
     */
    abstract public function import(qtiRepository $qtiRepos, \DOMDocument $document);

}
