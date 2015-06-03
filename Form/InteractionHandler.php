<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use UJM\ExoBundle\Entity\Category;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Entity\User;

abstract class InteractionHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $exoServ;
    protected $user;
    protected $exercise;
    protected $isClone = FALSE;
    protected $translator;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Symfony\Component\Form\Form $form for an Interaction
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Doctrine EntityManager $em
     * @param \UJM\ExoBundle\Services\classes\exerciseServices $exoServ
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise if the Interaction is created or modified since an exercise if since the bank $exercise=-1
     * @param Translation $translator
     *
     */
    public function __construct(Form $form = NULL, Request $request = NULL, EntityManager $em, $exoServ, User $user, $exercise=-1, TranslatorInterface $translator=NULL)
    {
        $this->form     = $form;
        $this->request  = $request;
        $this->em       = $em;
        $this->exoServ  = $exoServ;
        $this->user     = $user;
        $this->exercise = $exercise;
        $this->translator = $translator;
    }

    /**
     * abstract method to valid the form of an Interaction and call the method to create an Interaction
     *
     * @access public
     */
    abstract public function processAdd();

    /**
     * abstract method to create an Interaction
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $interaction
     *
     * @access protected
     */
    abstract protected function onSuccessAdd($interaction);

    /**
     * abstract method to valid the form of an Interaction and call the method to edit an Interaction
     *
     * @access public
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $interaction
     */
    abstract public function processUpdate($interaction);

    /**
     * abstract method to edit an Interaction
     *
     * @access protected
     */
    abstract protected function onSuccessUpdate();

    /**
     * To persit hints of an Interaction
     *
     * @access protected
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     *
     */
    protected function persistHints($inter) {
        foreach ($inter->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $hint->setInteraction($inter->getInteraction());
            $this->em->persist($hint);
        }
    }

    /**
     * To modify hints of an Interaction
     *
     * @access protected
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     * @param Collection of \UJM\ExoBundle\Entity\Hint $originalHints
     *
     */
    protected function modifyHints($inter, $originalHints) {

        // filter $originalHints to contain hint no longer present
        foreach ($inter->getInteraction()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionqcm
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionqcm
            $inter->getInteraction()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        //On persite tous les hints de l'entité interaction
        foreach ($inter->getInteraction()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $inter->getInteraction()->addHint($hint);
            $this->em->persist($hint);
        }
    }
    /**
     * retrieve the first 50 characters of the issue if no title
     */
    protected function checkTitle()
    {
        $title = $this->form->getData()->getInteraction()->getQuestion();
        $invite =  $this->form->getData()->getInteraction()->getInvite();
        if($title->getTitle() == "")
        {
            //removes html tags and entity code html
            $provTitle=html_entity_decode(strip_tags($invite));

            $newTitle=substr($provTitle,0,50);
            $title->setTitle($newTitle);
        }
    }
    /**
     * Check the category
     */
    protected function checkCategory()
    {
        $data = $this->form->getData()->getInteraction()->getQuestion();
        $default = $this->translator->trans('default');
        $uid=$this->user->getId();
        $ListeCategroy=$this->em->getRepository('UJMExoBundle:Category')->getListCategory($uid);

        $this->lockCategoryDefault($default,$ListeCategroy,$data);

            if($data->getCategory()== null)
            {
                $this->categoryDefault($default,$ListeCategroy,$data);
            }
    }

    /**
     * Lock the category default
     * @param string $default
     * @param array $ListeCategroy
     */
    protected function lockCategoryDefault($default,$ListeCategroy)
    {

        foreach($ListeCategroy as $category)
        {
             if($category->getValue()== $default)
             {
                $category->setLocker('1');
             }
        }
    }

    /**
     * Creates or uses the default category
     * @param type $default
     * @param type $ListeCategroy
     * @param type $data
     */
    protected function categoryDefault($default,$ListeCategroy,$data)
    {
        $checkCategory = False;
        foreach($ListeCategroy as $category)
                {
                     if($category->getlocker()== '1')
                     {
                        $data->setCategory($category);
                        $checkCategory = true;
                     }
                }
                if($checkCategory==false)
                {
                    $newCategory= new Category();
                    $newCategory->setValue($default);
                    $newCategory->setLocker(1);
                    $newCategory->setUser($this->user);
                    $this->em->persist($newCategory);
                    $this->em->flush();
                    $data->setCategory($newCategory);
                }
    }
    /**
     * Add the Interaction in the exercise if created since an exercise
     *
     * @access protected
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     *
     */
    protected function addAnExercise($inter) {

        $this->exoServ->addQuestionInExercise($inter, $this->exercise);
    }

    /**
     * Duplicate the Interaction during the creation
     *
     * @access protected
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     *
     */
    protected function duplicateInter($inter) {
        $request = $this->request;
        if ($this->isClone === FALSE && $request->request->get('nbq') > 0)
        {
            $nbCop = 0;
            while ($nbCop < $request->request->get('nbq')) {
                $nbCop ++;
                $this->singleDuplicateInter($inter);
            }
        }
    }

    /**
     * To limit the number of the clone 10 max
     *
     * @access protected
     *
     * Return boolean
     *
     */
    protected function validateNbClone() {

        $int =  $this->request->request->get('nbq');
        if (preg_match('/^[0-9]$/', $int)) {
            if ($int>=0 && $int<= 10 ) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Duplicate once
     *
     * @access protected
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     *
     */
    public function singleDuplicateInter($inter) {
        $copy = clone $inter;
        $title = $copy->getInteraction()->getQuestion()->getTitle();
        $copy->getInteraction()->getQuestion()
             ->setTitle($title.' #');

        $this->isClone = TRUE;
        $this->onSuccessAdd($copy);
    }
}
