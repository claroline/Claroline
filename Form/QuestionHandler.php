<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use UJM\ExoBundle\Entity\Category;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Entity\User;

abstract class QuestionHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $exoServ;
    protected $user;
    protected $exercise;
    protected $isClone = false;
    protected $translator;

    /**
     * Constructor.
     *
     *
     * @param \Symfony\Component\Form\Form                     $form       for an Interaction
     * @param \Symfony\Component\HttpFoundation\Request        $request
     * @param Doctrine EntityManager                           $em
     * @param \UJM\ExoBundle\Services\classes\exerciseServices $exoServ
     * @param \Claroline\CoreBundle\Entity\User                $user
     * @param UJM\ExoBundle\Entity\Exercise                    $exercise   instance of Exercise if the Interaction is created or modified since an exercise if since the bank $exercise=-1
     * @param Translation                                      $translator
     */
    public function __construct(Form $form = null, Request $request = null, EntityManager $em, $exoServ, User $user, $exercise = -1, TranslatorInterface $translator = null)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->exoServ = $exoServ;
        $this->user = $user;
        $this->exercise = $exercise;
        $this->translator = $translator;
    }

    /**
     * abstract method to valid the form of an Interaction and call the method to create an Interaction.
     */
    abstract public function processAdd();

    /**
     * abstract method to create an Interaction.
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $interaction
     */
    abstract protected function onSuccessAdd($interaction);

    /**
     * abstract method to valid the form of an Interaction and call the method to edit an Interaction.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $interaction
     */
    abstract public function processUpdate($interaction);

    /**
     * abstract method to edit an Interaction.
     */
    abstract protected function onSuccessUpdate();

    /**
     * To persit hints of an Interaction.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     */
    protected function persistHints($inter) {
        foreach ($inter->getQuestion()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $hint->setQuestion($inter->getQuestion());
            $this->em->persist($hint);
        }
    }

    /**
     * To modify hints of an Interaction.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     * @param Collection of \UJM\ExoBundle\Entity\Hint                    $originalHints
     */
    protected function modifyHints($inter, $originalHints)
    {

        // filter $originalHints to contain hint no longer present
        foreach ($inter->getQuestion()->getHints() as $hint) {
            foreach ($originalHints as $key => $toDel) {
                if ($toDel->getId() == $hint->getId()) {
                    unset($originalHints[$key]);
                }
            }
        }

        // remove the relationship between the hint and the interactionqcm
        foreach ($originalHints as $hint) {
            // remove the Hint from the interactionqcm
            $inter->getQuestion()->getHints()->removeElement($hint);

            // if you wanted to delete the Hint entirely, you can also do that
            $this->em->remove($hint);
        }

        //On persite tous les hints de l'entité interaction
        foreach ($inter->getQuestion()->getHints() as $hint) {
            $hint->setPenalty(ltrim($hint->getPenalty(), '-'));
            $inter->getQuestion()->addHint($hint);
            $this->em->persist($hint);
        }
    }
    /**
     * retrieve the first 50 characters of the issue if no title.
     */
    protected function checkTitle()
    {
        $question = $this->form->getData()->getQuestion();

        if ($question->getTitle() == "") {
            //removes html tags and entity code html
            $provTitle = html_entity_decode(strip_tags($question->getDescription()));
            $newTitle = substr($provTitle,0,50);
            $question->setTitle($newTitle);
        }
    }
    /**
     * Check the category.
     */
    protected function checkCategory()
    {
        $data = $this->form->getData()->getQuestion();
        $default = $this->translator->trans('default', array(), 'ujm_exo');
        $uid = $this->user->getId();
        $ListeCategroy = $this->em->getRepository('UJMExoBundle:Category')->getListCategory($uid);

        $this->lockCategoryDefault($default, $ListeCategroy, $data);

        if ($data->getCategory() == null) {
            $this->categoryDefault($default, $ListeCategroy, $data);
        }
    }

    /**
     * Lock the category default.
     *
     * @param string $default
     * @param array  $ListeCategroy
     */
    protected function lockCategoryDefault($default, $ListeCategroy)
    {
        foreach ($ListeCategroy as $category) {
            if ($category->getValue() == $default) {
                $category->setLocker('1');
            }
        }
    }

    /**
     * Creates or uses the default category.
     *
     * @param type $default
     * @param type $ListeCategroy
     * @param type $data
     */
    protected function categoryDefault($default, $ListeCategroy, $data)
    {
        $checkCategory = false;
        foreach ($ListeCategroy as $category) {
            if ($category->getlocker() == '1') {
                $data->setCategory($category);
                $checkCategory = true;
            }
        }
        if ($checkCategory == false) {
            $newCategory = new Category();
            $newCategory->setValue($default);
            $newCategory->setLocker(1);
            $newCategory->setUser($this->user);
            $this->em->persist($newCategory);
            $this->em->flush();
            $data->setCategory($newCategory);
        }
    }
    /**
     * Add the Interaction in the exercise if created since an exercise.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     */
    protected function addAnExercise($inter)
    {
        $this->exoServ->addQuestionInExercise($inter, $this->exercise);
    }

    /**
     * Duplicate the Interaction during the creation.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     */
    protected function duplicateInter($inter)
    {
        $request = $this->request;
        if ($this->isClone === false && $request->request->get('nbq') > 0) {
            $nbCop = 0;
            while ($nbCop < $request->request->get('nbq')) {
                ++$nbCop;
                $this->singleDuplicateInter($inter);
            }
        }
    }

    /**
     * To limit the number of the clone 10 max.
     */
    protected function validateNbClone()
    {
        $int = $this->request->request->get('nbq');
        if (preg_match('/^[0-9]$/', $int)) {
            if ($int >= 0 && $int <= 10) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Duplicate once.
     *
     *
     * @param object type of InteractionQCM or InteractionGraphic or .... $inter
     */
    public function singleDuplicateInter($inter)
    {
        $copy = clone $inter;
        $title = $copy->getQuestion()->getTitle();
        $copy->getQuestion()
             ->setTitle($title.' #');

        $this->isClone = true;
        $this->onSuccessAdd($copy);
    }
}
